<?php

require_once("sql.php")
;
function recup_file_id($id){
    global $database;
    $user =  "SELECT * FROM file WHERE id=$id";
	$result = mysqli_query($database, $user);
	$file_data=mysqli_fetch_assoc($result);
	
	return $file_data;
}
function recup_file($id,$user){
    global $database;
    $user =  "SELECT f.*, COUNT(DISTINCT m.id) AS nb_comments, COUNT(DISTINCT fl.id) AS nb_likes, EXISTS(SElECT * FROM file_liked WHERE file_id=$id AND user_id=$user) AS liked, u.username AS creator_name FROM file f JOIN message m ON f.chat_id=m.chat_id JOIN file_liked fl ON fl.file_id=f.id JOIN user u ON u.id = f.creator_id WHERE f.id =$id AND m.deleted=0";
	$result = mysqli_query($database, $user);
	$file_data=mysqli_fetch_assoc($result);
	
	return $file_data;
}
function recup_file_filename($filename){
    global $database;
    $user =  "SELECT * FROM file WHERE name = '$filename'";
    $result = mysqli_query($database, $user);
	$user_data = mysqli_fetch_assoc($result);
	
	return $user_data;
}
function supprime_file($id) {
    global $database;
    $query = "DELETE FROM file WHERE id=$id";
    $res=mysqli_query($database, $query);
    $filename = dirname(__FILE__)."/../files/$id.bin";
    if (is_numeric($id)) {
        if (file_exists($filename)) unlink($filename);
    }

    return $res;
}
function create_file($group,$folder,$filename,$content_type,$size,$description,$id_creator){
//créer un fichier 
    global $database;

	$id_chat=ajoute_chat();
    $last_update = now();
    $query = "INSERT INTO file (location, name, extension, creator_id,size,description,chat_id, creation_date, last_update) VALUES ($folder, '$filename', '$content_type', $id_creator,$size,'$description',$id_chat,$last_update,$last_update)";
    mysqli_query($database, $query);
    $id=mysqli_insert_id($database);
    modif_nb_files($group, 1);
    return $id;
}

function modifie_file($id,$nom,$description){
    global $database;

    $last_update = now();

    $str_nom=mysqli_real_escape_string($database, $nom);
    $str_description=mysqli_real_escape_string($database, $description);
    
    $query = "UPDATE file SET name='$str_nom', description='$str_description', last_update=$last_update WHERE id=$id";//description";
    $res=mysqli_query($database, $query);
    
    return $res;
}

function finish_upload($id){
    global $database;

    $last_update = now();
    $query = "UPDATE file SET status='online', last_update=$last_update WHERE id=$id";//description";
    $res=mysqli_query($database, $query);
    
    return $res;
}

function search_files($needle,$page,$nb_results){
    global $database;
	$offset = $nb_results * $page;
	$query = "SELECT id FROM file WHERE name LIKE '%$needle%' OR description LIKE '%$needle%' LIMIT $nb_results OFFSET $offset";
	$resq = mysqli_query($database, $query);
	
	$grouplist=array();
		while($row = mysqli_fetch_assoc($resq)) {
			$grouplist[] = $row["id"];
		}
	return $grouplist;
}

function modif_nombre_like($id,$edit){
    global $database;

    $last_update = now();
    $query = "UPDATE file SET nb_likes=nb_likes+$edit, last_update=$last_update WHERE id=$id";
    $res = mysqli_query($database, $query);
    //var_dump($query,$res);
    return $res;
}

function like_file($file,$user){
    global $database;
    $query = "INSERT INTO file_liked (file_id,user_id) VALUES($file,$user)";
    $res = mysqli_query($database, $query);
    //var_dump($query,$res);
    return $res;
}

function unlike_file($file,$user){
    global $database;
    $query = "DELETE FROM file_liked WHERE file_id=$file AND user_id=$user";
    $res = mysqli_query($database, $query);
    //var_dump($query,$res);
    return $res;
}

function is_liked($file,$user){
    global $database;
    $query = "SELECT * FROM file_liked WHERE file_id=$file AND user_id=$user";
    $res = mysqli_query($database, $query);
    //var_dump($query,"test",$res,(mysqli_num_rows($res) >= 1));
    return (mysqli_num_rows($res) >= 1);
}

function is_creator($user,$id){
    global $database;
    $query="SELECT * FROM file WHERE creator_id=$user AND id=$id";
    $res=mysqli_query($database,$query);
    return mysqli_num_rows($res)>0;
}
?>