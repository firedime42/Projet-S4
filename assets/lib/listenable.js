!(function (window) {

    function _ExternPrivatePromise() {
        var rs, rj;
        var p = new Promise(async function (resolve, reject) { rs = resolve; rj = reject; });
        return { promise: p, resolve: rs, reject: rj };
    }
    
    function _arrayIndexOf(array, element) {
        let nb_elements = array.length;
        let i = 0;
    
        while (i < nb_elements && element != array[i]) i++;
    
        return (i < nb_elements) ? i : -1;
    }
    
    function _appendElementOnce(array, element) {
        let index = _arrayIndexOf(array, element);
    
        if (index == -1)
            array.push(element);
    }
    
    function _removeElement(array, element) {
        let index = _arrayIndexOf(array, element);
    
        if (index > -1)
            array.splice(index, 1);
    }
    
    class Listenable {
        #listeners;
        #promises;
    
        constructor () {
            this.#listeners = {};
            this.#promises = {};
        }
    
        addListener(eventname, listener) {
            if (typeof this.#listeners[eventname] == "undefined")
                this.#listeners[eventname] = [];
            
            _appendElementOnce(this.#listeners[eventname], listener);
    
            return this;
        }
    
        removeListener(eventname, listener) {
            if (typeof this.#listeners[eventname] != "undefined")
                _removeElement(this.#listeners[eventname], listener);
    
            return this;
        }
    
        async emit(eventname, ...data) {
            await null;
    
            if (this.#listeners[eventname]) {
             
                let nb_listeners = this.#listeners[eventname].length;
                for (let i = 0; i < nb_listeners; i++)
                    try {
                        this.#listeners[eventname][i](this, ...data);
                    } catch (e) {
                        console.error(e, this.#listeners[eventname][i]);
                    }
            }
    
            if (this.#promises[eventname]) {
                this.#promises[eventname].resolve(this, ...data);
                this.#promises[eventname] = null;
            }
        }
    
        /**
         * renvoi une promesse resolu lors que l'evenement est emit
         */
        until(eventname) {
            if (!this.#promises[eventname])
                this.#promises[eventname] = _ExternPrivatePromise();
    
            return this.#promises[eventname].promise;
        }
    }
    
    window.Listenable = Listenable;
    
})(window);