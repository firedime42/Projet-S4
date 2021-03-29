/**
 * HTMLList.js version 1.0
 * Ajoute des fonctionnalitées pour creer une liste dynamique d'element html.
 * @author Mattéo Mezzasalma
 * @requires Dom.js
 * @requires uTemplate.js
 * @ Listenable.js
 */

(function (window, Dom) {

    class HTMLList {
        #version;
        #container;
        #parser;
        #updateFunction;
        #elements;

        constructor(container, parser) {
            let _this = this;
            this.#version = 0;
            this.#container = container;
            this.#parser = parser;
            console.log(parser);
            this.#updateFunction = function (element) { _this.update(element); };
            this.#elements = {};
        }

        async add(id, element, data) {
            if (this.#elements[id]) return;

            let dom_element = Dom.create(`<div></div>`).children[0];
            Dom.append(this.#container, dom_element);
            this.#elements[id] = { dom_element, element: null, data };

            let r = this.#version;
            element = await element;

            if (r != this.#version) return;

            this.#elements[id].element = element;
            console.log(this.#parser);
            this.#elements[id].dom_element = this.#parser.parse({ element, ...data }).children[0];
            Dom.replace(dom_element, this.#elements[id].dom_element);

            if (element instanceof Listenable)
                element.addListener('upload', this.#updateFunction);
        }

        update(element) {
            if (!this.#elements[element.id]) return;

            let dom_element = this.#elements[element.id].dom_element;
            let data = this.#elements[element.id].data;
            this.#elements[element.id].dom_element = this.#parser.parse({ element, ...data });
            Dom.replace(dom_element, this.#elements[element.id].dom_element);
        }

        remove(element_id) {
            if (!this.#elements[element_id]) return;

            let element = this.#elements[element_id].element;
            let dom_element = this.#elements[element_id].dom_element;

            if (element instanceof Listenable)
                element.removeListener('upload', this.#updateFunction);

            delete this.#elements[element_id];
            Dom.remove(dom_element);
        }

        clear() {
            let ids = Object.keys(this.#elements);
            let nb_ids = ids.length;

            for (let i = 0; i < nb_ids; i++) this.remove(ids[i]);

            this.#version++; // now the version is updated
        }
    }

    window.HTMLList = HTMLList;

    console.log('oh oh');

})(window, Dom);