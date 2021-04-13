<?php

require_once("sql.php");

function recup_file_id($id){
    global $database;
    $user =  "SELECT * FROM file WHERE id = $id";
	$result = mysqli_query($database, $user);
	$file_data=mysqli_fetch_assoc($result);
	
	return $file_data;
}
function recup_file_filename($filename){
    global $database;
    $user =  "SELECT * FROM file WHERE name = '$filename'";
    $result = mysqli_query($database, $user);
	$user_data=mysqli_fetch_assoc($result);
	
	return $user_data;
}
function supprime_file($id){
    global $database;
    $querry = "DELETE FROM file WHERE id=$id";
    $res=mysqli_query($database, $querry);
    
    return $res;
}
function create_file($folder,$filename,$content_type,$size,$description,$id_creator){
//créer un fichier 

    global $database;
    $querry = "INSERT INTO file (location, name, extension, creator_id,size,description) VALUES ($folder, '$filename', '$content_type', $id_creator,$size,'$description')";
    mysqli_query($database, $querry);
    $id=mysqli_insert_id($database);
    
    return $id;
}

function modifie_file($id,$nom,$description){
    global $database;
    $querry = "UPDATE file SET name=$nom, description='$description' WHERE id=$id";//description";
    $res=mysqli_query($database, $querry);
    
    return $res;
}

function finish_upload($id){
    global $database;
    $querry = "UPDATE file SET status='online' WHERE id=$id";//description";
    $res=mysqli_query($database, $querry);
    
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
?>