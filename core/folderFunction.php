<?php

require_once("sql.php");
require_once("fileFunction.php");
require_once("messageFunction.php");

/**
 * créé un dossier et l'attache à son parent s'il est definie
 * @param {int} $group_id : l'identifiant du groupe auquel appartient le dossier (son existance à été vérifié au préalable)
 * @param {string} $name : nom du dossiers
 * @param {string} $description : description du dossier
 * @param {int|null} $parent_id : identifiant du parent
 * 
 * @return {int} l'identifiant du dossier parent ou -1 si la requete a échoué
 */
function create_folder($group_id, $name, $description="", $parent_id = null) {
    global $database;

    # format des données
    $str_name = mysqli_real_escape_string($database, $name);
    $str_description = mysqli_real_escape_string($database, $description);

    # creation du chat associé au dossier
	$id_chat = ajoute_chat();
    
    # creation du dossier en liant au chat
    $root = !isset($parent_id);
    $anchor_right = $root ? 1 : 0;
    $str_parent_id = $root ? "NULL" : "$parent_id";

    $time = now();
	
    $query = "INSERT INTO folder (name, group_id, description, parent_id, chat_id, last_update, anchor_left, anchor_right) VALUES ('$str_name',$group_id,'$str_description', $str_parent_id, $id_chat, $time, 0, $anchor_right)";
	$success = mysqli_query($database, $query);

    $folder_id = mysqli_insert_id($database);

    # attache le dossier à son dossier parent
    if ($success && !$root) {
        attach_folder($folder_id, $parent_id);
    }
    
	return $success ? $folder_id : -1;
}



/**
 * on relie le dossier à son dossier parent
 * les deux dossier doivent au préalable appartenir au même groupe
 */
function attach_folder($folder_id, $parent_id) {
    global $database;

    $time = now();

    # on recupère l'ancre à gauche du parent
    $query_parent_anchor = "SELECT anchor_left, group_id FROM folder WHERE id = $parent_id"; // on peut peut-être modifier cette requete pour s'assurer que les deux dossier cohexiste au seins du même groupe
	$res = mysqli_query($database, $query_parent_anchor);
    $parent = mysqli_fetch_assoc($res);
    $parent_anchor_left = $parent['anchor_left'];
    $parent_group_id = $parent['group_id'];
    
    # mise à jour des valeur d'ancrage des parents et des voisins du dossier
    $update_others = "UPDATE folder f SET f.anchor_left = f.anchor_left + 2 * (f.anchor_left > $parent_anchor_left), f.anchor_right = f.anchor_right + 2 WHERE f.anchor_right > $parent_anchor_left AND f.group_id = $parent_group_id";
    mysqli_query($database, $update_others);

    # mise à jour des valeur d'ancrage du dossier
    $update_folder = "UPDATE folder f SET f.anchor_left = $parent_anchor_left + 1, f.anchor_right = $parent_anchor_left + 2 WHERE f.id = $folder_id";
    mysqli_query($database, $update_folder);
}


/**
 * marque l'utilisateur comme ayant visité le dossier
 */
function visitFolder($folder_id, $user_id) {
    global $database;

    # recherche si la ligne existe
    $res = mysqli_query($database, "SELECT * FROM folderUser WHERE folder_id=$folder_id AND user_id=$user_id");
    $exists = mysqli_num_rows($res) >= 1;

    # recuperation de l'instant
    $time = now();

    # requetes
    $query_update = "UPDATE folderUser SET last_update = $time WHERE folder_id=$folder_id AND user_id=$user_id";
    $query_insert = "INSERT INTO folderUser (folder_id,user_id,last_update) VALUES ($folder_id,$user_id,$time)";

	# envoi de la requete
    return mysqli_query($database, $exists ? $query_update : $query_insert);
}

function getFolderPath($folder_id) {
    global $database;

    $folder_parents = "SELECT p.id, p.name FROM folder p JOIN folder f ON p.group_id = f.group_id AND p.anchor_left < f.anchor_left AND f.anchor_right < p.anchor_right WHERE f.id = $folder_id ORDER BY p.anchor_left";

    $res = mysqli_query($database, $folder_parents);
    $path = array();

    while ($f = mysqli_fetch_array($res)) {
        $path[] = [
            "id" => (int) $f["id"],
            "nom" => $f["name"]
        ];
    }

    return $path;
}

/**
 * récupère les informations du dossier
 * @param folder_id : identifiant du dossier
 * @param user_id : identifiant de l'utilisateur
 * @return array/null : les données associées au dossier ou null 
 */
