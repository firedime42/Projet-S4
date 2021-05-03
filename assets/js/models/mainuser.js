(function (window) {

    const EMAIL_FORMAT_CHECK = /^[\w\.]+\@[\w\.]+\.[\w\.]{1,4}$/;
    const UNAME_FORMAT_CHECK = /^[\w\.]{3,25}$/;

    const PSW_FORMAT_CHECK   = [
        /^(?:.*[A-Z]){1,}.*$/, // 1+ maj
        /^(?:.*[a-z]){1,}.*$/, // 1+ min
        /^(?:.*[0-9]){1,}.*$/, // 1+ dig
        /^(?:.*[^0-9a-zA-Z]){1,}.*$/, // 1+ spec
        /^.{8,}$/            // 8+ total caractère
    ];


    class MainUser extends Listenable {
        
        static EVENT_LOGGED_IN = "loggedIn";       // utilisateur connecté
        static EVENT_LOGGED_OUT = "loggedOut";     // utilisateur déconnecté
        static EVENT_STATE_CHANGE = "statechange"; // utilisateur l'état de l'utilisateur change : loggedIn true/false
        
        static STATE_LOGGED_OUT = 0;
        static STATE_LOGGED_IN  = 1;
        static STATE_PROCESSING = 2;

        #id;
        #username;
        #email;

        #state;

        constructor() {
            super();

            this.#state = MainUser.STATE_LOGGED_OUT;
        }

        /**
         * Tente de récupérer l'utilisateur
         */
        async retrieveSession() {
            if (this.#state == MainUser.STATE_PROCESSING) return false;

            this.__setState(MainUser.STATE_PROCESSING);

            let r = await fetch("/core/controller/account.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify({
                    action: 'retrieve'
                })
            });

            let rdata = await r.json();

            if (!rdata.success) {
                this.__setState(MainUser.STATE_LOGGED_OUT);
                return _error(rdata.error);
            }

            this.__parseUser(rdata.user);

            this.__setState(MainUser.STATE_LOGGED_IN);

            return this;
        }

        /**
         * Permet de connecter l'utilisateur
         * @param {String|null} email Adresse email de l'utilisateur
         * @param {null|String} username Nom de l'utilistateur
         * @param {String} password Mot de passe
         * @return {Boolean} vrai si l'utilisateur est connecté faux sinon.
         */
        async login(emailOrUname, password) {

            if (this.#state != MainUser.STATE_LOGGED_OUT) return false;

            let email = EMAIL_FORMAT_CHECK.test(emailOrUname) ? emailOrUname : null;
            let username = UNAME_FORMAT_CHECK.test(emailOrUname) ? emailOrUname : null;


            // check if it isn't username and email
            if (email == null && username == null) return _error(ERRCODES.LOGIN_INVALID_USERNAME);

            let time = Math.round(new Date().getTime() / 1000);

            this.__setState(MainUser.STATE_PROCESSING);

            let r = await fetch("/core/controller/account.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify({
                    action: 'login',
                    email,
                    username,
                    password: sha256(time + "" + sha256(password)),
                    time
                })
            });

            let rdata = await r.json();

            if (!rdata.success) {
                this.__setState(MainUser.STATE_LOGGED_OUT);
                return _error(rdata.error);
            }

            this.__parseUser(rdata.user);

            this.__setState(MainUser.STATE_LOGGED_IN);

            return this;
        }

        /**
         * Permet de créer un nouveau compte
         * @param {String} email Adresse email de l'utilisateur
         * @param {String} username Nom de l'utilisateur
         * @param {String} psw1 Mot de passe
         * @param {String} psw2 Mot de passe
         */
        async register(email, username, psw) {

            if (this.#state != MainUser.STATE_LOGGED_OUT) return false;

            let error = null;

            if (!this.checkEmailFormat(email)) error = _error(ERRCODES.REGISTER_FORMAT_EMAIL);
            else if (!this.checkUsernameFormat(username)) error = _error(ERRCODES.REGISTER_FORMAT_USERNAME);
            else if (!this.checkPasswordStrength(psw)) error = _error(ERRCODES.REGISTER_FORMAT_PASSWORD);

            if (error != null) return error;

            this.__setState(MainUser.STATE_PROCESSING);

            let r = await fetch("/core/controller/account.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify({
                    action: 'register',
                    email,
                    username,
                    password: sha256(psw)
                })
            });

            let rdata = await r.json();

            if (!rdata.success) {
                this.__setState(MainUser.STATE_LOGGED_OUT);
                
                return _error(rdata.error);
            }

            this.__parseUser(rdata.user);

            this.__setState(MainUser.STATE_LOGGED_IN);
        }

        /**
         * Permet de déconnecter l'utilisateur courant
         */
        async logout() {
            if (this.#state != MainUser.STATE_LOGGED_IN) return false;

            this.__setState(MainUser.STATE_PROCESSING);

            let r = await fetch("/core/controller/account.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify({
                    action: 'logout'
                })
            });

            let rdata = await r.json();

            if (!rdata.success) {
                console.log("Erreur Logout : " + rdata.error);
                this.__setState(MainUser.STATE_LOGGED_IN);
                return _error(rdata.error);
            }

            this.__setState(MainUser.STATE_LOGGED_OUT);
            return true;
        }

        /**
         * Renvoi un booléen indiquant si l'utilisateur est connecté
         */
        get isLoggedIn() {
            return this.#state == MainUser.STATE_LOGGED_IN;
        }

        get id() { return this.#id; }
        get username() { return this.#username; }
        get email() { return this.#email; }

        checkEmailFormat(email) {
            return EMAIL_FORMAT_CHECK.test(email);
        }

        checkUsernameFormat(username) {
            return UNAME_FORMAT_CHECK.test(username);
        }

        checkPasswordStrength(password) {
            return PSW_FORMAT_CHECK[0].test(password)
            &&     PSW_FORMAT_CHECK[1].test(password)
            &&     PSW_FORMAT_CHECK[2].test(password)
            &&     PSW_FORMAT_CHECK[3].test(password)
            &&     PSW_FORMAT_CHECK[4].test(password);
        }

        __setState(state) {
            switch (state) {
                case this.#state: break; // ne change rien
                case MainUser.STATE_LOGGED_IN:
                    this.#state = state;
                    this.emit(MainUser.EVENT_STATE_CHANGE);
                    this.emit(MainUser.EVENT_LOGGED_IN);
                break;
                case MainUser.STATE_LOGGED_OUT:
                    this.#state = state;
                    this.emit(MainUser.EVENT_STATE_CHANGE);
                    this.emit(MainUser.EVENT_LOGGED_OUT);
                break;
                case MainUser.STATE_PROCESSING:
                    this.#state = state;
                    this.emit(MainUser.EVENT_STATE_CHANGE);
                break;
                default: break; // n'est pas un état valide
            }
        }

        __parseUser(user) {
            this.#id = user.id;
            this.#username = user.username;
            this.#email = user.email;
        }
    }

    window.MainUser = MainUser;
})(window);
