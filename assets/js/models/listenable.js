!(function (window) {

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

    if (index == -1)
        array.splice(index, 1);
}

class Listenable {
    #listeners;

    constructor () {
        this.#listeners = {};
    }

    addListener(eventname, listener) {
        if (typeof this.#listeners[eventname] == "undefined")
            this.#listeners[eventname] = [];
        
        _appendElementOnce(this.#listeners[eventname], listener);
    }

    removeListener(eventname, listener) {
        if (typeof this.#listeners[eventname] != "undefined")
            _removeElement(this.#listeners[eventname], listener);
    }

    emit(eventname, ...data) {
        if (typeof this.#listeners[eventname] == 'undefined') return;
         
        let nb_listeners = this.#listeners[eventname].length;
        for (let i = 0; i < nb_listeners; i++)
            this.#listeners[eventname][i](this, ...data);
    }

}

window.Listenable = Listenable;

})(window);