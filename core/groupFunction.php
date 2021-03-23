<?php

require_once("sql.php");

function create_group($nom, $description, $id_proprietaire) {
	global $database;
	$query ="INSERT INTO folder (name,group_id) VALUES ('$nom',$id_proprietaire)";
	mysqli_query($database,$query);
	$id=mysqli_insert_id($database);
	$query = "INSERT INTO `group` (name, description, root, id_creator) VALUES ('$nom', '$description', $id, $id_proprietaire)";//, $avatar )";
	mysqli_query($database, $query);
	$id=mysqli_insert_id($database);
	create_role($id,"Membre");
	create_role_color($id,"Fondateur","dc3545");
	apply_group($id,$id_proprietaire);
	join_group($id,$id_proprietaire);
	return $id;
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
		$query = "SELECT g.id,g.name,g.last_update,g.description FROM `group` g JOIN groupUser gu ON g.id=gu.group_id WHERE gu.user_id=$id_user AND g.last_update>$time";
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
	$query="UPDATE groupUser SET gu.status='accepted' WHERE gu.group_id=$group_id AND gu.user_id=$user_id";
	$res=mysqli_query($database,$query);
	return $res;
}

function quit_group($group_id,$user_id){
	global $database;
	$query = "DELETE FROM groupUser WHERE user_id=$user_id AND group_id=$group_id";
	$res=mysqli_query($database,$query);
	return $res;
}

function nb_members($group_id){
	global $database;
	$query = "SELECT COUNT(user_id) FROM groupUser WHERE group_id=$group_id";
	$res = mysqli_query($database,$query);
	return $res;
}
?>