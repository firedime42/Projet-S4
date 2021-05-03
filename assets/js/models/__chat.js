(function () {

    const MAX_MESSAGES_PER_LOAD = 100;
    const MAX_TIME_BETWEEN_UPDATE = 1000;
    const MIN_TIME_BETWEEN_UPDATE = 200;

    const LOAD_DIRECTION_OLDER = -1;
    const LOAD_DIRECTION_NEWER = 1;

    /**
     * Ajoute un element à un tableau trié de facons à preserver l'ordre et de ne pas avoir de doublons.
     * @param {Array<Number>} arr
     * @param {Number} e 
     */
    function _pushUniqueSorted(arr, e) {
        let i = arr.length - 1;
        while (i >= 0 && arr[i] > e) i--;
        if (arr[i] != e) arr.splice(i+1, 0, e);
    }
    function _unshiftUniqueSorted(arr, e) {
        let i = 0;
        let len = arr.length;
        while (i < len && arr[i] < e) i--;
        if (arr[i] != e) arr.splice(i, 0, e);
    }

    class Message extends Listenable {
        static EVENT_UPDATE = 'update';

        constructor (id, author, content) {
            this.id = id;
            this.author = author;
            this.content = content;
        }

        update (msg_data) {
            if (this.id !== null && this.id != msg_data.id) return;

            this.id = msg_data.id;
            this.author = msg_data.author;
            this.content = msg_data.content;

            this.emit(Message.EVENT_UPDATE);
        }
    }

    class Chat extends Listenable {

        static EVENT_UPDATE = 'update';
        static EVENT_NEW_MESSAGE = 'new_message';
        static EVENT_RM_MESSAGE = 'rm_message';
        static EVENT_REBASE_HEAD = 'rebase_head';

        static LOAD_DIRECTION_OLDER = LOAD_DIRECTION_OLDER;
        static LOAD_DIRECTION_NEWER = LOAD_DIRECTION_NEWER;

        #id;

        #messages;
        #disp;// liste contenant les messages affichés
        #head;// liste contenant les messages sur la tête de liste
        #tail;

        #lastUpdate;

        #last_request_update;

        constructor (id) {
            this.#id = id;
            this.#head = [];
            this.#disp = [];
            this.#messages = {};
            this.#lastUpdate = 0;
            this.#last_request_update = 0;
        }

        /* Propriété de la partie affichée */
        isTail () { return this.#tail >= this.#disp[0]; }
        isHead () { return this.#head[this.#head.length - 1] <= this.#disp[this.#disp.length - 1]; }

        get id() { return this.#id; }

        /**
         * renvoi la liste des messages affichés
         * @returns {Array<Message>}
         */
        getDisplayedMessages () {
            let nb_messages = this.#disp.length;
            var msgs = new Array(nb_messages);
            for (let i = 0; i < nb_messages; i++)
                msgs[i] = this.#messages[this.#disp[i]];
            return msgs;
        }

        /**
         * Force la mise à jour des données du chat.
         */
        async forceUpdate () {
            let r = await request('/core/controller/chat.php', {
                action: 'update',
                id: this.#id,
                oldest_message: this.#disp[0] || 0,
                newest_message: this.#disp[this.#disp.length - 1] || 0,
                resp_max: MAX_MESSAGES_PER_LOAD,
                lastUpdate: this.#lastUpdate
            });
            /**
             * renvoi :
             * {
             *  rebaseHead: Boolean, // s'il faut recharger (trop de nouveaux messages)
             *  head: [ {}, ... ],
             *  removed: [ Number, ... ],
             *  edited: [ {}, ... ],
             *  lastUpdate: Number
             * }
             */

            if (r instanceof Error) {

                return r;
            }

            this.__updateFromData(r);
        }

        /**
         * Tente de mettre à jour les données provenant du serveur
         */
        update() {
            // on verifie l'age de la dernière requête
            if (Date.now() - this.#last_request_update < MIN_TIME_BETWEEN_UPDATE) return false;

            return this.forceUpdate();
        }

        async loadMore(dir = LOAD_DIRECTION_OLDER) {
            let r = await request('/core/controller/chat.php', {
                action: 'loadMore',
                id: this.#id,
                oldest_message: this.#disp[0],
                newest_message: this.#disp[this.#disp.length - 1],
                resp_max: MAX_MESSAGES_PER_LOAD,
                direction: dir,
                lastUpdate: this.#lastUpdate
            });

            if (r instanceof Error) {
                return r;
            }

            // ajout des messages avant ou après
            let nb_messages = r.messages.length;
            if (dir == LOAD_DIRECTION_OLDER)
                for (let i = nb_messages - 1; i >= 0; i--) {
                    this.__addMessage(r.messages[i]);
                    _unshiftUniqueSorted(this.#disp, r.messages[i].id);
                }
            else 
                for (let i = 0; i < nb_messages; i++) {
                    this.__addMessage(r.messages[i]);
                    _pushUniqueSorted(this.#disp, r.messages[i].id);
                }

            // traitement du reste des données
            this.__updateFromData(r);
        }

        /**
         * Envoi un message
         * @param {String} msg_content
         */
        async send(msg_content) {
            let r = await request('/core/controller/chat.php', {
                action: 'send',
                id: this.#id,
                content: msg_content,
                resp_max: MAX_MESSAGES_PER_LOAD,
                lastUpdate: this.#lastUpdate
            });

            if (r instanceof Error) {
                return r;
            }

            this.__updateFromData(r);

            return this.#messages[r.id];
        }

        goToHead() {
            this.unloaddisplayed();

            let nb_messages = this.#head.length;
            this.#disp = new Array(nb_messages);

            for (let i = 0; i < nb_messages; i++)
                this.#disp[i] = this.#head[i];
        }

        unloaddisplayed() {
            let first = this.#head[0];
            let i = 0;
            let nb_msg_in_disp = this.#disp.length;

            // on passe tous les messages présent dans le display.
            while (i < nb_msg_in_disp && this.#disp[i] < first) this.__removeMessage(this.#disp[i++]);

            // on reinitialise l'entête
            this.#disp = [];
        }

        unloadhead() {
            let last = this.#disp[this.#disp.length - 1];
            let i = 0;
            let nb_msg_in_head = this.#head.length;

            // on passe tous les messages présent dans le display.
            while (i < nb_msg_in_head && this.#head[i] <= last) i++;
            
            // on retire les messages restant
            for (;i<nb_msg_in_head;i++) this.__removeMessage(this.#head[i]);

            // on reinitialise l'entête
            this.#head = [];

            // on n'a plus de donnée
            this.#lastUpdate = 0;
        }

        /**
         * (privé) Ajoute un message
         * @param {Object} msg_data donnée du messages
         */
        __addMessage(msg_data) {
            if (!this.#messages[msg_data.id])
                this.#messages[msg_data.id] = new Message(msg_data.id, msg_data.author, msg_data.content);
            else
                this.#messages[msg_data.id].update(msg_data);
        }

        /**
         * (privé) Supprime un messages de la liste par son identifiant
         * @param {Number} msg_id 
         */
        __removeMessage(msg_id) {
            if (!this.#messages[msg_id]) return;

            // remove from this.#messages
            delete this.#messages[msg_id];

            // remove from this.#head
            let nb_messages = this.#head.length;
            if (this.#head[0] <= msg_id && msg_id <= this.#head[nb_messages - 1]) {
                let i = 0;
                while (i < nb_messages && this.#head[i] != msg_id) i++;
                if (i < nb_messages) this.#head.splice(i, 1);
            }

            // remove from this.#disp
            nb_messages = this.#disp.length;
            if (this.#disp[0] <= msg_id && msg_id <= this.#disp[nb_messages - 1]) {
                let i = 0;
                while (i < nb_messages && this.#disp[i] != msg_id) i++;
                if (i < nb_messages) this.#disp.splice(i, 1);
            }

            this.emit(Chat.EVENT_RM_MESSAGE, msg_id);
        }

        /**
         * (privé) update à partir d'un jeu de donnée issu d'une requête au serveur.
         * @param {Object} r les données
         */
        __updateFromData (r) {
            let displaying_head = this.isHead();
            
            if (r.rebaseHead) {
                let last = this.#disp[this.#disp.length - 1];
                let i = 0;
                let nb_msg_in_head = this.#head.length;

                // on passe tous les messages présent dans le display.
                while (i < nb_msg_in_head && this.#head[i] <= last) i++;
                
                // on retire les messages restant
                for (;i<nb_msg_in_head;i++) this.__removeMessage(this.#head[i]);

                // on reinitialise l'entête
                this.#head = [];
            }

            // definir le head.
            let nb_messages = r.head.length;
            for (let i = 0; i < nb_messages; i++) {
                this.__addMessage(r.head[i]);
                _pushUniqueSorted(this.#head, r.head[i].id);
            }
            
            // ajouter les messages à la liste affichés si le chat est en tête
            if (displaying_head)
                for (let i = 0; i < nb_messages; i++) {
                    let added = _pushUniqueSorted(this.#disp, r.head[i].id);
                    if (added) this.emit(Chat.EVENT_NEW_MESSAGE, this.#messages[r.head[i].id]);
                }

            // modifier tous les messages modifiés
            let nb_edited = r.edited.length;
            for (let i = 0; i < nb_edited; i++)
                if (this.#messages[r.edited[i].id])
                    this.#messages[r.edited[i].id].update(r.edited[i]);

            // retirer tous les messages retirés
            let nb_removed = r.removed.length;
            for (let i = 0; i < nb_removed; i++)
                this.__removeMessage(r.removed[i]);

            // maintenir la taille du head
            let nb_out_head = this.#head.length - MAX_MESSAGES_PER_LOAD;
            if (nb_out_head > 0) {
                let rm = this.#head.splice(0, nb_out_head);
                let last_message = this.#disp[this.#disp.length - 1];
                
                for (let i = 0; i < nb_out_head; i++)
                    if (rm[i] > last_message)
                        this.__removeMessage(rm[i]);
            }

            if (r.rebaseHead) this.emit(Chat.EVENT_REBASE_HEAD);

            this.#lastUpdate = r.lastUpdate;
        }


    }

    class ChatManager {
        #chats;
        #waiting;

        constructor () {
            this.#chats = {};
        }

        get(id) {

        }
    }

    window.Chat = Chat;
    window.ChatManager = new ChatManager();

})();