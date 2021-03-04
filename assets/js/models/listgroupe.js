(function () {

    /**
     * Recherche
     * @param {Array} array 
     * @param {String} key 
     * @param {*} value 
     */
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

    class ListGroupe extends Listenable {
        #list;
        #lasttime;

        static EVENT_APPEND = 'append';
        static EVENT_CHANGE = 'change';
        static EVENT_REMOVE = 'remove';

        static STATUS_MEMBRE = 'membre';
        static STATUS_INVITE = 'invite';
        static STATUS_CANDIDAT = 'candidat';
        static STATUS_EX = 'ex';


        constructor() {
            super();
            this.#list = [];
            this.#lasttime = 0;
        }

        /**
         * Met à jour la liste en ajoutant ou supprimant un groupe
         * @param {Object} groupe 
         */
        __update(groupe) {
            let p = _indexOfProp(this.#list, 'id', groupe.id);
            if (p == -1 && groupe.status != ListGroupe.STATUS_EX) {
                this.#list.push(groupe);
                this.emit('append', groupe);
            } else if (p >= 0 && groupe.status != ListGroupe.STATUS_EX) {
                let old = this.#list.splice(p, 1);
                this.#list.push(groupe);
                this.emit('change', old, groupe);
            } else if (p >= 0) {
                let old = this.#list.splice(p, 1);
                this.emit('remove', old);
            }
        }

        /**
         * Met à jour la liste des groupes de l'utilisateur
         */
        async update() {
            if (this.#lasttime < Math.floor(new Date().getTime() / 1000) - 12 * 60 * 60 * 1000) {
                this.#lasttime = 0;
                this.#list.length;
            }

            let r = await fetch("/core/controller/groupe.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify({
                    action: 'list',
                    time: this.#lasttime
                })
            });
            let rdata = await r.json();

            if (!rdata.success) {
                return _error(rdata.error);
            }

            let nb_updates = rdata.groups.length;

            for (let i = 0; i < nb_updates; i++)
                this.__update(rdata.groups[i]);
        }

        /**
         * Retourne la liste de l'utilisateur
         */
        list() {
            return this.#list;
        }
    }

    window.ListGroupe = ListGroupe;

})();