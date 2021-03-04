(function () {
    
    const MAX_RESULTS = 40;
    const RESULTS_PER_PAGE = 10;

    /**
     * Génère une nouvelle instance d'{Erreur} avec le code entrée en paramètre
     * @param {Number} errcode le code de l'erreur
     */
    function _error(errcode) {
        let err = new Error();
        err.code = errcode;
        return err;
    }

    async function _req(url, query, page_first, nb_results) {
        let r = await fetch(url, {
            method: "POST",
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify({
                action: 'search',
                query: query,
                page_first,
                nb_results
            })
        });
        rdata = await r.json();

        if (!rdata.success) _error(rdata.error);

        return rdata.results;
    }

    class Search extends Listenable {
        #url;
        #query;
        #rpp;
        #results = [];
        #lastpage = 0;


        constructor(url) {
            super();

            this.#url = url;
            this.#query = null;
        }

        __append(data) {
            this.#results.push(data);
            this.emit("append", data);
        }

        async loadMore() {
            let results = await _req(this.#url, this.#query, this.#lastpage, this.#rpp);
            let nb_results = results.length;
            this.#lastpage+=nb_results;
            for (let i = 0; i < nb_results; i++)
                this.__append(results[i]);
        }

        getAll() {
            return this.#results;
        }

        async search(query, rpp = RESULTS_PER_PAGE) {
            if (query == this.#query) return;

            this.#query = query;
            this.#rpp = rpp;

            this.#lastpage = 0;
            this.#results = [];
            
            this.emit("newSearch");

            await this.loadMore();

            return this.#results;
        }
    }

    window.Search = Search;

})(window);