<?php

function ajouter_message($message, $idFile, $groupId,$idUtilisateur) {
    global $database;
    $query="INSERT INTO message (message,author,group_id,file_id) VALUES('$message',$idUtilisateur,$groupId,$idFile)";
    $res=mysqli_query($database,$query);
    return $res;
}
function ajouter_message_group($message, $groupId, $idUtilisateur) {
    global $database;
    $query="INSERT INTO message (message,author,group_id) VALUES('$message',$idUtilisateur,$groupId)";
    $res=mysqli_query($database,$query);
    return $res;
}
function supprimer_message($id) {
    global $database;
    $query="DELETE FROM message WHERE id=$id";
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

function  edit_message($id,$message){
    global $database;
    $query="UPDATE FROM message SET message=$message WHERE id=$id";
    $res=mysqli_query($database,$query);
    return $res;
}
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
}
?>