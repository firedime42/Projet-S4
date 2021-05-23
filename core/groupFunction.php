<?php

require_once("sql.php");

function create_group($nom, $description, $id_proprietaire) {
	global $database;

	# creation du groupe
	$query = "INSERT INTO `group` (name, description, id_creator) VALUES ('$nom', '$description', $id_proprietaire)";//, $avatar )";
	mysqli_query($database, $query);
	$id_group = mysqli_insert_id($database);

	# creation du dossier associé au groupe
	$id_folder = create_folder($id_group, $nom);
	
	# creation des roles de base du groupe
	create_role($id_group,"Membre",1,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
	$id_role_membre=mysqli_insert_id($database);
	create_role_color($id_group,"Fondateur","dc3545",1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);
	$id_role_fondateur=mysqli_insert_id($database);
	
	# ajout du role par défaut et du dossier racine
	$query="UPDATE `group` SET default_role=$id_role_membre,root=$id_folder WHERE id=$id_group";
	mysqli_query($database,$query);

	# ajout de l'utilisateur au groupe
	apply_group($id_group, $id_proprietaire);                  // candidature
	join_group($id_group, $id_proprietaire);                   // accepte l'utilisateur
	add_role($id_group, $id_proprietaire, $id_role_fondateur); // attribution du role fondateur

	return $id_group;
}

function recup_group_id($id) {
    // retourne les info du group passé en paramètre sous forme d'un tableau
    global $database;
    $query = "SELECT *,(SELECT username FROM user WHERE id=id_creator) AS creator_name  FROM `group` WHERE id = $id";
    $res = mysqli_query($database, $query);
	$group_data=mysqli_fetch_assoc($res);
    return $group_data;
}

function recup_group($id, $user) {
    // retourne les info du group passé en paramètre sous forme d'un tableau
    global $database;
    $query = "SELECT g.*, r.*, g.id AS id_group, g.name AS group_name, u.username AS creator_name FROM `group` g JOIN groupUser gu ON gu.group_id = g.id JOIN role r ON r.id = gu.role_id JOIN user u ON u.id = g.id_creator WHERE g.id = $id AND gu.user_id = $user";
	$res = mysqli_query($database, $query);
	$group_data=mysqli_fetch_assoc($res);
    return $group_data;
}

function recup_group_msg($id){
	global $database;
	$query="SELECT fa.group_id AS g1, f.group_id AS g2 FROM message m LEFT JOIN file fi ON fi.chat_id=m.chat_id LEFT JOIN folder fa ON fa.id=fi.location LEFT JOIN folder f ON f.chat_id=m.chat_id WHERE m.id=$id";
	$resq=mysqli_query($database,$query);
	$res=mysqli_fetch_assoc($resq);
	$group=null;
	if(isset($res["g1"]))
		$group=$res["g1"];
	elseif(isset($res["g2"]))
		$group=$res["g2"];
	return $group;
}

function recup_group_chat($id){
	global $database;
	$query="SELECT fa.group_id AS g1, f.group_id AS g2 FROM chat c LEFT JOIN file fi ON fi.chat_id=c.id LEFT JOIN folder fa ON fa.id=fi.location LEFT JOIN folder f ON f.chat_id=c.id WHERE c.id=$id";
	$resq=mysqli_query($database,$query);
	$res=mysqli_fetch_assoc($resq);
	$group=null;
	if(isset($res["g1"]))
		$group=$res["g1"];
	elseif(isset($res["g2"]))
		$group=$res["g2"];
	return $group;
}

function recup_group_folder($id){
	global $database;
	$query="SELECT group_id FROM folder WHERE id=$id";
	$resq=mysqli_query($database,$query);
	$res=mysqli_fetch_assoc($resq);
	return $res["group_id"];
}

function recup_group_file($id){
	global $database;
	$query="SELECT group_id FROM file fi JOIN folder f ON f.id=fi.location WHERE fi.id=$id";
	$resq=mysqli_query($database,$query);
	$res=mysqli_fetch_assoc($resq);
	return $res["group_id"];
}

function recherche_par_nom_ou_description($needle, $page, $nb_element_page){
	global $database;
	$offset = $nb_element_page * $page;
	$query = "SELECT * FROM `group` WHERE name LIKE '%$needle%' OR description LIKE '%$needle%' LIMIT $nb_element_page OFFSET $offset";
	$resq = mysqli_query($database, $query);
	$grouplist=array();
			while($row = mysqli_fetch_assoc($resq)) {
			$grouplist[] = $row;
		}
	return $grouplist;
} 

function nb_group(){
	global $database;
	$query = "SELECT COUNT(*) FROM group";
	$resq = mysqli_query($database, $query);
	return mysqli_fetch_assoc($resq)["COUNT"];
		
}

function recup_status_by_user_and_group($id_user, $id_group){

	global $database;
	$query = "SELECT status FROM groupUser WHERE user_id = $id_user AND group_id = $id_group ";
	$resq = mysqli_query($database, $query);
	$res = mysqli_fetch_assoc($resq)["status"];
	if($res==NULL)
		$res="left";
	return $res;	
}

function recup_groups_since ($id_user,$time){
	global $database;
		$query = "SELECT g.id,g.name,g.last_update,g.description,g.id_creator FROM `group` g JOIN groupUser gu 
		ON g.id=gu.group_id WHERE gu.user_id=$id_user AND g.last_update>$time";
		$resq = mysqli_query($database, $query);
		$grouplist=array();
		while($row = mysqli_fetch_assoc($resq)) {
			$grouplist[] = $row;
		}
		return $grouplist;
}

function apply_group($id_group,$id_user){
	global $database;
	$query = "INSERT INTO groupUser SET group_id = $id_group,user_id=$id_user,status='pending'";
	$res=mysqli_query($database,$query);
	return $res;
}

function join_group($group_id,$user_id){
	global $database;
	$query="UPDATE groupUser SET status='accepted',role_id = (SELECT default_role FROM `group` WHERE id=$group_id) WHERE group_id=$group_id AND user_id=$user_id";
	$res=mysqli_query($database,$query);
	return $res;
}

function leave_group($group_id,$user_id){
	global $database;
	$query = "DELETE FROM groupUser WHERE user_id=$user_id AND group_id=$group_id";
	$res=mysqli_query($database,$query);
	return $res;
}

function modif_groupe($id,$nom,$description){
	global $database;
	$query="UPDATE `group` SET name = '$nom',description='$description' WHERE id=$id";
	$res=mysqli_query($database,$query);
	return $res;
}

function modif_nom_group($id,$nom){
	global $database;
	$query=
	$query="UPDATE `group` SET name = '$nom' WHERE id=$id";
	$res=mysqli_query($database,$query);
	$query="UPDATE folder SET name='$nom' WHERE group_id=$id AND parent_id IS NULL";
	mysqli_query($database,$query);
	return $res;
}

function modif_description_group($id,$description){
	global $database;
	$query="UPDATE `group` SET description='$description' WHERE id=$id";
	$res=mysqli_query($database,$query);
	$query="UPDATE folder SET description='$description' WHERE group_id=$id IS NULL";
	mysqli_query($database,$query);
	return $res;
}

function recup_membres($group){
	global $database;
	$query="SELECT g.user_id,u.username,g.role_id FROM groupUser g JOIN user u ON u.id=g.user_id WHERE g.group_id=$group AND g.status='accepted'";
	$res=mysqli_query($database,$query);
	$list_membres = array();
	while($row=mysqli_fetch_assoc($res)){
		$list_membres[]=array(
			"id" => $row["user_id"],
			"name" => $row["username"],
			"role_id" => $row["role_id"]
		);
	}
	return $list_membres;
}

function recup_applications($group){
	global $database;
	$query="SELECT g.user_id,u.username FROM groupUser g JOIN user u ON u.id=g.user_id WHERE g.group_id=$group AND g.status='pending'";
	$res=mysqli_query($database,$query);
	$list_applications = array();
	while($row=mysqli_fetch_assoc($res)){
		$list_applications[]=array(
			"id" => $row["user_id"],
			"nom" => $row["username"]
		);
	}
	return $list_applications;
}
function recup_dashboard($group){
	global $database;

	$query="SELECT g.nb_messages AS nb_messages_overall,g.nb_membres AS nb_members_overall, g.nb_folders AS nb_folders_overall ,g.nb_files AS nb_files_overall, COUNT(DISTINCT m.id) AS nb_messages_folder, COUNT(DISTINCT mi.id) AS nb_messages_file, COUNT(DISTINCT fi.name) AS nb_files, COUNT(DISTINCT f.id) AS nb_folders, COUNT(DISTINCT gu.id) AS nb_members FROM `group`g JOIN groupUser gu ON g.id=gu.group_id JOIN folder f ON f.group_id=g.id LEFT JOIN file fi ON fi.location=f.id LEFT JOIN message m ON m.chat_id=f.chat_id LEFT JOIN message mi ON mi.chat_id=fi.chat_id WHERE g.id=45 AND (mi.deleted!=1 OR mi.deleted IS NULL) AND (m.deleted!=1 OR mi.deleted IS NULL)";
	$resq=mysqli_query($database,$query);
	$res=mysqli_fetch_assoc($resq);
	return $res;
};

function recup_info($group){
	global $database;
	$query="SELECT (SELECT COUNT(*) FROM groupUser WHERE group_id=$group) AS nb_members, 
	(SELECT SUM(f.size) FROM file f JOIN folder fo ON f.location=fo.id WHERE fo.group_id=$group) AS total_space,
	(SELECT COUNT(f.size) FROM file f JOIN folder fo ON f.location=fo.id WHERE fo.group_id=$group) AS nb_files, 
	COUNT(*) AS nb_messages FROM (SELECT message FROM message WHERE chat_id IN 
	( SELECT fo.chat_id FROM folder fo LEFT JOIN file f ON f.location=fo.id WHERE fo.group_id=$group) 
	UNION 
	SELECT message FROM message WHERE chat_id IN 
	(SELECT f.chat_id FROM folder fo LEFT JOIN file f ON f.location=fo.id WHERE fo.group_id=$group)) AS test";
	$res=mysqli_query($database,$query);
	return mysqli_fetch_array($res);
};

function recup_repart($group){
	global $database;
	$query="";
	$files=array();
	$res=mysqli_query($database,$query);
	while($row=mysqli_fetch_assoc($res)){
		$files[]=$row;
	}
	return $files;
}

function recup_most_liked($group){
	global $database;
	$query="SELECT COUNT(fl.user_id) AS nb_likes ,f.name FROM file f JOIN folder fo ON fo.id=f.location LEFT JOIN file_liked fl ON fl.file_id=f.id WHERE fo.group_id=$group GROUP BY fl.file_id ORDER BY nb_likes";
	$files=array();
	$res=mysqli_query($database,$query);
	while($row=mysqli_fetch_assoc($res)){
		$files[]=$row;
	}
	return $files;
}

function recup_most_commented($group){
	global $database;
	$query="SELECT COUNT(m.id) AS nb_messages,f.name FROM file f JOIN folder fo ON fo.id=f.location LEFT JOIN message m ON m.chat_id=f.chat_id WHERE fo.group_id=$group GROUP BY m.chat_id ORDER BY nb_messages";
	$files=array();
	$res=mysqli_query($database,$query);
	while($row=mysqli_fetch_assoc($res)){
		$files[]=$row;
	}
	return $files;
}

function modif_nb_members($group_id,$val){
	global $database;
	$query = "UPDATE `group` SET nb_membres=nb_membres+$val WHERE id=$group_id";
	$res = mysqli_query($database,$query);
	$res=mysqli_num_rows($res);
	return $res;
}
function modif_nb_files($group_id,$val){
	global $database;
	$query = "UPDATE `group` SET nb_files=nb_files+$val WHERE id=$group_id";
	$res = mysqli_query($database,$query);
	return $res;
}
function modif_nb_messages($group_id,$val){
	global $database;
	$query = "UPDATE `group` SET nb_messages=nb_messages+$val WHERE id=$group_id";
	$res = mysqli_query($database,$query);
	return $res;
}
function notifs($group,$user){
	global $database;
	$query="SELECT COUNT(*) FROM folderUser fu JOIN folder f ON f.id=fu.folder_id WHERE fu.last_update>=f.last_update AND fu.user_id = $user AND f.group_id=$group";
	$tmp=mysqli_query($database,$query);
	$res["notif_folder"]=mysqli_fetch_array($tmp)[0];
	$query="SELECT COUNT(*) AS notif_message FROM chatUser cu JOIN chat c ON c.id=cu.chat_id WHERE cu.last_update>=c.last_update AND cu.user_id =$user";
	$tmp=mysqli_query($database,$query);
	$res["notif_message"]=mysqli_fetch_array($tmp)[0];
	return $res;
}
function est_dans_groupe($group,$user){
	global $database;
	$query="SELECT * FROM groupUser WHERE group_id=$group AND user_id=$user AND status='accepted'";
	$res=mysqli_query($database,$query);
	return mysqli_num_rows($res)>0;
}

function recup_date_messages($group){
	global $database;
	$query="SELECT m.last_update FROM message m JOIN file f ON f.chat_id = m.chat_id JOIN folder fo ON fo.id = f.location WHERE fo.group_id = $group UNION SELECT m.last_update FROM message m JOIN folder f ON f.chat_id = m.chat_id WHERE f.group_id = $group";
	$res=mysqli_query($database,$query);
	return mysqli_fetch_array($res);
}

function recup_membres_dashboard($group){

}

function recup_file_dashboard($group){
	
}