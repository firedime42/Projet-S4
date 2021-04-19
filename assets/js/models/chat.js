(function () {

    const MAX_MESSAGES_PER_LOAD = 100;
    const MAX_MESSAGES_DISPLAYED = 200;

    const MAX_TIME_BETWEEN_UPDATE = 1000;
    const MIN_TIME_BETWEEN_UPDATE = 500;

    const LOAD_DIRECTION_OLDER = -1;
    const LOAD_DIRECTION_NEWER = 1;

    function _removeFromSortedList(arr, removed) {
        let nb_removed = removed.length;
        let nb_elements = arr.length;

        let i = 0, j = 0;
        while (i < nb_removed && j < nb_elements) {
            if (removed[i] == arr[j]) {
                arr.splice(j, 1);
                nb_elements--;
                i++;
            } else if (removed[i] < arr[j]) i++;
            else if (removed[i] > arr[j]) j++;
        }
    }

    function _sortedIndexOf(arr, e) {
        let nb_elements = arr.length;
        if (nb_elements == 0 || arr[0] > e || arr[nb_elements - 1] < e) return -1;

        let i = 0;
        while (i < nb_elements && arr[i] < e) i++;

        return arr[i] == e ? i : -1;
    }

    function _pushMaxLength(arr, values, max_length) {
        let len_arr = arr.length;
        let nb_values = values.length;
        arr.push(...values);
        let total_length = len_arr + nb_values;
        let nb_removed = (total_length > max_length) ? total_length - max_length : 0;
        return arr.splice(0, nb_removed);
    }

    function _unshiftMaxLength(arr, values, max_length) {
        let len_arr = arr.length;
        let nb_values = values.length;
        arr.push(...values);
        let total_length = len_arr + nb_values;
        let nb_removed = (total_length > max_length) ? total_length - max_length : 0;
        return arr.splice(0, nb_removed);
    }

    class Message extends Listenable {
        static EVENT_UPDATE = 'update';
        static EVENT_REMOVE = 'remove';

        constructor(data) {
            this.id = null;
            this.author = null;
            this.content = null;
            this.publication_date = Date.now() / 1000;

            this.update(data);
        }

        update(data) {
            if (this.id !== null && this.id != data.id) return;

            if (data.id instanceof Number && data.id >= 0) this.id = data.id;
            if (data.author != null
                && data.author.id instanceof Number && data.author.id >= 0
                && data.author.name instanceof String && data.author.name.length > 0
            )
                this.author = {
                    id: data.author.id,
                    name: data.author.name
                };

            if (data.content instanceof String && data.content.length > 0) this.content = data.content;
            if (data.publish_date instanceof Number) this.publish_date = data.publish_date;

            this.emit(Message.EVENT_UPDATE);
        }
    }

    class Chat extends Listenable {

        static EVENT_UPDATE = 'update';
        static EVENT_NEW_MESSAGE = 'new_message';
        static EVENT_NEW_MESSAGE_DISPLAYED = 'new_message_displayed';
        static EVENT_RM_MESSAGE = 'rm_message';
        static EVENT_REBASE = 'rebase';

        static LOAD_DIRECTION_OLDER = LOAD_DIRECTION_OLDER;
        static LOAD_DIRECTION_NEWER = LOAD_DIRECTION_NEWER;

        #id;

        #messages; // objets contenant les données des messages sur le chat
        #disp;     // liste contenant les identifiant des messages affichés sur le chats
        #head;     // liste contenant les identifiant des messages en tête du chat (les derniers messages)
        #tail;     // identifiant du premier message du chat

        #last_request_update
        #lastUpdate;
        #update;

        constructor (id) {
            this.#id = id;
            this.#head = [];
            this.#disp = [];
            this.#tail = 0;
            this.#messages = {};
            this.#lastUpdate = 0;
            this.#last_request_update = 0;
            this.#update = new SuperPromise().resolve();

            this.keepHead = true; // indique s'il faut rester en tête ou abandonner la tête
        }

        isTail() { return this.#tail >= this.#disp[0]; }
        isHead() { return this.#disp[this.#disp.length - 1] == this.#head[this.#head.length - 1]; }
        
        get id() { return this.#id; }
        get displayed() {
            let nb_messages = this.#disp.length;
            var msgs = new Array(nb_messages);
            for (let i = 0; i < nb_messages; i++)
                msgs[i] = this.#messages[this.#disp[i]];
            return msgs;
        }

        /**
         * Affiche la tête de discussion du chat
         * @returns 
         */
        displayHead() {
            this.keepHead = true;
            if (this.isHead()) return this;

            let nb_messages_displayed = this.#disp.length;
            let nb_messages_head = this.#head.length;

            // on retire les messages
            for (let i = 0; i < nb_messages_displayed; i++)
                if (this.#disp[i] < this.#head[0])
                    delete this.#disp[i];

            // on remplace par les messages de head
            this.#disp = new Array(nb_messages_head);
            for (let i = 0; i < nb_messages_head; i++)
                this.#disp[i] = this.#head[i];

            this.emit(Chat.EVENT_REBASE);

            return this;
        }

        /**
         * Realise possiblement une mise à jour si la dernière n'est pas trop recente
         * @returns 
         */
        update() {
            if (Date.now() - this.#last_request_update < MIN_TIME_BETWEEN_UPDATE) return this;
            
            return this.forceUpdate();
        }

        /**
         * Force la mise à jour
         * @returns 
         */
        async forceUpdate() {
            if (this.#update.state == SuperPromise.STATE_PENDING)
                return await this.#update.promise;
            
            this.#last_request_update = Date.now();
            this.#update.renew();
            let v = this.#update.nb_renew;

            // creation de la requete
            let r = await request('/core/controller/chat.php', {
                action: 'update',
                id: this.#id,
                oldest_message: this.#disp[0] || 0,
                newest_message: this.#disp[this.#disp.length - 1] || 0,
                resp_max: MAX_MESSAGES_PER_LOAD,
                lastUpdate: this.#lastUpdate
            });

            // cas : une autre update à été forcé
            if (this.#update.nb_renew != v) return await this.#update.promise;
            
            // cas : une erreur c'est produite
            if (r instanceof Error) { this.#update.resolve(r); return r; }

            // On effectu l'update normalement
            this.__updateFromData(r);

            this.#update.resolve(this);
            this.#last_request_update = Date.now();
            return this;
        }

        /**
         * Ajoute un message à #messages
         * @param {Object} msg 
         */
        __addMessage(msg) {
            if (!this.messages[msg.id])
                this.messages[msg.id] = new Message(msg);
            else
                this.messages[msg.id].update(msg);
        }

        /**
         * Supprime un messages de #disp, #head, #messages
         * @param {Array<Number>} removed 
         */
        __deleteMessages(removed) {
            let nb_removed = removed.length;

            _removeFromSortedList(this.#disp, removed);
            _removeFromSortedList(this.#head, removed);

            for (let i = 0; i < nb_removed; i++)
                if (this.#messages[removed[i]]) {
                    this.emit(Chat.EVENT_RM_MESSAGE, this.#messages[removed[i]]);
                    delete this.#messages[removed[i]];
                }
        }

        /**
         * Supprime les messages qui ne sont plus utilisés
         */
        __garbageCollect() {
            let ids = Object.keys(this.#messages).sort((a,b) => a - b);

            _removeFromSortedList(ids, this.#disp);
            _removeFromSortedList(ids, this.#head);

            let nb_ids = ids.length;
            for (let i = 0; i < nb_ids; i++)
                delete this.#messages[ids[i]];
        }

        /**
         * 
         * @param {Object} data 
         */
        __updateFromData(data) {
            if (this.#lastUpdate >= data.lastUpdate) return;

            let isHead = this.isHead();

            let nb_news = data.head.length;
            let nb_edited = data.edited.length;

            // messages en tête de liste
            var head_id = new Array(nb_news);
            for (let i = 0; i < nb_news; i++) {
                head_id[i] = data.head[i].id;
                this.__addMessage(data.head[i]);
            }
            _pushMaxLength(this.#head, head_id, MAX_MESSAGES_PER_LOAD);
            

            // messages édités
            for (let i = 0; i < nb_edited; i++)
                if (this.messages[data.edited[i].id])
                    this.messages[data.edited[i].id].update(data.edited[i]);

            // messages supprimés
            this.__deleteMessages(data.removed);

            if (isHead) {
                if (nb_news == MAX_MESSAGES_PER_LOAD && this.keepHead) this.displayHead();
                else if (nb_news < MAX_MESSAGES_PER_LOAD) {
                    let nb_disp = this.#disp.length
                    if (nb_disp + nb_news > MAX_MESSAGES_DISPLAYED && !this.keepHead) {
                        // on décroche : on n'est plus à la tête
                        for (let i = 0; i < MAX_MESSAGES_DISPLAYED - nb_disp; i++) {
                            this.#disp.push(head_id[i]);
                            this.emit(Chat.EVENT_NEW_MESSAGE_DISPLAYED, this.#messages[head_id[i]]);
                        }
                    } else {
                        for (let i = 0; i < nb_news; i++)
                            this.emit(Chat.EVENT_NEW_MESSAGE_DISPLAYED, this.#messages[head_id[i]]);
                        
                        let rm = _pushMaxLength(this.#disp, head_id, MAX_MESSAGES_DISPLAYED);
                        for (let i = 0, nb_rm = rm.length; i < nb_rm; i++)
                            this.emit(Chat.EVENT_RM_MESSAGE, this.#messages[head_id[i]]);
                    }
                }
            }

            // suppression des messages inutiles
            this.__garbageCollect();

            this.#lastUpdate = data.lastUpdate;
        }

        /**
         * Envoi un message au serveur
         * @param {Message}
         */
        async send(msg) {
            if (!(msg instanceof Message)) return _error(-1);

            let r = await request('/core/controller/chat.php', {
                action: 'send',
                id: this.#id,
                content: msg.content
            });

            if (r instanceof Error) return r;

            msg.id = r.id;
            this.#messages[r.id] = msg;
            
            this.forceUpdate();
        }

        /**
         * Supprime un message par son identifiant
         * @param {Number} msg_id 
         * @returns 
         */
        async remove(msg_id) {
            let r = await request('/core/controller/chat.php', {
                action: 'remove',
                chat_id: this.#id,
                msg_id: msg_id
            });

            if (r instanceof Error) return r;

            this.__deleteMessages([ msg_id ]);

            return true;
        }

        /**
         * Modifie un message via son identifiant
         * @param {Number} msg_id identifiant du message
         * @param {String} content le contenu du message
         * @returns 
         */
        async edit(msg_id, content) {
            let r = await request('/core/controller/chat.php', {
                action: 'edit',
                chat_id: this.#id,
                msg_id: msg_id,
                content: content
            });

            if (r instanceof Error) return r;

            if (this.#messages[msg_id])
                this.#messages[msg_id].update({ content });

            return true;
        }

        /**
         * Charge plus de message
         * @param {-1|1} direction la direction dans laquel chargé plus de message
         * @returns 
         */
        async loadMore(direction = LOAD_DIRECTION_OLDER) {
            if (direction != LOAD_DIRECTION_OLDER && direction != LOAD_DIRECTION_NEWER) return _error(-1);

            let r = await request('/core/controller/chat.php', {
                action: 'loadMore',
                id: this.#id,
                oldest_message: this.#disp[0] || 0,
                newest_message: this.#disp[this.#disp.length - 1] || 0,
                direction,
                resp_max: MAX_MESSAGES_PER_LOAD,
                lastUpdate: this.#lastUpdate
            });

            if (r instanceof Error) return r;

            // ajout des données sur la liste d'affichage
            let nb_messages = r.messages.length;
            var msgs_id = new Array(nb_messages);
            for (let i = 0; i < nb_messages; i++) {
                msgs_id[i] = r.messages[i].id;
                this.__addMessage(r.messages[i]);
                this.emit(Chat.EVENT_NEW_MESSAGE_DISPLAYED, this.#messages[r.messages[i].id]);
            }

            let rm = (direction == LOAD_DIRECTION_OLDER) ?
                _unshiftMaxLength(this.#disp, msgs_id, MAX_MESSAGES_DISPLAYED):
                _pushMaxLength(this.#disp, msgs_id, MAX_MESSAGES_DISPLAYED);
            
            for (let i = 0, nb_rm = rm.length; i < nb_rm; i++)
                this.emit(Chat.EVENT_RM_MESSAGE_DISPLAYED, rm[i]);

            this.__garbageCollect();

            // mise à jour du reste des données
            this.__updateFromData(r);

        }

    }


    class ChatManager {
        #chats;

        constructor () {
            this.#chats = {};
        }

        /**
         * récupère un chat par son identifiant
         * @param {*} id 
         * @returns 
         */
        async get(id) {
            if (!this.#chats[id])  {
                let chat = new Chat(id);
                let r = await chat.update();

                if (r instanceof Error) return r;

                this.chats[id] = chat;
            }

            return this.#chats[id];
        }
    }

    window.Message = Message;
    window.Chat = Chat;
    window.ChatManager = new ChatManager();
})