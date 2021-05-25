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
        #biography;
        #nb_messages;
        #nb_files;
        #nb_groups;
        #creation_date;

        constructor(id) {
            super();
            
            this.#id = id;
            this.#name = null;
        }

        get id() { return this.#id; }
        get name() { return this.#name; }
        get avatar() { return `/core/controller/avatar.php?user=${this.#id}`; }
        get biography() { return this.#biography; }
        get nb_messages() { return this.#nb_messages; }
        get nb_files() { return this.#nb_files; }
        get nb_groups() { return this.#nb_groups; }
        get creation_date() { return this.#creation_date; }

        async pull() {
            let r = await request("/core/controller/user.php", {
                action: 'pull',
                id: this.#id
            });

            if (r instanceof Error) return r;

            this.__parseData(r.user);

            return this;
        }

        __parseData(data) {
            if (valideName(data.name)) this.#name = data.name;
            if (typeof data.biography == 'string') this.#biography = data.biography;
            if (Number.isInteger(data.nb_messages)) this.#nb_messages = data.nb_messages;
            if (Number.isInteger(data.nb_files)) this.#nb_files = data.nb_files;
            if (Number.isInteger(data.nb_groups)) this.#nb_groups = data.nb_groups;
            if (Number.isInteger(data.creation_date)) this.#creation_date = data.creation_date;

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
            if (!valideID(id)) return _error(-1);

            if (!this.#users[id]) this.#users[id] = new User(id);
            
            return await this.#users[id].pull();
        }

        getWithoutPull(id) {
            if (!valideID(id)) return _error(-1);

            if (!this.#users[id]) this.#users[id] = new User(id);
            
            return this.#users[id];
        }
    }

    window.User = User;
    window.USERS = new UserManager();
})();