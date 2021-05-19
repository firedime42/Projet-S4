<?php 

require_once("sql.php");


/**
 * Met en forme les données d'un dossier et corrige les types
 */
function parseFolderData($row) {
	$folder = array();

	if (isset($row["id"])) $folder["id"] = (int) $row["id"];

	$folder["nom"] = $row["name"];
	$folder["description"] = $row["description"];

	if (isset($row["group_id"])) $folder["groupe_id"] = (int) $row["group_id"];
	if (isset($row["parent_id"])) $folder["parent_id"] = (int) $row["parent_id"]; 
	if (isset($row["chat_id"])) $folder["chat_id"] = (int) $row["chat_id"];

	$folder["nb_messages"] = (int) $row["nb_messages"];
	$folder["lastUpdate"] = $row["last_update"];

	if (isset($row["folders"])) $folder["folders"] = (int) $row["folders"];
	if (isset($row["files"])) $folder["files"] = (int) $row["files"];
	
	return $folder;
}

function parseFileData($row) {
	$file = array();
	
	if (isset($row['id'])) $file['id'] = (int) $row['id'];
	
	$file['nom'] = $row['nom'];
	$file['description'] = $row['description'];
	
	if (isset($row['chat_id'])) $file['chat_id'] = (int) $row['chat_id'];

	$file['nb_likes'] = (int) $row['nb_likes'];
	$file['nb_comments'] = (int) $row['nb_messages'];
	$file['liked'] = (bool) $row['liked'];
	$file['lastUpdate'] = $row['last_update'];

	return $file;
}

function getFolderData($id) {
	global $database;

	// sous requete compte le nombre de messages
	$query_nb_messages = "SELECT COUNT(*) FROM message WHERE chat_id = f.chat_id";
	
	// requete
	$query = "SELECT f.name, f.description, f.group_id, f.parent_id, f.chat_id, f.last_update, ($query_nb_messages) AS nb_messages FROM folder AS f WHERE f.id = $id";

	$res = mysqli_query($database, $query);
	
	if ($res == false) return -1; // erreur dans la requete
	else if (mysqli_num_rows($res) == 0) return 0; // erreur dans la réponse : dossier non trouvé
	else {
		$row = mysqli_fetch_array($res);
		return parseFolderData($row)
	}
}

/**
 * Récupère les données associés aux dossiers enfant d'un dossier
 */
function getSubFolders($parent_id) {
	global $database;

	$query_nb_messages = "SELECT COUNT(*) FROM message WHERE chat_id = f.chat_id";
	$query_nb_folders = "SELECT COUNT(*) FROM folder WHERE parent_id = f.id";
	$query_nb_files = "SELECT COUNT(*) FROM file WHERE location = f.id";
	
	$query_last_visit_chat = "SELECT last_update FROM chatUser WHERE chat_id = f.chat_id AND user_id = $user_id";
	$query_new_messages = "f.last_update > IFNULL(($query_last_visit_chat), 0)";

	$query = "SELECT f.id, f.name, f.description, f.parent_id, f.chat_id, f.last_update, ($query_new_messages) AS notif_new_messages, ($query_nb_messages) AS nb_messages, ($query_nb_folders) AS folders, ($query_nb_files) AS files FROM folder AS f WHERE f.parent_id = $id";

	$res = mysqli_query($database, $query);

	$folders = array();
	
	while ($row = mysqli_fetch_array($res)) {
		$folders[] = parseFolderData($row);
	}

	return $folders;
}

/**
 * Récupère les données associés aux fichiers enfant d'un dossier
 */
function getSubFiles($parent_id, $user_id, $last_visit) {
	global $database;

	$query_nb_messages = "SELECT COUNT(*) FROM message WHERE chat_id = f.chat_id";
	$query_liked = "SELECT * FROM file_liked WHERE file_id = f.id AND user_id = $user_id";
	
	$query_last_visit_chat = "SELECT last_update FROM chatUser WHERE chat_id = f.chat_id AND user_id = $user_id";
	$query_new_messages = "f.last_update > IFNULL(($query_last_visit_chat), 0)";

	$query_base = "SELECT f.id, f.name, f.description, f.last_update, f.nb_likes, ($query_new_messages) AS notif_new_messages, EXISTS($query_liked) AS liked, ($query_nb_messages) AS nb_messages FROM file AS f WHERE f.location = $id ORDER BY f.last_update";

	$res = mysql_query($database, $query);

	$files = array();

	while ($row = mysql_fetch_array($res)) {
		$files[] = parseFileData($row);
	}

	return $files;
}



/*function recupere_fichiers_dans_dossier($folderId){

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

function recupere_dossiers_dans_dossier($folderId,$chat,){

	// Retourne les information concernant les fichiers contenus dans le dossier donné

	global $database;
	$query = "SELECT id,name,description,(SELECT COUNT(*) FROM message WHERE chat_id=5 and deleted=0) AS nb_messages,
	(SELECT COUNT(*) FROM file WHERE location=44) AS files,
	(SELECT COUNT(*) FROM folder WHERE parent_id=44) AS folders 
	FROM folder WHERE id = 44";

	$resq = mysqli_query($database, $query);
	
	$folderarray=array();
	while($row = mysqli_fetch_assoc($resq)) {
		$folderarray[] = (int) $row["name"];// probablement erreur ici...
	}
	return $folderarray;
}*/

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
?>