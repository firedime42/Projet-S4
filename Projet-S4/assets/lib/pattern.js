<<<<<<< Updated upstream


(function (window) {
    

    /**
     * Assemble des patterns RegExp
     * @param {RegExp} model Le pattern que l'on va etendre /^:sub_pattern_name:* otherthing/g
     * @param {Object<RegExp>} patterns Les patterns sources { "sub_pattern_name": /\w/g }
     * @return {RegExp} /^\w* otherthing/g
     */
    function patternAssemble(model, patterns) {
        let model_source = model.source;
        
        let names = Object.keys(patterns);
        let nb_patterns = names.length;

        for (let i = 0; i < nb_patterns; i++)
            model_source = model_source.replaceAll(':'+names[i]+':', (patterns[names[i]] || {}).source || "");

        return new RegExp(model_source, model.flags);
    }

    window.Pattern = {};
    window.Pattern.assemble = patternAssemble;

    

=======


(function (window) {
    

    /**
     * Assemble des patterns RegExp
     * @param {RegExp} model Le pattern que l'on va etendre /^:sub_pattern_name:* otherthing/g
     * @param {Object<RegExp>} patterns Les patterns sources { "sub_pattern_name": /\w/g }
     * @return {RegExp} /^\w* otherthing/g
     */
    function patternAssemble(model, patterns) {
        let model_source = model.source;
        
        let names = Object.keys(patterns);
        let nb_patterns = names.length;

        for (let i = 0; i < nb_patterns; i++)
            model_source = model_source.replaceAll(':'+names[i]+':', (patterns[names[i]] || {}).source || "");

        return new RegExp(model_source, model.flags);
    }

    window.Pattern = {};
    window.Pattern.assemble = patternAssemble;

    

>>>>>>> Stashed changes
})(window);