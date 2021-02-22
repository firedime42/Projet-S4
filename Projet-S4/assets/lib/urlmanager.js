<<<<<<< Updated upstream
/**
 * changer l'url sans recharger la page , interception des clicks sur les liens
 * 
 * Supports
 * @author Mattéo M. (MattJP)
 * @required Dom.js
 */

(function (window, document) {

    var History = window.history;

    /**
     * Fonction pour creer une fonction qui emet des evenements
     * @param {Function} f la fonction qui doit emmetre des evenements sur le
     * @param {String} eventname le nom de l'evenement
     */
    function _createEventTrigger(f, eventname) {
        return function () {
            let result = f.apply(this, arguments);
            let e = new Event(eventname);
            e.arguments = arguments;
            window.dispatchEvent(e);
            return result;
        }
    }

    History.pushState = _createEventTrigger(History.pushState, "pushstate");// when history is changed by scripts
    window.addEventListener('popstate', () => window.dispatchEvent(new Event("pushstate")));// when history is changed by user


    /**
     * Fonction qui permet de se rendre sur une page
     * @param {String} url 
     */
    function goTo (url) {
        // ajout dans l'historique du navigateur
        History.pushState({ title: document.title }, document.title, url);
    };

    // chargement de la page local
    Dom.onLoad(window, () => {
        // interception des clics sur les liens
        Dom.onClick(Dom.body, (event) => {
            // on empêche la suivi du lien
            event.preventDefault();
            event.returnValue = false;

            // on cherche un lien activé
            var activedlink = false;
            for (let i = 0, l = event.path.length - 1; i <= l && !activedlink; i++) {
                let element = event.path[i];
                if (element.attributes && element.attributes['href']) {
                    activedlink = true;
                    goTo(element.attributes['href'].value);
                }
            }
            
            // on bloque éventuellement les prochains eventlisteners
            return !activedlink;
        });
    });

    class URLParser {
        constructor (urlpattern) {

        }

        match(url, f) {
            
        }

    }


    window.setURL = goTo;
=======
/**
 * changer l'url sans recharger la page , interception des clicks sur les liens
 * 
 * Supports
 * @author Mattéo M. (MattJP)
 * @required Dom.js
 */

(function (window, document) {

    var History = window.history;

    /**
     * Fonction pour creer une fonction qui emet des evenements
     * @param {Function} f la fonction qui doit emmetre des evenements sur le
     * @param {String} eventname le nom de l'evenement
     */
    function _createEventTrigger(f, eventname) {
        return function () {
            let result = f.apply(this, arguments);
            let e = new Event(eventname);
            e.arguments = arguments;
            window.dispatchEvent(e);
            return result;
        }
    }

    History.pushState = _createEventTrigger(History.pushState, "pushstate");// when history is changed by scripts
    window.addEventListener('popstate', () => window.dispatchEvent(new Event("pushstate")));// when history is changed by user


    /**
     * Fonction qui permet de se rendre sur une page
     * @param {String} url 
     */
    function goTo (url) {
        // ajout dans l'historique du navigateur
        History.pushState({ title: document.title }, document.title, url);
    };

    // chargement de la page local
    Dom.onLoad(window, () => {
        // interception des clics sur les liens
        Dom.onClick(Dom.body, (event) => {
            // on empêche la suivi du lien
            event.preventDefault();
            event.returnValue = false;

            // on cherche un lien activé
            var activedlink = false;
            for (let i = 0, l = event.path.length - 1; i <= l && !activedlink; i++) {
                let element = event.path[i];
                if (element.attributes && element.attributes['href']) {
                    activedlink = true;
                    goTo(element.attributes['href'].value);
                }
            }
            
            // on bloque éventuellement les prochains eventlisteners
            return !activedlink;
        });
    });


    class URLrooter {
        #listeners;

        constructor () {
            this.#listeners = [];

            let _this = this;
            Dom.addListener(window, 'pushstate', function () { _this.urlchange(); });
        }

        addListener(path, match, unmatch, tests={}) {
            if (path.startsWith('/')) path = path.substr(1);// remove the first char

            let _path = path.split('/');
            let nb_nodes = _path.length;

            let varnames = [];
            let varvalues = [];
            let vartests = [];

            for (let i = 0; i < nb_nodes; i++) {
                if (_path[i][0] == '$') {
                    let varname = _path[i].substr(1);
                    let test = tests[varname];
                    varnames.push(varname);
                    varvalues.push(i);
                    vartests.push(
                        (test instanceof RegExp) ? test.test :
                        (typeof test == 'function') ? test :
                        null
                    );
                    _path[i] = '*';
                } else vartests.push(null);
            }

            // gerer le cas ou l'url est déjà actuelle
            let r = this.parseURL('/'+path, tests);
            if (typeof r == 'object' && typeof match == 'function') match(r);

            this.#listeners.push({
                path: _path,
                deep: nb_nodes,
                varnames,
                varvalues,
                vartests,
                match,
                unmatch,
                matchPrevUrl: true
            });
        }

        execListener(url, listener, match) {
            if (match && typeof listener.match == 'function') {
                let varobject = {};
                let nb_vars = listener.varnames.length;

                for (let i = 0; i < nb_vars; i++)
                    varobject[listener.varnames[i]] = url[listener.varvalues[i]];

                listener.match(varobject);
            } else if (listener.matchPrevUrl && typeof listener.unmatch == 'function')
                listener.unmatch();
        }

        urlchange() {
            let _url = window.location.pathname.substr(1).split('/');

            let nb_listeners = this.#listeners.length;
            let urldeep = _url.length;

            let survivor = new Array(nb_listeners);
            let nb_survivor = 0;

            // on filtre une première fois
            for (let j = 0; j < nb_listeners; j++) {
                let listener = this.#listeners[j];
                let deep = listener.deep;

                if (listener.deep == urldeep || (listener.deep < urldeep && listener.path[deep - 1] == "*"))
                    survivor[nb_survivor++] = j;
                else
                    this.execListener(_url, listener, false);
            }

            nb_listeners = nb_survivor;
            
            // on filtre le reste
            for (let i = 0; i < urldeep && nb_listeners > 0; i++) {
                let nb_survivor = 0;

                for (let j = 0; j < nb_listeners; j++) {
                    let listener = this.#listeners[survivor[j]];

                    if (listener.path[i] != _url[i] && listener.path[i] != "*")
                        this.execListener(_url, listener, false);
                    else if (listener.path[i] == '*' && listener.vartests[i] != null && !listener.vartests[i](_url[i]))
                        this.execListener(_url, listener, false);
                    else if (listener.deep > i + 1)
                        survivor[nb_survivor++] = survivor[j];
                    else
                        this.execListener(_url, listener, true);
                }

                nb_listeners = nb_survivor;
            }
        }

        parseURL(pattern, tests = {}) {
            let url = window.location.pathname.split('/');
            pattern = pattern.split('/');
            let data = {};
            let pattern_match = true;

            let nb_nodes_pattern = pattern.length;
            let nb_nodes_url = url.length;

            let nb_min_nodes = Math.min(nb_nodes_pattern, nb_nodes_url);

            let i = 0;
            

            while (pattern_match && i < nb_min_nodes) {
                
                if (pattern[i][0] == '$') {
                    let varname = pattern[i].substr(1);
                    let test = tests[varname];
                    pattern_match = (test instanceof RegExp) ? test.test(url[i]) : (typeof test == 'function') ? test(url[i]) : true;
                    data[varname] = url[i];
                } else
                    pattern_match = pattern[i] == url[i] || pattern[i] == "*";

                i++;
            }
            
            pattern_match = pattern_match && (
                nb_nodes_pattern == nb_nodes_url ||
                (nb_nodes_pattern < nb_nodes_url && pattern[i - 1] == '*')
            );

            return (pattern_match) ? data : false;
        }
    }


    window.setURL = goTo;
    window.URLrooter = new URLrooter();
>>>>>>> Stashed changes
})(window, document);