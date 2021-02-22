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
        #groupe;

        constructor() {
        }

        async load(id) {
            let r = await fetch("/core/controller/groupe.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify({
                    action: 'info',
                    id
                })
            });
    
            let rdata = await r.json();
    
            if (!rdata.success)
                return _error(rdata.error);
    
            this.#groupe = rdata.groupe;

            return rdata.groupe;
        }

        
    }

})();