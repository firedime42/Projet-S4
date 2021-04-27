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
            this.#updateFunction = function (element) { _this.update(element); };
            this.#elements = {};
        }

        /**
         * Ajoute un element à la liste
         * @param {Number} id 
         * @param {Object} element element à ajouter
         * @param {Object} data données additionnels
         * @param {Boolean} prepend boolean indiquant si l'element doit être ajouté à la fin
         * @returns 
         */
        async add(id, element, data, prepend = false) {
            if (this.#elements[id]) return;

            let dom_element = Dom.create(`<div></div>`).children[0];
            
            if (prepend) Dom.prepend(this.#container, dom_element);
            else Dom.append(this.#container, dom_element);
            
            this.#elements[id] = { dom_element, element: null, data };

            let r = this.#version;
            element = await element;

            if (r != this.#version) return;

            this.#elements[id].element = element;

            dom_element = this.#parser.parse({ element, ...data }).children[0];
            
            Dom.replace(this.#elements[id].dom_element, dom_element);
            
            this.#elements[id].dom_element = dom_element;

            if (element instanceof Listenable)
                element.addListener('update', this.#updateFunction);
        }


        /**
         * 
         * @param {Number} id 
         * @returns {HTMLElement} le noeud associé à l'element
         */
        getElement(id) {
            return (this.#elements[id]) ? this.#elements[id].dom_element : null;
        }

        /**
         * 
         * @returns {Array<HTMLElement>} listes des elements
         */
        getElements() {
            let ids = Object.keys(this.#elements);
            let nb_ids = ids.length;
            var dom_elements = new Array(nb_ids);

            for (let i = 0; i < nb_ids; i++)
                dom_elements[i] = this.#elements[ids[i]].dom_element;
            
            return dom_elements;
        }

        /**
         * Met à jour un element
         * @param {Object} element 
         * @returns 
         */
        update(element) {
            if (!this.#elements[element.id]) return;

            let dom_element = this.#elements[element.id].dom_element;
            let data = this.#elements[element.id].data;
            this.#elements[element.id].dom_element = this.#parser.parse({ element, ...data }).children[0];
            Dom.replace(dom_element, this.#elements[element.id].dom_element);
        }

        /**
         * retire un element de la liste
         * @param {*} element_id 
         * @returns 
         */
        remove(element_id) {
            if (!this.#elements[element_id]) return;

            let element = this.#elements[element_id].element;
            let dom_element = this.#elements[element_id].dom_element;

            if (element instanceof Listenable)
                element.removeListener('update', this.#updateFunction);

            delete this.#elements[element_id];
            Dom.remove(dom_element);
        }

        /**
         * Enleve tous les elements de la liste
         */
        clear() {
            let ids = Object.keys(this.#elements);
            let nb_ids = ids.length;

            for (let i = 0; i < nb_ids; i++) this.remove(ids[i]);

            this.#version++; // now the version is updated
        }
    }

    window.HTMLList = HTMLList;

})(window, Dom);    