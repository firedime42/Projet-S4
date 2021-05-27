<?php


/*function ajouter_message_group($message, $groupId, $idUtilisateur) {
    global $database;
    $time = now();
    $query="INSERT INTO message (message,author,group_id,last_update,creation_date) VALUES('$message',$idUtilisateur,$groupId,$time,$time)";
    $res=mysqli_query($database,$query);
    return $res;
}

function recherche_message($query, $page, $nb_results){
    global $database;
	$offset = $nb_results * $page;
	$query = "SELECT * FROM message WHERE message LIKE '%$query%' LIMIT $nb_results OFFSET $offset";
	$resq = mysqli_query($database, $query);
	$messages=array();
	while($row = mysqli_fetch_assoc($resq)) {
		$messages[] = $row;
	}
	return $messages;
}

function recup_chat_folder($id){
    global $database;
    $query="SELECT id FROM chat WHERE folder_id=$id";
    $res=mysqli_query($database,$query);
    return mysqli_fetch_assoc($res)["id"];
}
function recup_chat_file($id){
    global $database;
    $query="SELECT id FROM chat WHERE file_id=$id";
    $res=mysqli_query($database,$query);
    return mysqli_fetch_assoc($res)["id"];
}

function supprime_chat_file($id){
    global $database;
    $querry = "DELETE FROM chat WHERE file_id=$id";
    $res=mysqli_query($database, $querry);
    
    return $res;
}

function supprime_chat_folder($id){
    global $database;
    $querry = "DELETE FROM chat WHERE folder_id=$id";
    $res=mysqli_query($database, $querry);
    return $res;
}

function edition_suppresion($id,$oldest_message,$newest_message,$lastUpdate,$direction,$resp_max){
    global $database;
    $lastUpdate = date("Y-m-d H:i:s", $lastUpdate/1000);
    switch ($direction) {
        case 1:
            $query="SELECT * ,m.id AS mess_id FROM message m JOIN user u ON u.id=m.author WHERE m.chat_id=$id AND m.id>$newest_message AND m.last_update>'$lastUpdate' LIMIT $resp_max";
            break;
        case -1:
            $query="SELECT * ,m.id AS mess_id FROM message m JOIN user u ON u.id=m.author ORDER BY m.id DESC WHERE m.chat_id=$id AND m.id<$oldest_message AND m.last_update>'$lastUpdate' LIMIT $resp_max";
            break;
        default:
            $query="SELECT * ,m.id AS mess_id FROM message m JOIN user u ON u.id=m.author WHERE m.chat_id=$id AND m.id BETWEEN $oldest_message AND $newest_message AND m.last_update>'$lastUpdate'";
            break;
    }
    $res=mysqli_query($database,$query);
    $list=mysqli_fetch_assoc($res);
    var_dump($query);
    $removed=array();
    $edited=array();
    while ($value = mysqli_fetch_array($res)) {
        if ((int)$value["deleted"]){
            $removed[]=array(
                "id" => (int)$value["mess_id"],
                "author" => array(
                    "id"=>(int)$value["author"],
                    "name"=>$value["username"]
                ),
                "publish_date" => strtotime($value["last_update"]),
                "content"=>$value["message"]
                );
        }else{
            $edited[]=array(
                "id" => (int)$value["mess_id"],
                "author" => array(
                    "id"=>(int)$value["author"],
                    "name"=>$value["username"]
                ),
                "publish_date" => strtotime($value["last_update"]),
                "content"=>$value["message"]
                );
        }
    }
    return array( "removed" => $removed,"edited"=>$edited); 
}
function recherche_messages($id,$lastUpdate,$resp_max,$newest_message,$oldest_message,$direction){
    global $database;
    //$lastUpdate = date("Y-m-d H:i:s", $lastUpdate/1000); // mal théo !
    switch ($direction) {
        case 1:
            $query="SELECT *,m.id AS mess_id FROM message m JOIN user u ON u.id=m.author WHERE m.id>$newest_message AND m.last_update>'$lastUpdate' AND m.chat_id=$id LIMIT $resp_max";
            break;
        case -1:
            $query="SELECT *,m.id AS mess_id FROM message m ORDER BY m.id DESC JOIN user u ON u.id=m.author WHERE m.id<$oldest_message AND m.last_update<'$lastUpdate' AND m.chat_id=$id LIMIT $resp_max";
            break;
        default:
        $query="SELECT *,m.id AS mess_id FROM message m JOIN user u ON u.id=m.author WHERE m.last_update>'$lastUpdate' AND m.chat_id=$id LIMIT $resp_max";
            break;
    }
    $res=mysqli_query($database,$query);
    var_dump($query);
    $head=array();
    while($value = mysqli_fetch_array($res)){
        $head[]=array(
        "id" => (int)$value["mess_id"],
        "author" => array(
            "id"=>(int)$value["author"],
            "name"=>$value["username"]
        ),
        "publish_date" => strtotime($value["last_update"]),
        "content"=>$value["message"]
        );
    }
    return $head;
}

*/

