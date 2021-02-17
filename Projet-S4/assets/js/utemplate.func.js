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

        /**
         * Inclu un fichier 1 fois : n'est plus inclu par la suite
         * @param {Node} node l'endroit où inserer le code
         * @param {String} url le chemin du fichier à inclure
         */
        INCLUDE: async function (node, url, global) {
            if (node.parentElement == null) return;

            // request content
            let r = await fetch(url, { "method": "GET" });
            let htmlcontent = await r.text();

            // create template
            let template = new uTemplate.template(htmlcontent);

            console.log(url);

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
                let f = AsyncFunction("doc", script[i]);
                f(template);
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
        }
    });
})(uTemplate);