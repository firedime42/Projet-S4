(function () {

    function _indexOfProp(array, key, value) {
        let nb_values = array.length;
        let i = 0;

        while (i < nb_values && array[i][key] != value) i++;

        return (i < nb_values) ? i : -1;
    }


    async function __getGroupeInfo(id, time = 0) {
        let r = await fetch("/core/controller/groupe.php", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'info',
                id,
                time
            })
        });

        let rdata = await r.json();

        if (!rdata.success)
            return _error(rdata.error);

        return rdata;
    }

    class Groupe extends Listenable {
        static FORMAT_NOM = /^[\w ÄÅÇÉÑÖÜáàâäãåçéèêëíìîïñóòôöõúùûüÂÊÁËÈÍÎÏÌÓÔÒÚÛÙ]{3,50}$/;
        static FORMAT_DESCRIPTION = /^.{0,255}$/;

        static EVENT_UPDATE = "update";

        #id;
        #lastUpdate;
        #nom;
        #description;

        #newNom;
        #newDescription;

        #creator_id;
        #status;
        #root;
        #nb_membres;
        #nb_messages;
        #nb_files;

        #lastCheck;

        constructor(id) {
            super();

            this.#id = id;
            this.#lastUpdate = 0;
            this.#lastCheck = 0;
        }

        /**
         * Récupère les informations depuis le serveur et les actualise si elles ont changées
         */
        async pull() {
            //if (Date.now() - this.#lastCheck < 1000) return this;

            //let r = await __getGroupeInfo(this.#id, this.#lastUpdate);
            let r = await request("/core/controller/groupe.php", {
                action: 'info',
                id: this.#id,
                time: this.#lastUpdate
            });

            this.#lastCheck = Date.now();
    
            if (r instanceof Error) return r;

            if (r.groupe != null) this.setData(r.groupe);

            return this;
        }


        async push() {
            let nom = this.#newNom || this.#nom;
            let description = this.#newDescription || this.#description;

            if (!Groupe.FORMAT_NOM.test(nom)) return _error(2301);
            if (!Groupe.FORMAT_DESCRIPTION.test(description)) return _error(2302);

            let r = await request("/core/controller/groupe.php", {
                action: 'push',
                id: this.#id,
                nom: nom,
                description: description
            });

            this.#newNom = null;
            this.#newDescription = null;

            if (r instanceof Error) return r;

            this.#nom = nom;
            this.#description = description;

            return this;
        }

        /**
         * envoi une requete pour rejoindre un groupe
         */
        async join() {
            if (this.#status == 'accepted' || this.#status == 'pending') return _error(-1);

            let r = await request("/core/controller/groupe.php", {
                action: "join",
                id: this.#id
            });

            if (r instanceof Error) return r;

            this.#status = r.status;
            this.emit(Groupe.EVENT_UPDATE);

            return this;
        }

        /**
         * quitter le groupe
         */
        async leave() {
            if (this.#status != 'accepted' && this.#status != 'pending') return _error(-1);

            let r = await request("/core/controller/groupe.php", {
                action: "leave",
                id: this.#id
            });

            if (r instanceof Error) return r;

            this.#status = 'left';
            this.emit(Groupe.EVENT_UPDATE);

            return this;
        }


        /**
         * Renvoi la liste des roles
         * @returns 
         */
        async getRoles() {
            if (this.#status != 'accepted') return _error(-1);

            let r = await request("/core/controller/groupe.php", {
                action: 'getRoles',
                group_id: this.#id
            });

            if (r instanceof Error) return r;

            return r.roles;
        }

        /**
         * modifie les roles
         * @param {Array} edited
         * @param {Array} removed
         * @param {Array} added
         */
        async setRoles(edited, removed, added) {
            if (this.#status != 'accepted') return _error(-1);

            let r = await request("/core/controller/groupe.php", {
                action: 'editRoles',
                group_id: this.#id,
                edited,
                removed,
                added
            });

            if (r instanceof Error) return r;

            return true;
        }

        /**
         * Recupère la liste des candidats au groupe
         */
        async getCandidates() {
            let r = await request('/core/controller/groupe.php', {
                action: 'getApplications',
                group: this.#id
            });

            if (r instanceof Error) return r;

            return r.applications;
        }

        /**
         * Accepte un utilisateur dans le groupe
         * @param {Number} user_id l'identifiant de l'utilisateur
         */
        async accept(user_id) {
            let r = await request('/core/controller/groupe.php', {
                action: 'acceptUser',
                group: this.#id,
                id: user_id
            });

            if (r instanceof Error) return r;

            return true;
        }

        /**
         * Refuse un utilisateur dans le groupe
         * @param {Number} user_id l'identifiant de l'utilisateur
         */
        async refuse(user_id) {
            return await this.kick(user_id);
        }

        /**
         * Recupère la liste des membres du groupe.
         */
        async getMembres() {
            let r = await request('/core/controller/groupe.php', {
                action: 'getMembers',
                group: this.#id
            });

            if (r instanceof Error) return r;

            return r.members;
        }

        /**
         * Eject un utilisateur du serveur.
         * @param {Number} user_id l'identifiant de l'utilisateur
         */
        async kick(user_id) {
            let r = await request('/core/controller/groupe.php', {
                action: 'kickUser',
                group: this.#id,
                id: user_id
            });

            if (r instanceof Error) return r;

            return true;
        }

        /**
         * Affect un role à l'Utilisateur
         * @param {Number} user_id l'identifiant de l'utilisateur
         * @param {Number} role_id l'identifiant du role à affecté à l'utilisateur
         */
        async setRole(user_id, role_id) {

        }

        setData(groupe) {
            let exists = (a, b) => (a != null && a != undefined) ? a : b;
            this.#nom = exists(groupe['nom'], this.#nom);
            this.#description = exists(groupe['description'], this.#description);
            this.#creator_id = exists(groupe['creator_id'], this.#creator_id);
            this.#root = exists(groupe['root'], this.#root);
            this.#status = exists(groupe['status'], this.#status);
            this.#nb_membres = exists(groupe['nb_membres'], this.#nb_membres);
            this.#nb_messages = exists(groupe['nb_messages'], this.#nb_messages);
            this.#nb_files = exists(groupe['nb_files'], this.#nb_files);
            this.#lastUpdate = exists(groupe['lastUpdate'], this.#lastUpdate);

            this.emit(Groupe.EVENT_UPDATE);
        }

        get id() { return this.#id; }
        get nom() { return this.#newNom || this.#nom; }
        get description() { return this.#newDescription || this.#description; }
        get creator_id() { return this.#creator_id; }
        get root() { return this.#root; }
        get status() { return this.#status; }
        get nb_files() { return this.#nb_files; }
        get nb_membres() { return this.#nb_membres; }
        get nb_messages() { return this.#nb_messages; }

        get lastPullRequest() { return this.#lastCheck; }

        set nom(nom) { this.#newNom = nom; }
        set description(descr) { this.#newDescription = descr; }
    }

    class GroupeManager {
        #groupes;
        #waiting;

        constructor () {
            this.#groupes = {};
            this.#waiting = {};
        }

        get(id) {
            id *= 1;

            if (this.#waiting[id]) return this.#waiting[id];

            let _this = this;

            // creer ou récuperer le groupe
            if (!this.#groupes[id]) this.#groupes[id] = new Groupe(id);

            // on compare à la version sur la db si la derniere mise à jour était il y a plus de 5s
            if ( Date.now() - this.#groupes[id].lastPullRequest > 5000 ) {
                this.#waiting[id] = this.#groupes[id].pull();
                this.#waiting[id].then(function () { _this.#waiting[id] = null; });
            }

            // verifier/recuperer les infos sur le serveur
            return this.#waiting[id] || this.#groupes[id];
        }

        /**
         * renvoi l'identifiant du groupe
         * @param {*} nom 
         * @param {*} description 
         */
        async create(nom, description) {
            if (!Groupe.FORMAT_NOM.test(nom)) return _error(2301);
            if (!Groupe.FORMAT_DESCRIPTION.test(description)) return _error(2302);

            console.log(nom, description);
            let r = await request("/core/controller/groupe.php", {
                action: 'create',
                nom: nom,
                description: description
            });

            if (r instanceof Error) return r;

            return r.groupe;
        }
    }

    window.Groupe = Groupe;

    window.GROUPES = new GroupeManager();
})();