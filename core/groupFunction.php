<?php

require_once("sql.php");

function create_group($nom, $description, $id_proprietaire) {
	global $database;
	$query ="INSERT INTO folder (name,group_id) VALUES ('$nom',$id_proprietaire)";
	mysqli_query($database,$query);
	$id=mysqli_insert_id($database);
	$query = "INSERT INTO `group` (name, description, root, id_creator) VALUES ('$nom', '$description', $id, $id_proprietaire)";//, $avatar )";
	mysqli_query($database, $query);
	$id_group=mysqli_insert_id($database);
	apply_group($id_group,$id_proprietaire);
	join_group($id_group,$id_proprietaire,$id_proprietaire);
	create_role($id_group,"Membre",1,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
	create_role_color($id_group,"Fondateur","dc3545",1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);
	$id=mysqli_insert_id($database);
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

function join_group($group_id,$user_id,$id_proprietaire){
	global $database;
	$query="UPDATE groupUser SET status='accepted' WHERE group_id=$group_id AND user_id=$user_id AND EXISTS(SELECT * FROM `group` WHERE id_creator=$id_proprietaire AND id=$group_id)";
	$res=mysqli_query($database,$query);
	return $res;
}

function leave_group($group_id,$user_id){
	global $database;
	$query = "DELETE FROM groupUser WHERE user_id=$user_id AND group_id=$group_id";
	$res=mysqli_query($database,$query);
	return $res;
}

function nb_members($group_id){
	global $database;
	$query = "SELECT * FROM groupUser WHERE group_id=$group_id";
	$res = mysqli_query($database,$query);
	$res=mysqli_num_rows($res);
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
			"name" => $row["name"],
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
?>