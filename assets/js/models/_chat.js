(function () {

    const MAX_MESSAGES_PER_LOAD = 100;

    /**
     * Insert des nombres dans un tableau trié
     * @param {Array<Number>} array le tableau destination trié sans doublons
     * @param {Array<Number>} inserted les elements à inserer trié sans doublons
     */
    function _arraySortedInsert(array, inserted) {
        let i = 0, j = 0;
        let nb_elements = array.length;
        let nb_inserted = inserted.length;
        while (i < nb_elements && j < nb_inserted) {
            if (array[i] == inserted[j]) j++; // eviter les doublons
            else if (array[i] > inserted[j]) {
                array.splice(i++, 0, inserted[j++]);
                nb_elements++;
            }
            else i++;
        }
        while (j < nb_inserted) array.push(inserted[j++]);
    }

    /**
     * Retire des nombres dans un tableau trié
     * @param {Array<Number>} array le tableau destination trié sans doublons
     * @param {Array<Number>} inserted les elements à retirer trié sans doublons
     */
    function _arraySortedRemove(array, removed) {
        let i = 0, j = 0;
        let nb_elements = array.length;
        let nb_removed = removed.length;
        while (i < nb_elements && j < nb_removed) {
            if (array[i] == removed[j]) {
                array.splice(i--, 1);
                j++;
                nb_elements--;
            } else if (array[i] > removed[j]) j++;
            else i++;
        }
    }

    function _arrayRemove(array, removed) {
        let i = 0;
        let len_array = array.length;
        while (i < len_array && array[i] != removed) i++;
        if (i < len_array) array.splice(i, 1);
    }


    class Message extends Listenable {
        static EVENT_UPDATE = 'update';

        constructor (id, author, msg) {
            this.id = id;
            this.author = author;
            this.content = msg;
        }

        update (msg_data) {
            this.author = mdg_data.author;
            this.content = msg_data.content;
            this.emit('update');
        }
    }

    class Chat extends Listenable {
        static EVENT_NEW_MESSAGE = "new_message";
        static EVENT_EDIT_MESSAGE = "edit_message";
        static EVENT_RM_MESSAGE = "rm_message";
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

        /**
         * 
         * @param {Array<Object>} msgs les messages retournés par le serveur
         */
        __addMessages(msgs) {
            let nb_messages = msgs.length;

            // add message in messages or replace data
            for (let i = 0; i < nb_messages; i++) {
                let msg = msgs[i];
                if (!this.#messages[msg.id]) {
                    this.#messages[msg.id] = new Message(msg.id, msg.author, msg.content);
                }
            }

            
        }

        __newMessage(msg) {
            // gerer un cas inconnu
            if (this.#messages[msg.id]) { console.debug('something strange occured'); return; }

            // creation du nouveau message
            this.#messages[msg.id] = new Message(msg.id, msg.author, msg.content);

            // ajout à la tête
            this.#head.push(msg.id);

            // ajout à la parti affiché si necessaire
            if (this.isHead()) {
                this.#part.push(msg.id);
                this.emit(Chat.EVENT_NEW_MESSAGE, msg);
            }
        }

        /**
         * Met à jour un message
         * @param {Object} msg données sur le messages
         * @returns 
         */
        __updateMessage(msg) {
            if (!this.#messages[msg.id]) return;

            this.#messages[msg.id].update(msg);
        }

        /**
         * retire un message
         * @param {Number} msg_id 
         */
        __removeMessage(msg_id) {

            // remove from messages
            delete this.#messages[msg_id];

            // remove from part
            if (this.#part[0] <= msg_id && msg_id <= this.#part[this.#part.length - 1])
                _arrayRemove(this.#part, msg_id);

            // remove from head
            if (this.#head[0] <= msg_id)
                _arrayRemove(this.#head, msg_id);

            this.emit(Chat.EVENT_RM_MESSAGE, msg_id);
        }

        /**
         * Charges plus de messages dans une direction donnée.
         * @param {-1|1} direction la direction dans laquelle on doit étendre la partie affiché des messages : -1 anciens, 1 recent
         * @returns 
         */
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
            let last_message = this.#parts.length - 1;

            let r = await request('/core/controller/chat.php', {
                action: 'loadMore',
                oldest_message: this.#parts[0],
                newest_message: this.#parts[last_message],
                direction,
                lastUpdate: this.#lastUpdate
            });

            if (r instanceof Error) return r; // TODO : traiter l'erreur plus en detail
            
            // charger d'autres messages
            this.__addMessages(r.messages);

            // modifier eventuelement les messages
            let nb_messages_edited = r.edited.length;
            for (let i = 0; i < nb_messages_edited; i++)
                this.__updateMessage(r.messages[i]);

            // retirer les eventuels messages suprimés
            let nb_messages_removed = r.removed.length - 1;
            for (let i = 0; i < nb_messages_removed; i++)
                this.__removeMessage(r.removed[i]);

            // rajouter les messages en tête de discussion
            let nb_messages_added = r.new.length;
            for (let i = 0; i < nb_messages_added; i++)
                this.__newMessage(r.new[i]);

            // fusionner sur la tête de discussion
            if (this.#part[this.#part.length - 1] >= this.#head[0]) {

            }

            // mettre à jours lastUpdate
            this.#lastUpdate = r.lastUpdate;
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