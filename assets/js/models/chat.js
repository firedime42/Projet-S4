(function () {

    class Message extends Listenable {
        static EVENT_UPDATE = 'update';

        constructor (id, author, msg) {
            this.id = id;
            this.author = author;
            this.message = msg;
        }

        update () {
            this.emit('update');
        }
    }

    class Chat extends Listenable {
        static EVENT_NEW_MESSAGES = "new_messages";
        static EVENT_EDIT_MESSAGES = "edit_messages";
        static EVENT_RM_MESSAGES = "rm_messages";
        static EVENT_UPDATE = "update";

        static MAX_MESSAGES_PER_PAGE = 10;

        #id;

        #groupe_id;
        #user_perms;
        
        #messages;
        #part;// partie actuellement affichée
        #head;// list des dix derniers messages
        #tail;// id du premier message

        #lastUpdate;

        constructor(id) {
            this.#id = id;
            this.#groupe_id = null;
            this.#user_perms = {
                send: false,
                edit: false,
                remove: false,
                manage: false
            };

            this.#messages = {};
            this.#head = [];
            this.#part = [];
            this.#tail = null;
        }
        
        async isHead() {
            return this.#part[this.#part.length - 1] == this.#head[this.#head.length - 1];
        }

        async isTail() {
            return this.#part[0] == this.#tail;
        }

        async getMessages() {
            let nb_messages = this.#part.length;
            var messages = new Array(nb_messages);
            for (let i = 0; i < nb_messages; i++)
                messages[i] = this.#messages[this.#part[i]];
            return messages;
        }

        async loadMore(direction=-1) {
            /**
             * La direction indique dans quel direction doit être étendu la partie de chat
             * On doit donc envoyer au serveur :
             *  - le plus vieux message en notre possession (this.#parts[0])
             *  - le plus recent (this.#parts[last])
             *  - la direction d'extension -1 = plus vieux, 1 plus recent
             *  - le lastUpdate
             * 
             * Le serveur nous renvoi :
             *  - les messages pour etendre.
             *  - les nouveaux messages sur la tête de discussion (depuis le dernier lastUpdate)
             *  - les messages retirer depuis le lastUpdate
             */

            // etendre les messages

            /**
             * Si le dernier message est la tête de discussion faut-il emettre un evenement update ?
             */

            // ajouter les eventuels messages à la liste de tête de discussiob

            // retirer les eventuels messages suprimés

            // mettre à jours lastUpdate
        }

        async forceUpdate () {
            /**
             * On envoi :
             *  - le lastUpdate
             * Il nous renvoi :
             *  - les nouvelles permissions de l'utilisateur si elles ont changées
             *  - les nouveaux nouveaux messages
             *  - (les messages modifies)
             *  - les messages retirés
             */

            // ajouter les messages

            // modifier les messages

            // retirer les messages 

            // mettre à jour lastUpdate

            // emettre eventuelement update
        }

        async update() {
            // verifie la derniere mise à jours n'est pas trop recentes

            this.forceUpdate();
        }


        async send () {
            // verifier que l'utilisateur peut envoyer des messages

            // ajouter le message sur la liste de tête this.#head et eventuellement sur this.#part avec le status envoi

            // envoyer une requete au serveur pour envoyer le message (faire en même temps lastUpdate)

            // ajouter les nouveaux messages

            // retirer les messages supprimer

            // modifier les eventuels messages modifiés

            // changer le status du message
        }

        // On rappel à chaque fois les mêmes fonctionnalités pour mettre à jours les données
        // ==> faire une fonction pour le faire
        // On peut aussi décomposé cette fonction en plusieurs fonctions liés au messages
        // On peut aussi créer une classe pour les messages afin de leur permettre de se mettre à jour !
    }

})();