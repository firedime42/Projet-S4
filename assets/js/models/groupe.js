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

    class Groupe {
        #id;
        #lastUpdate;
        #nom;
        #descr;
        #status;
        #root;
        #nb_membres;

        #lastCheck;

        constructor(id) {
            this.#id = id;
            this.#lastUpdate = 0;
            this.#lastCheck = 0;
        }

        /**
         * Récupère les informations depuis le serveur et les actualise si elles ont changées
         */
        async pull() {
            if (Date.now() - this.#lastCheck < 5000) return this;

            //let r = await __getGroupeInfo(this.#id, this.#lastUpdate);
            let r = await request("/core/controller/groupe.php", {
                action: 'info',
                id: this.#id,
                time: this.#lastUpdate
            });

            this.lastCheck = Date.now();
    
            if (r instanceof Error) return r;
    
            if (r.groupe != null) this.setData(r.groupe);

            return this;
        }

        /**
         * envoi une requete pour rejoindre un groupe
         */
        async join() {
            if (this.#status == 'membre' || this.#status == 'candidat') return _error(-1);

            let r = await request("/core/controller/groupe.php", {
                action: "join",
                id: this.#id
            });

            if (r instanceof Error) return r;

            this.#status = r.status;

            return this;
        }

        setData(groupe) {
            let exists = (a, b) => (a != null && a != undefined) ? a : b;
            this.#nom = exists(groupe['nom'], this.#nom);
            this.#descr = exists(groupe['descr'], this.#descr);
            this.#root = exists(groupe['root'], this.#root);
            this.#status = exists(groupe['status'], this.#status);
            this.#nb_membres = exists(groupe['nb_membres'], this.#nb_membres);
            this.#lastUpdate = exists(groupe['lastUpdate'], this.#lastUpdate);
        }

        get id() { return this.#id; }
        get nom() { return this.#nom; }
        get descr() { return this.#descr; }
        get root() { return this.#root; }
        get status() { return this.#status; }
        get nb_membres() { return this.#nb_membres; }
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
            this.#groupes[id] = this.#groupes[id] || new Groupe(id);

            this.#waiting[id] = this.#groupes[id].pull();
            this.#waiting[id].then(function () { _this.#waiting[id] = null; });

            // verifier/recuperer les infos sur le serveur
            return this.#waiting[id];
        }
    }

    window.Groupe = Groupe;

    window.GROUPES = new GroupeManager();
})();