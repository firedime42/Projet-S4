<?php

require_once("sql.php");

function create_group($nom, $description, $id_proprietaire) {
	global $database;
	$query = "INSERT INTO `group` (name, description, id_creator) VALUES ('$nom', '$description', $id_proprietaire)";//, $avatar )";
	mysqli_query($database, $query);
	$id_group=mysqli_insert_id($database);
	create_folder($nom, $id_group);
	$id_folder=mysqli_insert_id($database);
	create_role($id_group,"Membre",1,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
	$id=mysqli_insert_id($database);
	$query="UPDATE `group` SET default_role=$id,root=$id_folder WHERE id=$id_group";
	mysqli_query($database,$query);
	create_role_color($id_group,"Fondateur","dc3545",1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);
	$id=mysqli_insert_id($database);
	apply_group($id_group,$id_proprietaire);
	join_group($id_group,$id_proprietaire,$id_proprietaire);
	add_role($id_group,$id_proprietaire,$id);
	return $id_group;
}

function recup_group_id($id) {
    // retourne les info du group passé en paramètre sous forme d'un tableau
    global $database;
    $query = "SELECT * FROM `group` WHERE id = $id";
    $res = mysqli_query($database, $query);
	$group_data=mysqli_fetch_assoc($res);
    return $group_data;
}

function recup_group($id,$user) {
    // retourne les info du group passé en paramètre sous forme d'un tableau
    global $database;
    $query = "SELECT g.*,r.*, g.id AS id_group,g.name AS group_name FROM `group` g JOIN groupUser gu ON gu.group_id=g.id JOIN role r ON r.id=gu.role_id WHERE g.id = $id AND gu.user_id=$user";
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
		$query = "SELECT g.id,g.name,g.last_update,g.description,g.id_creator FROM `group` g JOIN groupUser gu ON g.id=gu.group_id WHERE gu.user_id=$id_user AND g.last_update>$time";
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
	$res=mysqli_num_rows($res);
	return $res;
}
function modif_nb_messages($group_id,$val){
	global $database;
	$query = "UPDATE `group` SET nb_messages=nb_messages+$val WHERE id=$group_id";
	$res = mysqli_query($database,$query);
	$res=mysqli_num_rows($res);
	return $res;
}
?>