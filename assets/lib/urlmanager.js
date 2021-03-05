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

    /**
     * Indique si une liste contient un element
     * @param {Array<*>} list 
     * @param {*} element 
     */
    function __contient(list, element) {
        let i = 0;
        let nb_elements = list.length;
        while (i < nb_elements && list[i] != element) i++;
        return i < nb_elements;
    }

    History.pushState = _createEventTrigger(History.pushState, "pushstate");// when history is changed by scripts
    window.addEventListener('popstate', () => window.dispatchEvent(new Event("pushstate")));// when history is changed by user


    /**
     * Fonction qui permet de se rendre sur une page
     * @param {String} url 
     */
    function goTo (url) {
        // on formate l'url
        url = new URL(url, window.location.href);

        // verification d'url identique
        if (url.href == window.location.href) return;

        // cross-origin
        if (url.origin != window.location.origin) {
            return;
        }

        // ajout dans l'historique du navigateur
        History.pushState({ title: document.title }, document.title, url.href);
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

/*
    class URLrooter {
        #listeners;

        constructor () {
            this.#listeners = [];

            let _this = this;
            Dom.addListener(window, 'pushstate', function () { _this.urlchange(); });
        }

        /**
         * Ajout un ecouteur d'evenement
         * @param {String} pattern le pattern à detecter
         * @param {Function} match la fonction à executer lorsque l'url match <path>
         * @param {Function} unmatch fonction à executer lorsque l'url ne match pas <path> n'est executé que la première fois
         * @param {Object} tests Conditions sur les variables { nom variable => fonction ou regex }
         * /
        addListener(pattern, match, unmatch, tests={}) {
            if (pattern.startsWith('/')) pattern = pattern.substr(1);// remove the first char

            let _pattern = pattern.split('/');
            let nb_nodes = _pattern.length;

            let varnames = [];
            let varvalues = [];
            let vartests = [];

            for (let i = 0; i < nb_nodes; i++) {
                if (_pattern[i][0] == '$') {
                    let varname = _pattern[i].substr(1);
                    let test = tests[varname];
                    varnames.push(varname);
                    varvalues.push(i);
                    vartests.push(
                        (test instanceof RegExp) ? (str) => test.test(str) :
                        (typeof test == 'function') ? test :
                        null
                    );
                    _pattern[i] = '*';
                } else vartests.push(null);
            }

            // gerer le cas ou l'url est déjà actuelle
            let r = this.parseURL('/'+pattern, tests);
            if (typeof r == 'object' && typeof match == 'function') match(r);

            this.#listeners.push({
                pattern: _pattern,
                deep: nb_nodes,
                varnames,
                varvalues,
                vartests,
                match,
                unmatch,
                matchPrevUrl: true
            });
        }

        /**
         * execute un ecouteur
         * 
         * @warning NE DOIT PAS ETRE APPELER PAR LEXTERIEUR
         * @param {Array.<String>} url 
         * @param {Object} listener 
         * @param {Boolean} match indiquant si l'url match ou pas
         * /
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


        /**
         * fonction lancé lorsque l'url change
         * @warning NE DOIT PAS ETRE APPELER PAR LEXTERIEUR
         * /
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

                if (listener.deep == urldeep || (listener.deep < urldeep && listener.pattern[deep - 1] == "*"))
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

                    if (listener.pattern[i] != _url[i] && listener.pattern[i] != "*")
                        this.execListener(_url, listener, false);
                    else if (listener.pattern[i] == '*' && listener.vartests[i] != null && !listener.vartests[i](_url[i]))
                        this.execListener(_url, listener, false);
                    else if (listener.deep > i + 1)
                        survivor[nb_survivor++] = survivor[j];
                    else
                        this.execListener(_url, listener, true);
                }

                nb_listeners = nb_survivor;
            }
        }

        /**
         * Parse l'url courante
         * @param {String} pattern 
         * @param {Object} tests { varname => regex ou function }
         * /
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
*/

    class URLrooter {
        #listeners;
        constructor() {
            this.#listeners = [];
            
            let _this = this;
            Dom.addListener(window, 'pushstate', function () { _this.__URLchange(); });
        }

        /**
         * parse un pattern en une version precalculé
         * @param {String} pattern pattern d'url : /nom/(valeur1|valeur2)/$varname/*
         * @param {Object|undefined} tests fonction de tests des variables varname => RegEx | Function
         */
        __patternParse(pattern, tests) {
            var parsed_pattern;
            var vars = [];

            let _pattern = ((pattern[0] == '/') ? pattern.substr(1) : pattern).split('/');
            let len_pattern = _pattern.length;
            parsed_pattern = new Array(len_pattern);

            for (let i = 0; i < len_pattern; i++) {
                switch (_pattern[i][0]) {
                    case '*': parsed_pattern[i] = "*"; break;
                    case '$':
                        parsed_pattern[i] = "$"; 
                        let varname = _pattern[i].substr(1);
                        let test = tests[varname];
                        vars.push({
                            p: i,
                            n: varname,
                            t: (test instanceof RegExp) ? (str) => test.test(str) : (test || null)
                        });
                    break;
                    case '(':
                        parsed_pattern[i] = _pattern[i].substr(1, _pattern[i].length - 2).split('|');
                    break;
                    default: parsed_pattern[i] = _pattern[i]; break;
                }
            }
            
            return {
                pattern: parsed_pattern,
                vars
            };
        }

        /**
         * verifie qu'un pattern précompilé correspond à une url
         * @param {Array<String>} url 
         * @param {Array<Array|String>} pattern 
         * @param {Array} vars 
         */
        __patternMatch(url, pattern, vars) {
            let nb_parts_url = url.length;
            let nb_parts_pattern = pattern.length;

            let nb_comparaison = (nb_parts_pattern > nb_parts_url) ? nb_parts_pattern : nb_parts_url;
            let nb_vars = vars.length;
            let is_matching = true;

            let j = 0;
            let i = 0;

            let last = (pattern[nb_parts_pattern - 1] == "*") ? "*" : "";

            while (is_matching && i < nb_comparaison) {
                let a = (i < nb_parts_pattern) ? pattern[i] : last;
                let b = (i < nb_parts_url) ? url[i] : "";

                is_matching = (typeof a == 'string') ?
                    ( a == "*" || a == "$" || a == b ) :
                    ( __contient(a, b) )
                ;

                if (is_matching && j < nb_vars && vars[j].p == i) {
                    let t = vars[j++].t;
                    try {
                        is_matching = t ? t(b) : true;
                    } catch (e) {
                        console.error(e);
                        is_matching = false;
                    }
                }

                i++;
            }


            return is_matching;
        }

        /**
         * Extrait les valeurs des variables dans une url
         * @param {Array<String>} url
         * @param {Array} vars 
         */
        __varsValues(url, vars) {
            var values = {};
            let nb_vars = vars.length;

            for (let i = 0; i < nb_vars; i++)
                values[vars[i].n] = url[vars[i].p];

            return values;
        }

        /**
         * Fonction qui ajoute un écouteur pour les changements d'url
         * @param {String} pattern  exemple : "/app/blabla/(choice1|choice2)/$variable/*"
         * @param {Function|null} match fonction appelé lorsque l'url correspond au pattern. Est passé en paramètre de la fonction un objet : varname => value
         * @param {Function|null} unmatch fonction appelé lorsque l'url ne correspond plus au pattern
         * @param {Object} tests Un objet varname => RegEx|Function // test que la variable correspond au données
         */
        addListener(pattern, match, unmatch, tests={}) {
            var listener;

            if (!match && !unmatch) return;// cas où match et unmatch ne sont pas definis

            let ppattern = this.__patternParse(pattern, tests);

            listener = {
                _pattern: pattern,
                pattern: ppattern.pattern,
                vars: ppattern.vars,
                match: match || null,
                unmatch: unmatch || null,
                prevUrlMatch: false
            };

            this.#listeners.push(listener);

            let v = this.parseURL(pattern, tests);
            listener.prevUrlMatch = v !== null;
            try {
                if (v && match) match(v);
                else if (unmatch) unmatch(v);
            } catch (e) { console.error(e); }

        }

        /**
         * Fonction appelé lorsque l'url change
         */
        __URLchange() {
            let url = window.location.pathname.substr(1).split('/');
            let nb_listeners = this.#listeners.length;

            for (let i = 0; i < nb_listeners; i++) {
                let listener = this.#listeners[i];
                let match = this.__patternMatch(url, listener.pattern, listener.vars);

                if (listener.match && match) {
                    let vars = this.__varsValues(url, listener.vars);
                    try { listener.match(vars); } catch (e) { console.error(e); }
                } else if (listener.unmatch && !match && listener.prevUrlMatch) {
                    try { listener.unmatch(); } catch (e) { console.error(e); }
                }

                listener.prevUrlMatch = match;
            }
        }

        /**
         * Parse l'url courrante
         * @param {String} pattern 
         * @param {Object} tests 
         */
        parseURL(pattern, tests = {}) {
            let url = window.location.pathname.substr(1).split('/');
            let ppattern = this.__patternParse(pattern, tests);
            let match = this.__patternMatch(url, ppattern.pattern, ppattern.vars);
            
            return match ? this.__varsValues(url, ppattern.vars) : null; 
        }
    }

    window.setURL = goTo;
    window.URLrooter = new URLrooter();
})(window, document);