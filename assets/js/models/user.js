/**
 * Mezzasalma Mattéo
 * 
 */
(function () {

    /**
     * Vérifie si l'objet passé en paramètre est un identifiant valide
     * @param {*} id 
     * @returns 
     */
    function valideID(id) {
        return typeof id == "number" && Number.isInteger(id) && id >= 0;
    }

    /**
     * verifie si l'objet passé en paramètre est un nom valide
     * @param {*} name 
     * @returns 
     */
    function valideName(name) {
        return typeof name == "string" && name.length > 3;
    }

    class User extends Listenable {
        static EVENT_UPDATE = 'update';

        #id;
        #name;

        constructor(id) {
            super();
            
            this.#id = id;
            this.#name = null;
        }

        get id() { return this.#id; }
        get name() { return this.#name; }
        get avatar() { return `/core/controller/avatar.php?user=${this.#id}`; }

        async pull() {
            let r = request("/core/controller/user.php", {
                id: this.#id
            });

            if (r instanceof Error) return r;

            this.__parseData(r);

            return this;
        }

        __parseData(data) {
            if (valideName(data.name)) this.#name = data.name;

            this.emit(User.EVENT_UPDATE);

            return this;
        }
    }

    class UserManager {
        #users;

        constructor() {
            this.#users = {};
        }

        async get(id) {
            if (!valideID(id)) return error(-1);

            if (!this.#users[id]) this.#users[id] = new User(id);
            
            return await this.#users[id].pull();
        }

        getWithoutPull(id) {
            if (!valideID(id)) return error(-1);

            if (!this.#users[id]) this.#users[id] = new User(id);
            
            return this.#users[id];
        }
    }

    window.User = User;
    window.USERS = new UserManager();
})();