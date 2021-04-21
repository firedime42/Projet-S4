<?php

function ajouter_message_group($message, $groupId, $idUtilisateur) {
    global $database;
    $query="INSERT INTO message (message,author,group_id) VALUES('$message',$idUtilisateur,$groupId)";
    $res=mysqli_query($database,$query);
    return $res;
}

/*function recherche_message($query, $page, $nb_results){
    global $database;
	$offset = $nb_results * $page;
	$query = "SELECT * FROM message WHERE message LIKE '%$query%' LIMIT $nb_results OFFSET $offset";
	$resq = mysqli_query($database, $query);
	$messages=array();
	while($row = mysqli_fetch_assoc($resq)) {
		$messages[] = $row;
	}
	return $messages;
}*/
function recherche_messages($id,$lastUpdate,$resp_max,$newest_message,$oldest_message,$direction){
    global $database;
    switch ($direction) {
        case 1:
            $query="SELECT * FROM message m JOIN user u ON u.id=m.author WHERE m.id>$newest_message AND m.last_update<$lastUpdate AND m.chat_id=$id LIMIT $resp_max";
            break;
        case -1:
            $query="SELECT * FROM message m ORDER BY m.id DESC JOIN user u ON u.id=m.author WHERE m.id<$oldest_message AND m.last_update<$lastUpdate AND m.chat_id=$id LIMIT $resp_max";
            break;
        default:
        $query="SELECT * FROM message m JOIN user u ON u.id=m.author WHERE m.last_update<$lastUpdate AND m.chat_id=$id LIMIT $resp_max";
            break;
    }
    $res=mysqli_query($database,$query);
    $res= mysqli_fetch_assoc($res);
    $head=array();
    foreach($res as $value){
        $head[]=array(
        "id" => $value["id"],
        "author" => array(
            "id"=>$value["author"],
            "name"=>$value["username"]
        ),
        "publish_date" => strtotime($value["last_update"]),
        "content"=>$value["message"]
        );
    }
    return $head;
}
function edition_suppresion($id,$oldest_message,$newest_message,$lastUpdate,$direction,$resp_max){
    global $database;
    switch ($direction) {
        case 1:
            $query="SELECT * FROM message WHERE chat_id=$id AND id>$newest_message AND last_update>$lastUpdate LIMIT $resp_max";
            break;
        case -1:
            $query="SELECT * FROM message ORDER BY id DESC WHERE chat_id=$id AND id<$oldest_message AND last_update>$lastUpdate LIMIT $resp_max";
            break;
        default:
            $query="SELECT * FROM message WHERE chat_id=$id AND id BETWEEN $oldest_message AND $newest_message AND last_update>$lastUpdate";
            break;
    }
    
    $res=mysqli_query($database,$query);
    $list=mysqli_fetch_assoc($res);
    $removed=array();
    $edited=array();
    foreach ($list as $value) {
        if ((int)$value["deleted"]){
            $removed[]=array(
                "id" => $value["id"],
                "author" => array(
                    "id"=>$value["author"],
                    "name"=>$value["username"]
                ),
                "publish_date" => $value["last_update"],
                "content"=>$value["message"]
                );
        }else{
            $edited[]=array(
                "id" => $value["id"],
                "author" => array(
                    "id"=>$value["author"],
                    "name"=>$value["username"]
                ),
                "publish_date" => strtotime($value["last_update"]),
                "content"=>$value["message"]
                );
        }
    }
    return array( "removed" => $removed,"edited"=>$edited); 
}
function recup_chat($id){
    global $database;
    $query="SELECT * FROM chat WHERE id=$id";
    $res=mysqli_query($database,$query);
    return mysqli_fetch_assoc($res);
}
function recup_message($id){
    global $database;
    $query="SELECT FROM message WHERE id=$id";
    $res=mysqli_query($database,$query);
    return mysqli_fetch_array($res);
}
function is_author($user,$id){
    global $database;
    $query="SELECT * FROM message WHERE author='$user',id=$id";
    $res=mysqli_query($database,$query);
    return mysqli_num_rows($res)>0;
}
function ajouter_message($chat,$message,$idUtilisateur) {
    global $database;
    $query="INSERT INTO message (message,author,chat_id) VALUES('$message',$idUtilisateur,$chat)";
    $res=mysqli_query($database,$query);
    return $res;
}
function supprimer_message($id) {
    global $database;
    $query="UPDATE message SET deleted=1 WHERE id=$id";
    //$query="DELETE FROM message WHERE id=$id";
    $res=mysqli_query($database,$query);
    return $res;
}
function  edit_message($id,$message){
    global $database;
    $query="UPDATE FROM message SET message='$message' WHERE id=$id";
    $res=mysqli_query($database,$query);
    return $res;
}
function ajouter_chat_folder($id){
    global $database;
    $query="INSERT INTO chat (folder_id) VALUES($id)";
    $res=mysqli_query($database,$query);
    return $res;
}
function ajouter_chat_file($id){
    global $database;
    $query="INSERT INTO chat (file_id) VALUES($id)";
    $res=mysqli_query($database,$query);
    return $res;
}
?>