/**
 * Converti une ligne retournée par la base de donnée en un message dans le format attendu par le protocole.
 */
function parseMessage($msg_row) {
    return array(
        "id" => (int)$msg_row["id"],
        "author" => array(
            "id"=>(int)$msg_row["author"],
            "name"=>$msg_row["username"]
        ),
        "publish_date" => (int) $msg_row["last_update"],
        "content"=>$msg_row["message"]
    );
}

/**
 * Fonction qui recupère plus de messages dans une direction
 */
function loadMore($chat_id, $resp_max, $newest_message, $oldest_message, $direction) {
    global $database;
    $query_base = "SELECT msg.id, msg.author, msg.message, msg.last_update, u.username FROM `message` AS msg JOIN `user` AS u ON msg.author = u.id WHERE msg.chat_id = $chat_id AND msg.deleted = 0";
    $load_newer = $query_base." AND msg.id > $newest_message ORDER BY msg.id LIMIT $resp_max";
    $load_older = $query_base." AND msg.id < $oldest_message ORDER BY msg.id DESC LIMIT $resp_max";

    // recuperer les messages depuis la base de donnée
    $res = mysqli_query($database, ($direction == 1) ? $load_newer : $load_older);
    $messages = array();
    while ($msg_row = mysqli_fetch_assoc($res)) {
        $messages[] = parseMessage($msg_row);
    }

    // renverser l'ordre si necessaire
    if ($direction == -1) $messages = array_reverse($messages);

    return $messages;
}

/**
 * Récupère les informations necessaires pour detecter les messages supprimés et modifiés edepuis la dernière requête de mise à jour
 */
function getHeadEditedRemovedMessage($chat_id, $lastUpdate, $resp_max, $newest_message, $oldest_message) {
    global $database;
    $query_base = "SELECT msg.id, msg.author, msg.message, msg.deleted, msg.last_update, u.username FROM `message` AS msg JOIN `user` AS u ON msg.author = u.id WHERE msg.chat_id = $chat_id";
    
    $query_between = 
        "
        ($query_base AND (msg.id > $newest_message AND msg.creation_date < $lastUpdate) AND msg.last_update > $lastUpdate ORDER BY msg.id DESC LIMIT $resp_max)
        UNION
        ($query_base AND (msg.id >= $oldest_message AND msg.id <= $newest_message) AND msg.last_update > $lastUpdate ORDER BY msg.id DESC)
        "
    ;
    $query_head    = $query_base." AND msg.deleted = 0 AND msg.creation_date > $lastUpdate ORDER BY msg.id DESC LIMIT $resp_max";
    
    $edited = array(); // contenant les messages qui ont été édités
    $removed = array(); // contenant les messages qui ont été supprimés
    $head = array(); // contenant les messages qui ont été ajoutés à la tête de chat

    // recuperation des elements modifiés et supprimés
    $req_between = mysqli_query($database, $query_between);
    while ($msg_row = mysqli_fetch_array($req_between)) {
        if ($msg_row['deleted'] == '1') $removed[] = $msg_row['id'];
        else if ($msg_row['deleted'] == '0') $edited[] = parseMessage($msg_row);
    }

    // recuperation de la tête
    $req_head = mysqli_query($database, $query_head);
    while ($msg_row = mysqli_fetch_array($req_head)) {
        $head[] = parseMessage($msg_row);
    }

    // on remet dans l'ordre croissant
    $removed = array_reverse($removed);
    $edited = array_reverse($edited);
    $head = array_reverse($head);

    // on retourne les resultats
    return array(
        "edited" => $edited,
        "removed" => $removed,
        "head" => $head
    );
}

