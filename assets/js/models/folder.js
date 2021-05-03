(function () {

    class Folder extends Listenable {
        static EVENT_UPDATE = "update";
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
        #parent;

        #folders;    // tous les identifiants des dossiers contenu dans le dossier
        #files;      // tous les identifiants des fichiers contenu dans le dossier
        #nb_messages;

        #chat;       // identifiant du chat

        constructor(id = null) {
            super();

            this.#id = id;
            this.#nom = null;
            this.#description = null;
            this.#groupe = null;
            this.#parent = null;

            this.#folders = [];
            this.#files = [];

            this.#lastUpdate = 0;
        }

        get id() { return this.#id; }
        get nom() { return this.#newNom || this.#nom; }
        get description() { return this.#newDescription || this.#description; }
        get groupe() { return this.#groupe; }
        get parent() { return this.#parent; }
        get chat() { return this.#chat; }

        get nb_folders() { return this.#folders.length; }
        get nb_files() { return this.#files.length; }
        get nb_messages() { return this.#nb_messages; }

        get folders() { return this.#folders; }
        get files() { return this.#files; }

        set nom(nom) { this.#newNom = nom; }
        set description(descr) { this.#newDescription = descr; }


        /**
         * Ecraser les données avec les nouvelles données
         * @param {*} data 
         */
        __parseData(data) {
            this.#nom = data.nom;
            this.#description = data.description;
            this.#groupe = data.groupe;
            this.#parent = data.parent;
            this.#chat = data.chat;
            this.#nb_messages = data.nb_messages;

            let mutationsFolders = arrayMutations(this.#folders, data.folders);
            let mutationsFiles = arrayMutations(this.#files, data.files);

            this.#folders = data.folders;
            this.#files = data.files;

            // emit event for each folders
            for (let i = 0, n = mutationsFolders.added.length; i < n; i++)
                this.emit(Folder.EVENT_NEW_FOLDER, mutationsFolders.added[i]);

            for (let i = 0, n = mutationsFolders.removed.length; i < n; i++)
                this.emit(Folder.EVENT_REMOVE_FOLDER, mutationsFolders.removed[i]);
                
            // emit event for each files    
            for (let i = 0, n = mutationsFiles.added.length; i < n; i++)
                this.emit(Folder.EVENT_NEW_FILE, mutationsFiles.added[i]);

            for (let i = 0, n = mutationsFiles.removed.length; i < n; i++)
                this.emit(Folder.EVENT_REMOVE_FILE, mutationsFiles.removed[i]);

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
            this.emit(Folder.EVENT_NEW_FOLDER, r.id);

            return r;
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