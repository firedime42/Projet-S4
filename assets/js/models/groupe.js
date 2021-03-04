(function () {

    function _indexOfProp(array, key, value) {
        let nb_values = array.length;
        let i = 0;

        while (i < nb_values && array[i][key] != value) i++;

        return (i < nb_values) ? i : -1;
    }

    /**
     * Génère une nouvelle instance d'{Erreur} avec le code entrée en paramètre
     * @param {Number} errcode le code de l'erreur
     */
    function _error(errcode) {
        let err = new Error();
        err.code = errcode;
        return err;
    }


    async function __getGroupeInfo(id, time = 0) {
        let r = await fetch("/core/controller/groupe.php", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
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
        #nom;
        #descr;
        #status;
        #root;
        #nb_membre;

        constructor() {
        }

        async load(id) {
            let r =  __getGroupeInfo(id, time = 0);
    
            if (r instanceof Error) return r;
    
            this.setData(r);

            return this;
        }

        setData(groupe) {
            this.#id = groupe.id || this.#id;
            this.#nom = groupe.nom || this.#nom;
            this.#descr = groupe.descr || this.#descr;
            this.#root = groupe.root || this.#root;
            this.#status = groupe.status || this.#status;
            this.#nb_membre = groupe.nb_membre || this.#nb_membre;
        }

        get id() { return this.#id; }
        get nom() { return this.#nom; }
        get descr() { return this.#descr; }
        get root() { return this.#root; }
        get status() { return this.#status; }
        get nb_membre() { return this.#nb_membre; }
    }

    class GroupeManager {
        #groupes;

        constructor () {
            this.#groupes = {};
        }

/** A FINIR */

        get(id) { return this.#groupes[id]; }


    }

    window.Groupe = Groupe;

    window.GROUPES = new GroupeManager();
})();