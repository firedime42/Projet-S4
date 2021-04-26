<?php 

require_once("sql.php");

function recupere_fichiers_dans_dossier($folderId){

	// Retourne les information concernant les fichiers contenus dans le dossier donné

	global $database;
	$query = "SELECT id FROM file WHERE location = $folderId";

	$resq = mysqli_query($database, $query);
	
	$folderarray=array();
	while($row = mysqli_fetch_assoc($resq)) {
		$folderarray[] = (int) $row["id"];
	}
	return $folderarray;	
}

function recupere_dossiers_dans_dossier($folderId){

	// Retourne les information concernant les fichiers contenus dans le dossier donné

	global $database;
	$query = "SELECT id FROM folder WHERE parent_id = $folderId";

	$resq = mysqli_query($database, $query);
	
	$folderarray=array();
	while($row = mysqli_fetch_assoc($resq)) {
		$folderarray[] = (int) $row["id"];
	}
	return $folderarray;
}

function recup_folder_id($id){
	global $database;
    $folder =  "SELECT * FROM folder WHERE id = $id";
	$result = mysqli_query($database, $folder);
	$folder_data=mysqli_fetch_assoc($result);
	
	return $folder_data;
}

function create_folder($nom,$group, $parent = null, $description=""){
	global $database;
	$id_chat=ajoute_chat();
	if(isset($parent))
		$query = "INSERT INTO folder (name,parent_id,group_id,description,chat_id) VALUES ('$nom',$parent,$group,$description,$id_chat)";
	else
		$query = "INSERT INTO folder (name,group_id,description,chat_id) VALUES ('$nom',$group,$description,$id_chat)";
	$res = mysqli_query($database, $query);
	return $res;
}

function recup_folder_nom_descr($nom,$description){
	global $database;
    $user =  "SELECT * FROM folder WHERE name = '$nom' ";
	$result = mysqli_query($database, $user);
	$file_data=mysqli_fetch_assoc($result);
	
	return $file_data;
}

function supprimer_dossier($id){
   global $database;
   supprime_chat_folder($id);
    $query = "DELETE FROM folder f WHERE f.id = $id";
    $res = mysqli_query($database, $query);
    return $res;
}

function nb_files($id){
	global $database;
	$query="SELECT * FROM file WHERE location=$id";
	$res=mysqli_query($database,$query);
	return mysqli_num_rows($res);
}

?>