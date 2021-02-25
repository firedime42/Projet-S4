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
            if ( node.parentElement == null ) {
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

            _this.template.ctx.markedElement[nom] = _this.currentNode.parentNode;

            return "";
        },

        /**
         * Compare une url et un pattern
         * @param {String} url l'url de la page
         * @param {String} urlpattern le pattern de l'url
         */
        URLLIKE: function (url, urlpattern = "") {
            if (urlpattern == "") {
                urlpattern = url;
                url = window.location.pathname;
            }

            let len = urlpattern.length;

            let last = urlpattern[len - 1];

            return (last == '*' && url.substr(0, len - 1) == urlpattern.substr(0, len - 1)) || url == urlpattern;
        },

        URLIN(url, ...urlpatterns) {
            let i = 0;
            let nb_patterns = urlpatterns.length;
            while (i < nb_patterns && !uTemplate.BASIC_DATA.URLLIKE(url, urlpatterns[i])) i++;
            return i < nb_patterns;
        }
    });
})(uTemplate);