function getFolder($folder_id, $user_id) {
    global $database;

    # recuperation notification messages
	$query_last_visit_chat = "SELECT last_update FROM chatUser WHERE chat_id = f.chat_id AND user_id = $user_id";
	$query_new_messages = "c.last_update > IFNULL(($query_last_visit_chat), 0)";

    # requete recuperation des données du dossier
    $query_folder =  "SELECT f.id, f.group_id, f.name, f.description, f.chat_id, f.last_update, COUNT(DISTINCT m.id) AS nb_messages, ($query_new_messages) AS notif_messages FROM folder f JOIN message m ON f.chat_id=m.chat_id JOIN chat c ON c.id=f.chat_id WHERE f.id = $folder_id AND m.deleted=0";

    $result = mysqli_query($database, $query_folder);
    $folder = null;

    if ($result && mysqli_num_rows($result) > 0) {
	    $folder_data = mysqli_fetch_assoc($result);
        $folder = [
            "id" => (int) $folder_data["id"],
            "name" => $folder_data["name"],
            "description" => $folder_data["description"],
            "group_id" => (int) $folder_data["group_id"],
            "chat_id" => (int) $folder_data["chat_id"],
            "nb_messages" => (int) $folder_data["nb_messages"],
            "notif_messages" => (bool) $folder_data["notif_messages"],
            "last_update" => (int) $folder_data["last_update"]
        ];
    }

    return $folder;
}

/**
 * récupère les dossiers enfants du dossier
 * @param folder_id : identifiant du dossier
 * @param user_id : identifiant de l'utilisateur
 * @return array : les données associées aux sous-dossiers
 */
function getSubFolders($folder_id, $user_id) {
	global $database;

	$query_nb_messages = "SELECT COUNT(*) FROM message WHERE chat_id = f.chat_id";
	$query_nb_folders = "SELECT COUNT(*) FROM folder WHERE parent_id = f.id";
	$query_nb_files = "SELECT COUNT(*) FROM file WHERE location = f.id";
	
	$query_last_visit_chat = "SELECT last_update FROM chatUser WHERE chat_id = f.chat_id AND user_id = $user_id";
	$query_new_messages = "c.last_update > IFNULL(($query_last_visit_chat), 0)";

	$query = "SELECT f.id, f.name, f.description, f.chat_id, f.last_update, ($query_new_messages) AS notif_messages, ($query_nb_messages) AS nb_messages, ($query_nb_folders) AS folders, ($query_nb_files) AS files FROM folder f JOIN chat c ON c.id=f.chat_id WHERE f.parent_id = $folder_id";

	$res = mysqli_query($database, $query);

	$folders = array();
	
	while ($row = mysqli_fetch_array($res)) {
		$folders[] = [
            "id" => (int) $row["id"],
            "nom" => $row["name"],
            "description" => $row["description"],
            "chat_id" => (int) $row["chat_id"],
            "nb_messages" => (int) $row['nb_messages'],
            "notif_messages" => (bool) $row['notif_messages'],
            "folders" => (int) $row['folders'],
            "files" => (int) $row['files'],
            "last_update" => (int) $row["last_update"]
        ];
	}

	return $folders;
}

/**
 * recupère les fichiers enfants du dossier
 * ne converti pas les types
 */
function getSubFiles($folder_id, $user_id) {
	global $database;

	$query_nb_messages = "SELECT COUNT(*) FROM message WHERE chat_id = f.chat_id";
	$query_liked = "SELECT * FROM file_liked WHERE file_id = f.id AND user_id = $user_id";
	
	$query_last_visit_chat = "SELECT last_update FROM chatUser WHERE chat_id = f.chat_id AND user_id = $user_id";
	$query_new_messages = "c.last_update > IFNULL(($query_last_visit_chat), 0)";
    $query_is_new = "SELECT * FROM folderUser WHERE f.id=folder_id AND user_id = $user_id";

	$query = "SELECT f.id, f.name, f.chat_id, f.description, f.last_update, f.nb_likes, f.creator_id, u.username AS creator_name, ($query_new_messages) AS notif_messages, EXISTS($query_liked) AS liked, ($query_nb_messages) AS nb_comments FROM file f JOIN chat c ON c.id=f.chat_id JOIN user u ON u.id = f.creator_id WHERE f.location = $folder_id ORDER BY f.last_update";

	$res = mysqli_query($database, $query);

	$files = array();

	while ($row = mysqli_fetch_array($res)) {
		$files[] = [
            "id" => (int) $row["id"],
            "nom" => $row["name"],
            "description" => $row["description"],
            "chat_id" => (int) $row["chat_id"],
            "nb_comments" => (int) $row['nb_comments'],
            "notif_messages" => (bool) $row['notif_messages'],
            "liked" => (bool) $row['liked'],
            "nb_likes" => (int) $row['nb_likes'],
            "auteur" => [
                "id" => (int) $row["creator_id"],
                "name" => $row["creator_name"]
            ],
            "last_update" => (int) $row["last_update"]
        ];
	}

	return $files;
}