function recup_chat($id){
    global $database;
    $query="SELECT id FROM chat WHERE id=$id";
    $res=mysqli_query($database,$query);
    return mysqli_fetch_assoc($res)["id"];
}

function recup_message($id){
    global $database;
    $query="SELECT * FROM message WHERE id=$id";
    $res=mysqli_query($database,$query);
    return mysqli_fetch_array($res);
}
function is_author($user,$id){
    global $database;
    $query="SELECT * FROM message WHERE author=$user AND id=$id";
    $res=mysqli_query($database,$query);
    return mysqli_num_rows($res)>0;
}
/**
 * Ajoute un message à un fil de discussion
 */
function ajouter_message($group_id,$chat,$message,$idUtilisateur) {
    global $database;
    $time = now();
    $message=mysqli_real_escape_string($database, $message);
    $query="INSERT INTO message (message,author,chat_id,last_update,creation_date) VALUES('$message',$idUtilisateur,$chat,$time,$time)";
    $success = mysqli_query($database,$query);
    $id=mysqli_insert_id($database);
    if ($success) {
        modif_nb_messages($group_id, 1);
        $query="UPDATE chat SET last_update=$time WHERE id=$chat";
        mysqli_query($database,$query);
    }
    return $success ? $id : -1;
}
function supprimer_message($id) {
    global $database;
    $time = now();
    $query="UPDATE message SET deleted=1, last_update=$time WHERE id=$id";
    $success=mysqli_query($database,$query);
    if ($success) {
        modif_nb_messages($group_id, -1);
    }
    return $success;
}
function edit_message($id,$message){
    global $database;
    $time = now();
    $message=mysqli_real_escape_string($database, $message);
    $query="UPDATE FROM message SET message='$message', last_update=$time WHERE id=$id";
    $res=mysqli_query($database,$query);
    return $res;
}

function ajoute_chat(){
    global $database;
    $time=(int)(microtime(true)*1000);
    $query="INSERT INTO chat (last_update) VALUES ($time)";
    mysqli_query($database,$query);
    return mysqli_insert_id($database);
}



function read_chat($chat,$user){
    global $database;
    $query="SELECT * FROM chatUser WHERE chat_id=$chat AND user_id=$user";
    $res=mysqli_query($database,$query);
    $time=now();
    if(mysqli_num_rows($res)>=1){
        $query="UPDATE chatUser SET last_update = $time WHERE chat_id=$chat AND user_id=$user";
    }else{
        $query="INSERT INTO chatUser(chat_id,user_id,last_update) VALUES ($chat,$user,$time)";
    }
    mysqli_query($database,$query);
}

function update_chat($id){
    global $database;
    $time=now();
    $query="UPDATE chat (last_update) VALUES ($time) WHERE id=$id";
    mysqli_query($database,$query);
}

function clear_chat($chat_id) {
    global $database;

    $query_remove_all_messages = "DELETE FROM message WHERE chat_id = $chat_id";
    $query_remove_all_userChat = "DELETE FROM chatUser WHERE chat_id = $chat_id";

    $success1 = mysqli_query($database, $query_remove_all_messages);
    $success2 = mysqli_query($database, $query_remove_all_userChat);

    return $success1 && $success2;
}

function remove_chat($chat_id) {
    global $database;
    // clear chat
    $success1 = clear_chat($chat_id);

    // remove chat
    $query_remove_chat = "DELETE FROM chat WHERE id = $chat_id";
    $success2 = mysqli_query($database, $query_remove_chat);

    return $success1 && $success2;
}
?>