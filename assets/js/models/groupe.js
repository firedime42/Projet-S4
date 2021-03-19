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
            if (new Date().getTime() - this.#lastCheck < 5000) return this;

            let r = await __getGroupeInfo(this.#id, this.#lastUpdate);

            this.lastCheck = new Date().getTime();
    
            if (r instanceof Error) return r;
    
            if (r.groupe != null) this.setData(r.groupe);

            return this;
        }

        setData(groupe) {
            this.#nom    = groupe.nom    || this.#nom;
            this.#descr  = groupe.descr  || this.#descr;
            this.#root   = groupe.root   || this.#root;
            this.#status = groupe.status || this.#status;
            this.#nb_membres = groupe.nb_membres || this.#nb_membres;
            this.#lastUpdate = groupe.lastUpdate || this.#lastUpdate;
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