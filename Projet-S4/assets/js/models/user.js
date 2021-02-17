
class MainUser extends Listenable {
    constructor() {
        super();


    }

    /**
     * Permet de connecter l'utilisateur
     * @param {String} email Adresse email de l'utilisateur
     * @param {String} password Mot de passe
     * @return {Boolean} vrai si l'utilisateur est connecté faux sinon.
     */
    async login(email, password) {
        let r = await fetch("/core/controller/account.php", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify({
                action: 'login',
                email,
                password
            })
        });
    }


    /**
     * Permet de créer un nouveau compte
     * @param {String} email Adresse email de l'utilisateur
     * @param {String} username Nom de l'utilisateur
     * @param {String} psw1 Mot de passe
     * @param {String} psw2 Mot de passe
     */
    async register(email, username, psw1, psw2) {
        if (psw1 != psw2) {
        } else {
            let r = await fetch("/core/controller/account.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify({
                    action: 'register',
                    email,
                    username,
                    password: psw1
                })
            });
        }
    }

    /**
     * Permet de déconnecter l'utilisateur courant
     */
    async logout() {
        let r = await fetch("/core/controller/account.php", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify({
                action: 'logout'
            })
        })
    }


}

MainUser.EVENT_LOGGED_IN = "loggedIn";