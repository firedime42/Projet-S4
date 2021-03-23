(function () {

    async function _getUsersById(id, time) {
        let r = await fetch("/core/controller/profil.php", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify({
                action: 'profil',

                id,
                time
            })
        });

        let rdata = await r.json();

        if (!rdata.success)
            return _error(rdata.error);

        return rdata;
    }

    class User {
        #id;
        #username;
        #description;
        #lastUpdate;

        constructor(id) {
            this.#id = id;
            this.#lastUpdate = 0;
        }

        /**
         * Récupère les informations depuis le serveur et les actualise si elles ont changées
         */
        async pull() {
            let d = await _getUsersById(id, this.lastUpdate);
            
            if (d instanceof Error) return d;

            if (d.user == null) return this;
            
            this.#username = d.user.username;
            this.#description = d.user.description;
            this.#lastUpdate = d.user.lastUpdate;

            return this;
        }
    }


    class UserManager {
        #users;
        #waiting;

        constructor() {
            this.#users = {};
            this.#waiting = {};
        }

        getById(id) {
            if (this.#waiting[id]) return this.#waiting[id];

            let _this = this;

            // creer ou récuperer le groupe
            this.#users[id] = this.#users[id] || new User(id);

            this.#waiting[id] = this.#users[id].pull();
            this.#waiting[id].then(function () { _this.#waiting[id] = null; });

            // verifier/recuperer les infos sur le serveur
            return this.#waiting[id];
        }
    }

    window.User = User;
    window.USERS = new UserManager();
})();