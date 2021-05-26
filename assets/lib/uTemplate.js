/**
 * 
 * @author Mattéo Mezzasalma
 * @requires Dom.js
 * @requires pattern.js
 */

(function (window, Dom) {


    var _numberPattern = /[-\+]?\d*\.?\d+/;
    var _stringPatternS = /"(?:\\"|[^"])*"/;
    var _stringPatternD = /'(?:\\'|[^'])*'/;
    var _variableNamePattern = /[\w\.]+/;

    var _stringPattern = Pattern.assemble(/(?::stringS:|:stringD:)/, {stringS: _stringPatternS, stringD: _stringPatternD});

    var _commandPattern = Pattern.assemble(/:number:|:string:|:variable:/g, { number: _numberPattern, string: _stringPattern, variable: _variableNamePattern });
    var _commandPatternExtract = Pattern.assemble(/\s+(:command:)/g, { command: _commandPattern });
    var _templateMarker = Pattern.assemble(/\{\{\s+(?::command:)(?:\s+(?::command:))*\s+\}\}/g, { command: _commandPattern });

    const CMD_TYPE = {
        VAR: 0,
        CONST: 1
    };

    const VAR_CONST = {
        "null": null,
        "false": false,
        "true": true
    };


    /**
     * Fonction de parcours d'une suite de fichier
     * @param {Node} element l'element parent
     * @return {Array.<Node>} liste des noeuds
     */
    function _getAllNodes(element) {
        let allelements = [element];

        let i = 0;
        let nb_elements = 1;

        while (i < nb_elements) {
            let e = allelements[i++];

            let childs = e.childNodes;
            let nb_childs = childs.length;

            for (let j = 0; j < nb_childs; j++)
                allelements.push(childs[j]);

            nb_elements += nb_childs;
        }
        
        return allelements;
    }

    /**
     * renvoie la liste de tous les noeuds parsable.
     * @param {Node} element l'element parent
     * @return {Array.<Node>} liste des noeuds qui contiennent du texte à parser
     */
    function _getParsableNode(element) {
        let nodes = _getAllNodes(element);
        let impNodes = [];

        let nb_nodes = nodes.length;

        for (let i = 0; i < nb_nodes; i++) {
            let node = nodes[i];

            _templateMarker.lastIndex = -1;
            if (node.nodeType == Node.TEXT_NODE && _templateMarker.test(node.nodeValue)) impNodes.push(node);
            else if (node.nodeType == Node.ELEMENT_NODE) {
                // contenu des attributs
                let nb_attributes = node.attributes.length;
                for (let j = 0; j < nb_attributes; j++) {
                    let attr = node.attributes[j];
                    _templateMarker.lastIndex = -1;
                    if (_templateMarker.test(attr.nodeValue)) impNodes.push(attr);
                }
            }
        }

        return impNodes;
    }


    /**
     * Fonction pour préparer et accéleré le parsage de valeur
     * @param {String} cmd la commande à parser
     */
    function _preParse(cmd) {
        _commandPatternExtract.lastIndex = 0;
        let matcher = cmd.matchAll(_commandPatternExtract);
        let parsedcmd = [];

        let element = matcher.next();

        while (!element.done) {
            let type;
            let value = element.value[1];

            if (_stringPattern.test(value)) {
                type = CMD_TYPE.CONST;
                value = value.substr(1, value.length - 2).replace(/\\(.)/g, "$1");
            } else if (_numberPattern.test(value)) {
                type = CMD_TYPE.CONST;
                value = Number.parseFloat(value);
            } else if (_variableNamePattern.test(value)) {
                if (VAR_CONST[value] !== undefined) {
                    type = CMD_TYPE.CONST;
                    value = VAR_CONST[value];
                } else {
                    type = CMD_TYPE.VAR;
                    value = value.split('.');
                }
            }
            
            parsedcmd.push({ type: type, value: value });
            element = matcher.next();
        }

        return parsedcmd;
    }

    /**
     * Extrait les codes précompilés d'un text
     * @param {String} text 
     * @param {Object}
     */
    function _extractParsingCodes(text) {

        let joins = [];
        let codes = [];

        _templateMarker.lastIndex = 0;

        let matcher = text.matchAll(_templateMarker);
        let lastpos = 0;
        let e = matcher.next();

        while (!e.done) {
            let value = e.value[0];

            joins.push(text.substr(lastpos, e.value.index - lastpos));
            codes.push(_preParse(value));
            
            lastpos = e.value.index + value.length;

            e = matcher.next();
        }

        joins.push(text.substr(lastpos, text.length - lastpos));

        return { joins, codes, nb_codes: codes.length };
    }




    /**
     * récupere la valeur d'une variable
     * @param {String} variable chemin d'une variable "user.pomme.truc"
     * @param {Object} data 
     * @param {UpdTemplate} _this le template qui a appelé la fonciton
     */
    function _getValue(variable, data, _this) {
        let value;

        if (variable.type == CMD_TYPE.CONST)
            value = variable.value;
        else if (variable.type == CMD_TYPE.VAR) {
            value = (variable.value[0] == 'this') ? { "this" : _this } : data;

            let i = 0, deep = variable.value.length;

            for (; i < deep && typeof value == 'object'; i++)
                value = value[variable.value[i]];
        }

        return value;
    }

    /**
     * Parse un code avec des données
     */
    function _parseCode(code, data, _this) {

        let first_var = _getValue(code[0], data, _this);
        let value;

        if (typeof first_var == 'function') {
            let nb_params = code.length - 1;
            let params = new Array(nb_params);

            for (let i = 0; i < nb_params; i++)
                params[i] = _getValue(code[i + 1], data, _this);
            
            value = first_var(...params);
        } else value = first_var;

        return value;
    }

    /**
     * Parse un enseble de codes
     */
    function _parseCodes(codes, data, _this) {
        let res = codes.joins[0];
        let values = new Array(codes.nb_codes);

        // launch functions
        for (let i = 0; i < codes.nb_codes; i++)
            values[i] = _parseCode(codes.codes[i], data, _this);

        // set results
        for (let i = 0; i < codes.nb_codes; i++) {
            res += values[i];
            res += codes.joins[i + 1];
        }

        return res;
    }

    /**
     * Renvoi l'ensemble des noeuds enfant du noeud passé en paramètre
     * @param {Node} e le noeud parent
     */
    function _childNodes(e) {
        let nb_nodes = e.childNodes.length;
        let nodes = new Array(nb_nodes);

        for (let i = 0; i < nb_nodes; i++)
            nodes[i] = e.childNodes[i];

        return nodes;
    }

    /**
     * Template Updatable
     */
    class UpdTemplate {
        #element;
        #elements;
        #impNodes;
        #parsingCodes;
        #prevValue;

        /**
         * 
         * @param {String|Node|Array.<Node>} html la source du code du template
         * @param {Object|undefined} data les données passé pour un premiere update
         */
        constructor(html, data) {
            // construction du context commun
            this.ctx = Object.assign({}, uTemplate.BASIC_DATA);

            // construct element
            this.#element = (typeof html == 'string') ? Dom.create(html) : html;
            this.#elements = _childNodes(this.#element);

            // extract nodes
            this.#impNodes = _getParsableNode(this.#element);
            let nb_nodes = this.#impNodes.length;
            this.#parsingCodes = new Array(nb_nodes);
            this.#prevValue = new Array(nb_nodes).fill(null);

            for (let i = 0; i < nb_nodes; i++)
                this.#parsingCodes[i] = _extractParsingCodes(this.#impNodes[i].nodeValue);

            if (typeof data == 'object')
                this.update(data);
        }

        /**
         * Met à jour le template avec les nouvelles données
         * @param {Object} data les données
         */
        update(data) {
            if (typeof this.ctx != 'object') console.log('debug : ', this, this.ctx);
            if (data && typeof data == 'object') this.ctx = Object.assign(this.ctx, data);
            
            let nb_nodes = this.#impNodes.length;

            for (let i = 0; i < nb_nodes; i++) {

                let res = _parseCodes(this.#parsingCodes[i], this.ctx, {
                    template: this,
                    currentNode: this.#impNodes[i]
                });

                if (res != this.#prevValue[i]) {
                    this.#prevValue[i] = res;
                    this.#impNodes[i].nodeValue = res;
                }
            }
        }

        /**
         * Renvoi le noeud qui a contenu / contient les elements du template lors de la création de ce dernier.
         */
        getElement() {
            return this.#element;
        }

        /**
         * Renvoi la liste des elements du template
         */
        getElements() {
            return this.#elements;
        }
    }

    /*
    class TemplateParser {
        #parsingCodes;

        /**
         * Permet de creer un parseur specifique au code html passé en paramètre
         * @param {String} html 
         *_/
        constructor (html) {
            this.#parsingCodes = _extractParsingCodes(html);
            this.ctx = Object.assign({}, uTemplate.BASIC_DATA);
        }

        /**
         * génère un nouvelle element contenant à partir du template et des données passés en paramètres
         * @param {Object} data
         *_/
        parse (data) {
            Object.assign(this.ctx, data);
            let html = _parseCodes(this.#parsingCodes, this.ctx);
            return Dom.create(html);
        }
    }*/
    class TemplateParser {
        #html;
        #parsingCodes;

        /**
         * Permet de creer un parseur specifique au code html passé en paramètres
         * @param {String} html 
         */
        constructor (html) {
            this.#html = (typeof html == 'string') ? html : Dom.html();
            this.#parsingCodes = null;
            this.ctx = Object.assign({}, uTemplate.BASIC_DATA);
        }

        /**
         * génère un nouvelle element contenant à partir du template et des données passés en paramètres
         * @param {Object} data
         */
        parse (data) {
            // assign data to ctx

            if (data) Object.assign(this.ctx, data);

            // create element
            let elements = Dom.create(this.#html);
            
            // on recupère les noeuds importants (qui contiennent du code)
            let impNodes = _getParsableNode(elements);
            let nb_nodes = impNodes.length;

            // on extraits et precompiles les codes
            if (this.#parsingCodes == null) {
                this.#parsingCodes = new Array(nb_nodes);
                for (let i = 0; i < nb_nodes; i++)
                    this.#parsingCodes[i] = _extractParsingCodes(impNodes[i].nodeValue);
            }

            // on parse les codes
            for (let i = 0; i < nb_nodes; i++) {
                impNodes[i].nodeValue = _parseCodes(this.#parsingCodes[i], this.ctx, {
                    template: this,
                    currentNode: impNodes[i]
                });
            }

            return elements;
        }
    }

    var uTemplate = {
        template: UpdTemplate,
        parser: TemplateParser,
        BASIC_DATA: { // fonctions disponibles par défaut.
            /**
             * Vérifie a == b
             */
            EQUALS: (a, b) => a == b,

            /**
             * Vérifie que a est entre b et c
             */
            BETWEEN: (a, b, c) => b <= a && a <= c,

            /**
             * Verifie la correspondance d'une chaine et d'un pattern
             * @param {String} string la chaine de caractère
             * @param {RegExp} pattern le pattern
             */
            REGMATCH: (string, pattern) => {
                pattern.lastIndex = 0;
                return pattern.test(string);
            }
        }
    };

    window.uTemplate = uTemplate;

})(window, Dom);