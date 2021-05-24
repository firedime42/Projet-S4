(function () {

    function valideID(id) { return Number.isInteger(id) && id >= 0; }
    function notEmptyString(str) { return typeof str == 'string' && str.length > 0; }
    function notNullOrUndefined(obj) { return obj !== null && obj !== undefined; }
    function isString(str) { return typeof str == 'string'; }
    function valideUser(user) { return typeof user == 'object' && valideID(user.id) && notEmptyString(user.name); }
    function valideStatus(status) { return notEmptyString(status) && ['pending', 'accepted', 'refused', 'excluded', 'left'].indexOf(status) !== -1; }

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

        #creator;
        #status;
        #root;
        #nb_membres;
        #nb_messages;
        #nb_files;
        #dashboard;

        #permissions;

        #lastCheck;

        constructor(id) {
            super();

            this.#id = id;
            this.#lastUpdate = 0;
            this.#lastCheck = 0;
        }

        /**
         * Récupère les informations depuis le serveur et les actualise si elles ont changées
         * @param {Boolean} forced indique si la requête doit imperativement être effectué
         */
        async pull(forced=false) {
            //let r = await __getGroupeInfo(this.#id, this.#lastUpdate);
            let r = await request("/core/controller/groupe.php", {
                action: 'info',
                id: this.#id,
                time: this.#lastUpdate
            });

            this.#lastCheck = Date.now();
    
            if (r instanceof Error) return r;

            if (r.groupe != null) this.__parseData(r.groupe);

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

        async loadDashboard() {
            let r = await request('/core/controller/groupe.php', {
                action: 'getDashboard',
                group: this.#id
            });

            if (r instanceof Error) return r;

            this.__parseData(r.group);

            return this.#dashboard;
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
            let r = await request('/core/controller/groupe.php', {
                action: 'setRole',
                group: this.#id,
                user: user_id,
                role: role_id
            });

            if (r instanceof Error) return r;

            return true;
        }

        __parseData(data) {

            if (notEmptyString(data.nom)) this.#nom = data.nom;
            if (isString(data.description)) this.#description = data.description;
            if (valideUser(data.creator)) this.#creator = USERS.getWithoutPull(data.creator.id).__parseData(data.creator);
            if (valideID(data.root)) this.#root = data.root;
            if (valideStatus(data.status)) this.#status = data.status;
            if (Number.isInteger(data.nb_members)) this.#nb_membres = data.nb_members;
            if (Number.isInteger(data.nb_messages)) this.#nb_messages = data.nb_messages;
            if (Number.isInteger(data.nb_files)) this.#nb_files = data.nb_files;
            if (notNullOrUndefined(data.permissions)) this.#permissions = data.permissions;
            if (notNullOrUndefined(data.lastUpdate)) this.#lastUpdate = data.lastUpdate;
            if (typeof data.dashboard == 'object') this.#dashboard = data.dashboard;

            this.emit(Groupe.EVENT_UPDATE);
        }

        get id() { return this.#id; }
        get nom() { return this.#newNom || this.#nom; }
        get description() { return this.#newDescription || this.#description; }
        get creator() { return this.#creator; }
        get root() { return this.#root; }
        get status() { return this.#status; }
        get nb_files() { return this.#nb_files; }
        get nb_membres() { return this.#nb_membres; }
        get nb_messages() { return this.#nb_messages; }
        get permissions() { return this.#permissions; }
        get dashboard() { return this.#dashboard; }

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