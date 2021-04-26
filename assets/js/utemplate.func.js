/**
 * Ajout de fonctionnalitées pour uTemplate.
 * - REGMATCH string pattern
 * - INCLUDE node url
 * 
 * @author Mattéo Mezzasalma
 * @required uTemplate.js
 * @required Dom.js
 */


(function (uTemplate) {

    function _scripts(nodes) {
        let scripts = [];
        let allelements = [{ tagName: "LIST", children : nodes }];

        let i = 0;
        let nb_elements = 1;

        while (i < nb_elements) {
            let e = allelements[i++];

            let childs = e.children;
            let nb_childs = childs.length;

            if (e.tagName == "SCRIPT")
                scripts.push(e);

            for (let j = 0; j < nb_childs; j++)
                allelements.push(childs[j]);

            nb_elements += nb_childs;
        }
        
        return scripts;
    }
    
    Object.assign(uTemplate.BASIC_DATA, {

        /**
         * Function d'affichage
         */
        LOG: console.log,

        IN: function (value, ...list) {
            return quickIndexOf(list, value) >= 0;
        },

        NOT: function (f, ...args) {
            return !((typeof f == 'function') ? f(...args) : f);
        },

        /**
         * Inclu un fichier 1 fois : n'est plus inclu par la suite
         * @param {Node} node l'endroit où inserer le code
         * @param {String} url le chemin du fichier à inclure
         */
        INCLUDE: async function (node, url, ctx={}) {
            await null;
            if (node == null ||  node.parentElement == null ) {
                //console.log("error parrentNull", node, url);
                return;
            }

            if ( !Dom.isReady() ) {
                //console.log("error isnt ready", node, url);
                Dom.ready(function () {
                    uTemplate.BASIC_DATA.INCLUDE(node, url);
                });
                return;
            }

            if ( !Dom.isVisible(node) ) {
                //console.log("error isnt visible", node.parentElement, url);
                return;
            }


            // request content
            let r = await fetch(url, { "method": "GET" });
            let htmlcontent = await r.text();

            // create template
            let template = new uTemplate.template(htmlcontent);

            console.log(url);

            if (node == null || node.parentElement == null) return;

            // replace node
            Dom.replace(node, template.getElement());

            // parse js
            let scripts = _scripts(template.getElements());
            let nb_scripts = scripts.length;

            let script = new Array(nb_scripts);

            window.scripts = scripts;

            // start requesting
            for (let i = 0; i < nb_scripts; i++)
                if (scripts[i].src != "")
                    script[i] = fetch(scripts[i].src);
                else
                    script[i] = scripts[i].text;

            for (let i = 0; i < nb_scripts; i++)
                if (scripts[i].src != "")
                    script[i] = (await script[i]).text();
                        
            for (let i = 0; i < nb_scripts; i++)
                if (scripts[i].src != "")
                    script[i] = await script[i];

            // execute the scripts
            for (let i = 0; i < nb_scripts; i++) {
                let ctx_var_names = Object.keys(ctx);
                let ctx_var_values = Object.values(ctx);
                let f = AsyncFunction("doc", ...ctx_var_names, script[i]);
                f(template, ...ctx_var_values);
            }
        },

        /**
         * Marque un noeud avec
         * @param {Object} _this 
         * @param {String} nom un nom à donner au noeud
         */
        MARK: function (_this, nom) {
            if (typeof _this.template.ctx.markedElement != "object")
                _this.template.ctx.markedElement = {};

            let path = nom.split('.');

            let node = _this.template.ctx.markedElement;
            let deep = path.length
            for (let i = 0; i < deep - 1; i++) {
                let node_name = path[i];

                if (!node[node_name])
                    node[node_name] = {};

                node = node[node_name];
            }

            node[path[deep - 1]] = _this.currentNode.parentNode;

            return "";
        },

        /**
         * (préférez CURRENT_URL_MATCH)
         * Compare une url et un pattern
         * @param {String} url l'url de la page
         * @param {String} urlpattern le pattern de l'url
         */
        URLLIKE: function (url, urlpattern = "", tests= {}) {
            if (urlpattern == "") {
                urlpattern = url;
                url = window.location.pathname;
            }

            let len = urlpattern.length;

            let last = urlpattern[len - 1];

            return (last == '*' && url.substr(0, len - 1) == urlpattern.substr(0, len - 1)) || url == urlpattern;
        },

        /**
         * (préférez CURRENT_URL_MATCH)
         * test si l'url respect l'un des patterns propose
         * @param {String} url 
         * @param  {...String} urlpatterns 
         * @return {Boolean} 
         */
        URLIN(url, ...urlpatterns) {
            let i = 0;
            let nb_patterns = urlpatterns.length;
            while (i < nb_patterns && !uTemplate.BASIC_DATA.URLLIKE(url, urlpatterns[i])) i++;
            return i < nb_patterns;
        },

        /**
         * l'url courrante verifie un certain pattern
         * @param {String} urlpattern Le pattern
         * @param {Object|undefined} tests Les tests à effectuer
         */
        CURRENT_URL_MATCH: function (urlpattern, tests = {}) {
            return !!URLrooter.parseURL(urlpattern, tests);
        },

        /**
         * Ajoute un listener à un element html
         * @param {Node} node 
         * @param {String} eventname 
         * @param {Function} listener 
         */
        ADDLISTENER: function (node, eventname, listener) {
            if (!(node instanceof HTMLElement)) node = node.parentElement;
            Dom.addListener(node, eventname, listener);
            return "";
        },

        /**
         * Selection un champs de la balise <selection>
         * @param {*} node 
         * @param {*} value 
         */
        INPUT_SELECT: function(node, value) {
            let selector = Dom.parent(node);
            selector.value = value;
            return '';
        },

        // todo : faire un truc pour le menu "select"
        SUPER_INPUT_SELECT: function (node, values, value) {
            let selector = Dom.parent(node);

            // on retire tous les anciens noeuds
            Dom.html(selector, '');

            console.log(node, values, value);

            // on rajoutes les nouveau
            let nb_values = values.length;
            for (let i = 0; i < nb_values; i++) {
                let e = document.createElement('option');
                Dom.attribute(e, { value: values[i].value });
                Dom.html(e, values[i].html);
                Dom.append(selector, e);
            }

            selector.value = value;
        },

        /**
         * Insert un element checkbox
         * @param {*} _this 
         * @param {String} name le nom de la checkbox
         * @param {Boolean} checked indique si la case est cochée
         */
        CHECKBOX: function (_this, name, checked, readonly=false) {
            let element = `<input type="checkbox" name="${name}" ${checked ? 'checked' : ''} ${readonly ? 'disabled' : ''}/>`;
            Dom.before(_this.currentNode, element);
            return '';
        },

        UNITS: Object.freeze({
            SECONDE: 1000,
            MINUTE: 60000,
            HOUR: 3600000,
            DAY: 86400000
        }),

        /**
         * Creer un element fichier
         * @param {*} _this 
         * @param {Number} time 
         * @param {String} mode
         * @param {Number} unit
         */
        AUTOTIME: function (_this, time, mode="default", unit=1) {
            let element = AutoTime.createHTML(mode, time * unit);
            Dom.before(_this.currentNode, element);
            return '';
        }
    });
})(uTemplate);