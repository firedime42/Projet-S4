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
	$query = "SELECT name FROM folder WHERE parent_id = $folderId";

	$resq = mysqli_query($database, $query);
	
	$folderarray=array();
	while($row = mysqli_fetch_assoc($resq)) {
		$folderarray[] = (int) $row["name"];
	}
	return $folderarray;
}

function recup_folder_id($id){
	global $database;
    $folder =  "SELECT f.*, COUNT(DISTINCT m.id) AS nb_messages FROM folder f JOIN message m ON f.chat_id=m.chat_id WHERE f.id =$id AND m.deleted=0";
	$result = mysqli_query($database, $folder);
	$folder_data=mysqli_fetch_assoc($result);
	
	return $folder_data;
}

function create_folder($nom, $group, $parent = null, $description=""){
	global $database;
	$id_chat=ajoute_chat();
	if(isset($parent))
		$query = "INSERT INTO folder (name,parent_id,group_id,description,chat_id) VALUES ('$nom',$parent,$group,'$description',$id_chat)";
	else
		$query = "INSERT INTO folder (name,group_id,description,chat_id) VALUES ('$nom',$group,'$description',$id_chat)";
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
    $query = "DELETE FROM folder WHERE id = $id";
	var_dump($query);
    return mysqli_query($database, $query);
}

function nb_files($id){
	global $database;
	$query="SELECT * FROM file WHERE location=$id";
	$res=mysqli_query($database,$query);
	return mysqli_num_rows($res);
}

function update_folder($folder,$user){
    global $database;
    $query="SELECT * FROM folderUser WHERE folder_id=$folder AND user_id=$user";
    $res=mysqli_query($database,$query);
    $time=(int) (microtime(true) * 1000);
    if(mysqli_num_rows($res)>=1){
        $query="UPDATE folderUser (last_update) VALUES($time) WHERE folder_id=$folder AND user_id=$user";
    }else{
        $query="INSERT INTO folderUser(folder_id,user_id,last_update) VALUES ($folder,$user,$time)";
    }
    $res=mysqli_query($database,$query);
	return $res;
}

function update_folder_everyone($folder){
	global $database;
	$query="SELECT gu.user_id FROM folder f JOIN groupUser gu ON gu.group_id=f.group_id WHERE f.id=$folder";
	$res=mysqli_query($database,$query);
	$res=mysqli_fetch_array($res);
	$query="INSERT INTO folderUser(folder_id,user_id,last_update) VALUES ";
	foreach ($res as $user) {
		$query.="($folder,$user,0),";
	}
	$query=substr($query,0,-1);
	$res=mysqli_query($database,$query);
	return $res;
}
?>