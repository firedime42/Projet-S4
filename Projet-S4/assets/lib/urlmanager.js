/**
 * changer l'url sans recharger la page , interception des clicks sur les liens
 * 
 * Supports
 * @author Mattéo M. (MattJP)
 * @required Dom.js
 */

(function (window, document) {

    var History = window.history;

    /**
     * Fonction pour creer une fonction qui emet des evenements
     * @param {Function} f la fonction qui doit emmetre des evenements sur le
     * @param {String} eventname le nom de l'evenement
     */
    function _createEventTrigger(f, eventname) {
        return function () {
            let result = f.apply(this, arguments);
            let e = new Event(eventname);
            e.arguments = arguments;
            window.dispatchEvent(e);
            return result;
        }
    }

    History.pushState = _createEventTrigger(History.pushState, "pushstate");// when history is changed by scripts
    window.addEventListener('popstate', () => window.dispatchEvent(new Event("pushstate")));// when history is changed by user


    /**
     * Fonction qui permet de se rendre sur une page
     * @param {String} url 
     */
    function goTo (url) {
        // ajout dans l'historique du navigateur
        History.pushState({ title: document.title }, document.title, url);
    };

    // chargement de la page local
    Dom.onLoad(window, () => {
        // interception des clics sur les liens
        Dom.onClick(Dom.body, (event) => {
            // on empêche la suivi du lien
            event.preventDefault();
            event.returnValue = false;

            // on cherche un lien activé
            var activedlink = false;
            for (let i = 0, l = event.path.length - 1; i <= l && !activedlink; i++) {
                let element = event.path[i];
                if (element.attributes && element.attributes['href']) {
                    activedlink = true;
                    goTo(element.attributes['href'].value);
                }
            }
            
            // on bloque éventuellement les prochains eventlisteners
            return !activedlink;
        });
    });

    class URLParser {
        constructor (urlpattern) {

        }

        match(url, f) {
            
        }

    }


    window.setURL = goTo;
})(window, document);