/**
 * renvoi la liste de tous les fichiers associés aux dossiers et aux sous dossiers
 */
function _getAllFiles($group_id, $anchor_left, $anchor_right) {
    global $database;
    $query = "SELECT f.id FROM file f JOIN folder dir ON f.location = dir.id WHERE dir.group_id = $group_id AND $anchor_left <= dir.anchor_left AND dir.anchor_right <= $anchor_right";
    $res = mysqli_query($database, $query);
    $files = array();
    if ($res) {
        while ($row = mysqli_fetch_array($res))
            $files[] = $row[0];
    }
    return $files;
}

/**
 * renvoi la liste de tous les chats associés aux dossiers et aux sous dossiers
 */
function _getAllChats($group_id, $anchor_left, $anchor_right) {
    global $database;
    $query = "SELECT dir.chat_id FROM folder dir WHERE dir.group_id = $group_id AND $anchor_left <= dir.anchor_left AND dir.anchor_right <= $anchor_right";
    $res = mysqli_query($database, $query);
    $chats = array();
    if ($res) {
        while ($row = mysqli_fetch_array($res))
            $chats[] = $row[0];
    }
    return $chats;
}

/**
 * Retourne des informations sur le dossier
 */
function getIdentFolder($folder_id) {
    global $database;
    $query = "SELECT id, group_id, parent_id, anchor_left, anchor_right FROM folder WHERE id = $folder_id";
    $res = mysqli_query($database, $query);
    return $res ? mysqli_fetch_assoc($res) : null;
}

/**
 * supprime un dossier ainsi que ses sous dossiers et dossiers associés
 */
function remove_folder($folder_id) {
    global $database;

    # recuperation de la partie gauche et droite ainsi que l'identifiant du groupe et du parent
    $folder = getIdentFolder($folder_id);
    
    $group_id = $folder['group_id'];
    $anchor_left = $folder['anchor_left'];
    $anchor_right = $folder['anchor_right'];
    $parent_id = $folder['parent_id'];

    # recuperation des enfants
    $files = _getAllFiles($group_id, $anchor_left, $anchor_right);
    $chats = _getAllChats($group_id, $anchor_left, $anchor_right);

    $nb_files = count($files);
    $nb_chats = count($chats);

    # suppressions des fichiers
    for ($i = 0; $i < $nb_files; $i++)
        supprime_file($files[$i]);

    # suppressions des dossiers et sous dossiers
    $query_remove_fold = "DELETE FROM folder WHERE group_id = $group_id AND $anchor_left <= anchor_left AND anchor_right <= $anchor_right";
    mysqli_query($database, $query_remove_fold);
    
    # suppressions des chats
    for ($i = 0; $i < $nb_chats; $i++)
        remove_chat($chats[$i]);

    # modification des dossiers parents
    $delta = $anchor_right - $anchor_left + 1;
    $query_dettach = "UPDATE folder SET anchor_left = anchor_left - (anchor_left > $anchor_right) * $delta, anchor_right = anchor_right - $delta WHERE group_id = $group_id AND anchor_right > $anchor_right";
    mysqli_query($database, $query_dettach);

    # update du parent
    update_folder($parent_id);

    return true;
}

/**
 * Met à jour le timestamp du dossier
 */
function update_folder($folder_id) {
    global $database;
    $time = now();
    $query = "UPDATE folder SET last_update = $time WHERE id = $folder_id";
    $res=mysqli_query($database, $query);
    return $res;
}

/**
 * Modifie les informations du dossier (le met à jour au passage)
 */
function update_folder_data($folder_id, $name, $descr) {
    global $database;
    $time = now();
    $query = "UPDATE folder SET name = '$str_name', description = '$str_descr', last_update = $time WHERE id = $folder_id";
    $res=mysqli_query($database, $query);
    return $res;
}