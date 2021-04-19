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
function recherche_messages($id,$lastUpdate,$resp_max){
    global $database;
    $query="SELECT * FROM message m JOIN user u ON u.id=m.author WHERE m.last_update<$lastUpdate AND m.chat_id=$id LIMIT $resp_max";
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
        "publish_date" => $value["last_update"],
        "content"=>$value["message"]
        );
    }
    return $head;
}
function edition_suppresion($id,$oldest_message,$newest_message,$lastUpdate){
    global $database;
    $query="SELECT * FROM message WHERE chat_id=$id AND id BETWEEN $oldest_message AND $newest_message AND last_update>$lastUpdate";
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
                "publish_date" => $value["last_update"],
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
/*
function recup_messages_file($file){
    global $database;
    $query="SELECT * FROM message WHERE file_id=$file";
    $res=mysqli_query($database,$query);
    $message_list=array();
	while($row = mysqli_fetch_assoc($res)) {
	    $message_list[] = $row;
	}
    return $message_list;
}

function recup_messages_group($group){
    global $database;
    $query="SELECT * FROM message WHERE group_id=$group";
    $res=mysqli_query($database,$query);
    while($row = mysqli_fetch_assoc($res)) {
	    $message_list[] = $row;
	}
    return $res;
}*/
?>