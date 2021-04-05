(function() {

    class Upload extends Listenable {
        /* Contraintes et paramètre par défaut */
        static DEFAULT_PART_SIZE = 32 * 1024; // 32 Ko
        static MIN_PART_SIZE = 1024; // 1 Ko

        /* Evenements */
        static EVENT_START = "start";
        static EVENT_END = "end";
        static EVENT_ERROR = "error";
        static EVENT_PROGRESS = "progress";

        /* Propriétés */
        #uploadStarted; // boolean indiquant si l'envoi à commencé
        #uploadEnded;   // boolean indiquant si l'envoi est terminé

        #url;           // url de reception du fichier
        #id;            // identifiant du fichier
        #file;          // fichier

        #part_size;     // taille des morceaux
        #nb_s_parts;    // nombre de morceaux déjà envoyé
        #nb_t_parts;    // nombre de morceaux total

        #start_time;    // timestamp du debut de l'envoi
        #total_duration;// durée total de l'envoi

        /**
         * Genère un flux d'envoi fractionné
         * @param {String} url 
         * @param {Number} id
         * @param {File} file
         */
        constructor(url, id, file) {
            super();
            
            this.#uploadStarted = false;
            this.#uploadEnded   = false;

            this.#url = url;
            this.#id = id;
            this.#file = file;
        }

        /**
         * Envoi du fichier
         */
        async send(part_size = 0) {
            this.uploadStarted = true;

            this.#part_size = (Number.isInteger(part_size) && part_size > Upload.MIN_PART_SIZE) ? part_size : Upload.DEFAULT_PART_SIZE;

            this.#nb_s_parts = 0;
            this.#nb_t_parts = Math.ceil(this.#file.size / this.#part_size);

            this.emit(Upload.EVENT_START);

            this.#start_time = (performance || Date).now();

            for (let part_num = 0; part_num < this.#nb_t_parts; part_num++)
                await this.__sendPart(part_num);

            this.#total_duration = (performance || Date).now() - this.#start_time;
            this.emit(Upload.EVENT_END);

            return this;
        }

        /**
         * Envoi d'une parti du fichier
         */
        async __sendPart(part_num) {
            let _this = this;

            let total_size = this.#file.size;
            let start_octet = part_num * this.#part_size;
            let part_blob = this.#file.slice(start_octet, start_octet + this.#part_size);

            let header = new Uint32Array([
                this.#id,
                part_blob.size,
                start_octet
            ]);

            let start_time = (performance || Date).now();

            let r = await (
                fetch(this.#url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/octet-stream'
                    },
                    body: new Blob([ new Blob([ header.buffer ]), part_blob ])
                }).catch(function (e) {
                    _this.emit(Upload.EVENT_ERROR, e);
                })
            );

            let end_time = (performance || Date).now();
            
            let upload_part_duration = end_time - start_time;

            this.#nb_s_parts++;
            this.#total_duration = end_time - this.#start_time;

            this.emit(Upload.EVENT_PROGRESS, {
                speed: part_blob.size / upload_part_duration,
                duration: upload_part_duration
            });
        }

        get url() { return this.#url; }
        get id() { return this.#id; }
        get file() { return this.#file; }
        get part_size() { return this.#part_size; }
        get nb_parts() { return this.#nb_t_parts; }
        get start_time() { return this.#start_time; }
        get total_duration() { return this.#total_duration; }

        get started() { return this.#uploadStarted; }
        get ended() { return this.#uploadEnded; }

        getNbPartsSended() { return this.#nb_s_parts; }
    }

    class WazapFile extends Listenable {
        static EVENT_UPDATE = 'update';

        #id;                // identifiant du fichier
        #lastUpdate;        // timestamp de la derniere version

        #nom;               // nom du fichier
        #description;       // description du fichier

        #newNom;            // nouveau nom du fichier
        #newDescription;    // nouvelle description

        #auteur;            // identifiant de l'auteur du fichier

        #type;              // MIME type du fichier
        #etat;              // etat du fichier : pending / uploading / online
        #size;              // taille du fichier en octet

        #rename;            // boolean indiquant si le fichier peut être modifié
        #delete;            // boolean indiquant si le fichier peut être suprimé
        #liked;             // boolean indiquant si l'utilisateur a aimé le fichier

        #nb_likes;          // nombre de "like" du fichier
        #nb_comments;       // nombre de commentaire sur le fichier

        #chat;              // identifiant du chat

        constructor(id = null) {
            super();

            this.#id = (Number.isInteger(id) && id >= 0) ? id : null;
            this.#lastUpdate = 0;
        }

        /**
         * Recupère les informations depuis le serveur
         */
        async pull() {
            if (this.#id == null) return _error(-1);
            // test if requete recente

            // ask server
            let r = await request("/core/controller/file.php", {
                action: 'pull',
                id: this.#id,
                lastUpdate: this.#lastUpdate
            });

            if (r instanceof Error) return r;

            if (r.file != null) this.__parseData(r.file);

            return this;
        }

        /**
         * Telecharge le fichier
         * on ne stoque pas le fichier : on laisse le soin au navigateur
         * et au serveur de gérer le cache pour ces données lourdes
         */
        download() {
            console.log(this);
            if (this.#id == null) return _error(-1);
            if (this.#etat != 'online') return _error(-1);

            let r = ExPromise();
            fetch("/core/controller/download.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: this.#id
                })
            })
            .then(res => res.blob())
            .catch(err => r.resolve(err))
            .then(b => r.resolve(b));

            return r;
        }

        /**
         * Met à jour les informations sur le serveur
         */
        async push() {
            if (this.#id == null) return _error(-1);
            if (this.#newNom == null && this.#newDescription == null) return _error(-1);
            if (this.#rename == false) return _error(-1);

            let r = await request("/core/controller/file.php", {
                action: 'push',
                id: this.#id,
                nom: this.#newNom,
                description: this.#newDescription
            });

            if (r instanceof Error) {
                this.#nom = this.#newNom;
                this.#description = this.#newDescription;
            }

            this.#newNom = null;
            this.#newDescription = null;

            return (r instanceof Error) ? r : this;
        }

        /**
         * Crée le fichier
         */
        async create(folder_id, file) {
            if (this.#id != null) return _error(-1);

            let r = await request("/core/controller/file.php", {
                action: 'create',
                nom: this.#newNom,
                description: this.#newDescription,
                folder: folder_id,
                type: file.type,
                size: file.size
            });

            if (r instanceof Error) return r;

            this.#id = r.id;
            await this.pull();

            return this.upload(file);
        }

        async remove() {
            if (this.#id != null) return _error(-1); // identifiant null
            if (!this.#delete) return _error(-1);    // autorisation manquante

            let r = await request("/core/controller/file.php", {
                action: 'remove',
                id: this.#id
            });

            if (r instanceof Error) return r;

            WFILES.free(this.#id);

            return true;
        }

        /**
         * Upload un fichier
         * @param {File} file 
         */
        upload(file) {
            console.log(this.#etat);
            if (this.#etat != "uploading") return false;

            var _this = this;
            var u = new Upload("/core/controller/upload.php", this.#id, file);

            u.send().then(function () {
                _this.__upload_end();
            });

            return u;
        }

        /**
         * Averti le serveur que le fichier a été envoyé
         * @returns 
         */
        async __upload_end() {
            let r = await request("/core/controller/file.php", {
                action: 'end-upload',
                id: this.#id
            });

            if (r instanceof Error) return r;

            this.#etat = "online";

            console.log(this);
        }


        get id() { return this.#id; }
        get lastUpdate() { return this.#lastUpdate; }
        get nom() { return this.#newNom || this.#nom; }
        get description() { return this.#newDescription || this.#description; }
        get size() { return this.#size; }
        get type() { return this.#type; }
        get etat() { return this.#etat; }

        get isLiked () { return this.#liked; }
        get canRemove () { return this.#delete; }

        get nb_likes() { return this.#nb_likes; }
        get nb_comments() { return this.#nb_comments; }

        set nom(nom) { this.#newNom = nom; }
        set description(descr) { this.#newDescription = descr; }

        async like() {}
        async unlike() {}

        getChat() { return null; }

        __parseData(data) {
            let exists = (a, b) => (a != null && a != undefined) ? a : b;
            this.#nom = exists(data['nom'], this.#nom);
            this.#description = exists(data['description'], this.#description);
            this.#auteur = exists(data['auteur'], this.#auteur);
            this.#type = exists(data['type'], this.#type);
            this.#etat = exists(data['etat'], this.#etat);
            this.#size = exists(data['size'], this.#size);
            this.#nb_likes = exists(data['nb_likes'], this.#nb_likes);
            this.#nb_comments = exists(data['nb_comments'], this.#nb_comments);
            this.#rename = exists(data['rename'], this.#rename);
            this.#delete = exists(data['delete'], this.#delete);
            this.#liked = exists(data['liked'], this.#liked);
            this.#lastUpdate = exists(data['lastUpdate'], this.#lastUpdate);

            this.emit(WazapFile.EVENT_UPDATE);
        }
    }

    class WazapFileManager {
        #files;
        constructor () {
            this.#files = {};
        }

        __valideID(id) { return Number.isInteger(id) && id >= 0; }


        /**
         * Recupère un fichier
         * @param {Number} id l'identifiant du fichier
         */
        async get(id) {
            // verification du type de l'id
            if (!this.__valideID(id)) return null;

            // on crée le fichier s'il n'existe pas
            if (!this.#files[id]) this.#files[id] = new WazapFile(id);

            // on récupère les données depuis le serveur
            return await this.#files[id].pull();
        }

        /**
         * Ajoute un fichier
         */
        add(wfile) {
            if (!(wfile instanceof WazapFile)) return false;
            if (!this.__valideID(wfile.id)) return false;
            if (this.#files[wfile.id]) return false;

            this.#files[wfile.id] = wfile;

            return true;
        }
        
        
        /**
         * Récupère un ensemble de fichier
         * @param {Array<Number>} list_id
         *./
        async gets(list_id) {
            let nb_files = list_id.length;
            let r_files = {};
            let files = {};

            // preparation de la requete
            for (let i = 0; i < nb_files; i++) {
                let id = list_id[i];

                // creation du fichier s'il n'existe pas
                if (!this.#files[id]) this.#files[id] = new WazapFile(id);

                r_files[id] = this.#files[id].lastUpdate;
            }

            // get all files
            let r = await request("/core/controller/file.php", {
                action: 'files',
                files: r_files
            });

            if (r instanceof Error) return r;

            for (let i = 0; i < nb_files; i++)
                if (r.files[i] != null)
                    files[i].__parseData(r.files[i]);

            return files;
        }
        /**/

        /**
         * Libère l'espace ram contenant les informations des fichiers dont l'identifiant est dans la liste passé en paramètre
         */
        free(list_id) {
            let nb_files = list_id.length;
            for (let i = 0; i < nb_files; i++) delete this.#files[list_id[i]];
        }
    }

    window.WFile = WazapFile;
    window.Upload = Upload;
    window.WFILES = new WazapFileManager();
})();