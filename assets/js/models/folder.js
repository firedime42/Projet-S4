(function () {

    function valideID(id) { return Number.isInteger(id) && id >= 0; }
    function notEmptyString(str) { return typeof str == 'string' && str.length > 0; }
    function notNullOrUndefined(obj) { return typeof obj !== null && obj !== undefined; }
    function isString(str) { return typeof str == 'string'; }
    function isBoolean(bool) { return typeof bool == 'boolean';}

    function ids(list) {
        let nb_items = list.length;
        var list_id = new Array(nb_items);

        for (let i = 0; i < nb_items; i++) list_id[i] = list[i].id;

        return list_id;
    }

    class Folder extends Listenable {
        static EVENT_UPDATE = "update";
        static EVENT_REMOVED = "removed";
        static EVENT_NEW_FILE = "newFile";
        static EVENT_NEW_FOLDER = "newFolder";
        static EVENT_REMOVE_FILE = "removeFile";
        static EVENT_REMOVE_FOLDER = "removeFolder";

        #id;         // identifiant du dossier
        #lastUpdate; // timestamp de la dernière update du dossier

        #nom;        // nom du dossier
        #description;// description du dossier

        #newNom;
        #newDescription;

        #groupe;     // identifiant du groupe auquel appartient le dossier
        #path;

        #folders;    // tous les identifiants des dossiers contenu dans le dossier
        #files;      // tous les identifiants des fichiers contenu dans le dossier
        #nb_messages;
        #new_messages;
        #new_files;

        #chat;       // identifiant du chat

        constructor(id = null) {
            super();

            this.#id = id;
            this.#nom = null;
            this.#description = null;
            this.#groupe = null;
            this.#path = null;
            this.#nb_messages = 0;
            this.#new_messages = false;
            this.#new_files = false;

            this.#folders = [];
            this.#files = [];

            this.#lastUpdate = 0;
        }

        get id() { return this.#id; }
        get nom() { return this.#newNom || this.#nom; }
        get description() { return this.#newDescription || this.#description; }
        get groupe() { return this.#groupe; }
        get path() { return this.#path; }
        get chat() { return this.#chat; }

        get nb_folders() { return this.#folders.length; }
        get nb_files() { return this.#files.length; }
        get nb_messages() { return this.#nb_messages; }
        get new_messages() { return this.#new_messages; }
        get new_files() { return this.#new_files; }

        get folders() { return this.#folders; }
        get files() { return this.#files; }

        set nom(nom) { this.#newNom = nom; }
        set description(descr) { this.#newDescription = descr; }


        /**
         * Ecraser les données avec les nouvelles données
         * @param {*} data 
         */
        __parseData(data) {
            if (notEmptyString(data.nom)) this.#nom = data.nom;
            if (isString(data.description)) this.#description = data.description;
            if (valideID(data.groupe)) this.#groupe = data.groupe;
            if (valideID(data.chat)) this.#chat = data.chat;
            if (Number.isInteger(data.nb_messages)) this.#nb_messages = data.nb_messages;
            if (isBoolean(data.notif_new_messages)) this.#new_messages = data.notif_new_messages;
            if (isBoolean(data.notif_new_files)) this.#new_files = data.notif_new_files;
            if (Array.isArray(data.path)) {
                let nb_parents = data.path.length;

                this.#path = new Array(nb_parents);
                for (let i = 0; i < nb_parents; i++)
                    this.#path[i] = data.path[i].id;

                if (data.path.length > 0) {
                    let parent = data.path.pop();
                    parent.path = data.path;
                    FOLDERS.getWithoutPull(parent.id).__parseData(parent);
                }
            }

            if (Number.isInteger(data.folders)) {
                if (!notNullOrUndefined(this.#folders)) this.#folders = new Array(data.folders).fill(-1);
            } else if (notNullOrUndefined(data.folders)) {
                let folders = ids(data.folders);
                
                // charger les données
                for (let i = 0, nb_folders = folders.length; i < nb_folders; i++)
                    FOLDERS.getWithoutPull(folders[i]).__parseData(data.folders[i]);

                // detecter les changements
                let mutationsFolders = arrayMutations(this.#folders, folders);

                // changer la liste d'identifiant
                this.#folders = folders;

                // emit event for each folders
                for (let i = 0, n = mutationsFolders.added.length; i < n; i++)
                    this.emit(Folder.EVENT_NEW_FOLDER, mutationsFolders.added[i]);

                for (let i = 0, n = mutationsFolders.removed.length; i < n; i++)
                    this.emit(Folder.EVENT_REMOVE_FOLDER, mutationsFolders.removed[i]);
            }

            if (Number.isInteger(data.files)) {
                if (!notNullOrUndefined(this.#files)) this.#files = new Array(data.files).fill(-1);
            } else if (notNullOrUndefined(data.files)) {

                let files = ids(data.files);

                // charger les données
                for (let i = 0, nb_files = files.length; i < nb_files; i++)
                    WFILES.getWithoutPull(files[i]).__parseData(data.files[i]);

                // detecter les changements
                let mutationsFiles = arrayMutations(this.#files, files);

                // changer la liste d'identifiant
                this.#files = files;

                // emit event for each files    
                for (let i = 0, n = mutationsFiles.added.length; i < n; i++)
                    this.emit(Folder.EVENT_NEW_FILE, mutationsFiles.added[i]);

                for (let i = 0, n = mutationsFiles.removed.length; i < n; i++)
                    this.emit(Folder.EVENT_REMOVE_FILE, mutationsFiles.removed[i]);

            }

            // emit event for data
            this.emit(Folder.EVENT_UPDATE);
        }

        /**
         * Récuperer les informations depuis le serveur et les actualise si elles ont changées
         */
        async pull() {
            if (this.#id == null) return _error(-1);
            
            let r = await request("/core/controller/folder.php", {
                action: 'pull',
                id: this.#id,
                lastUpdate: this.#lastUpdate
            });

            if (r instanceof Error) return r;

            if (r.folder != null) this.__parseData(r.folder);

            return this;
        }

        /**
         * tenter de mettre à jour les données du serveur en fonction des données locals
         */
        async push() {

        }


        /**
         * Envoyer le fichier au serveur
         * @param {WazapFile} wfile 
         */
        async addFile(wfile) {
            this.#files.push(wfile.id);
            this.emit(Folder.EVENT_NEW_FILE, wfile.id);
        }

        /**
         * créer un dossier dans le dossier courant
         */
        async createFolder(nom, description) {
            if (!(typeof nom == 'string' && nom.length > 2 && nom.length < 50)) return _error(-1);
            if (!(typeof description == 'string' && nom.length < 300)) return _error(-1);

            let r = await request("/core/controller/folder.php", {
                action: 'create',
                parent: this.#id,
                nom: nom,
                description: description
            });

            if (r instanceof Error) { return r; }

            this.#folders.push(r.id);

            let f = await FOLDERS.get(r.id);
            this.emit(Folder.EVENT_NEW_FOLDER, r.id);

            return f;
        }

        /**
         * supprimer le dossier
         * @returns 
         */
        async remove() {
            if (this.#id == null) return _error(-1);
            
            let r = await request("/core/controller/folder.php", {
                action: 'remove',
                id: this.#id
            });

            if (r instanceof Error) { return r; }

            this.emit(Folder.EVENT_REMOVED, r.id);
            
            FOLDERS.free([this.#id]);

            return true;
        }
    }

    class FolderManager {
        #folders;

        constructor () {
            this.#folders = {};
        }

        __valideID(id) { return Number.isInteger(id) && id >= 0; }

        /**
         * Recupère un dossier
         * @param {Number} id l'identifiant du dossier
         */
        async get(id) {
            // verification du type de l'id
            if (!this.__valideID(id)) return null;

            // on crée le dossier s'il n'existe pas
            if (!this.#folders[id]) this.#folders[id] = new Folder(id);

            // on récupère les données depuis le serveur
            return await this.#folders[id].pull();
        }

        /**
         * récupère le dossier sans mettre à jour.
         * @param {Number} id 
         * @returns 
         */
        async _get(id) {
            // verification du type de l'id
            if (!this.__valideID(id)) return null;

            // on crée le dossier s'il n'existe pas
            if (!this.#folders[id]) { 
                this.#folders[id] = new Folder(id);
                await this.#folders[id].pull();
            }

            // on récupère les données depuis le serveur
            return this.#folders[id];
        }

        getWithoutPull(id) {
            // verification du type de l'id
            if (!this.__valideID(id)) return null;

            if (!this.#folders[id]) this.#folders[id] = new Folder(id);

            return this.#folders[id];
        }

        /**
         * Libere l'espace alloué en ram
         * @param list_id liste des identifiants des dossiers
         */
        free(list_id) {
            let nb_folders = list_id.length;
            for (let i = 0; i < nb_folders; i++)
                delete this.#folders[list_id[i]];
        }
    }

    window.Folder = Folder;
    window.FOLDERS = new FolderManager();
})();