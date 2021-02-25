/**
 * 
 * @author Mattéo Mezzasalma
 * @required Dom.js
 * @required pattern.js
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
     * 
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
     * 
     * @param {String} variableName chemin d'une variable "user.pomme.truc"
     * @param {Object} data 
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
         * @param {String|Node|Array.<Node>} html 
         * @param {Object|undefined} data 
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
            if (typeof data == 'object') this.ctx = Object.assign(this.ctx, data);
            
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

        getElement() {
            return this.#element;
        }

        getElements() {
            return this.#elements;
        }
    }

    class TemplateParser {
        #parsingCodes;

        constructor (html) {
            this.#parsingCodes = _extractParsingCodes(html);
            this.ctx = Object.assign({}, uTemplate.BASIC_DATA);
        }

        parse (data) {
            Object.assign(this.ctx, data);
            let html = _parseCodes(this.#parsingCodes, this.ctx);
            return Dom.create(html);
        }
    }

    var uTemplate = {
        template: UpdTemplate,
        parser: TemplateParser,
        BASIC_DATA: {
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