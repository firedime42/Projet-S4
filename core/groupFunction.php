<?php

include_once("sql.php");

function create_group($nom, $description, $id_proprietaire) {

	$sql = sqlconnect();
	$query ="INSERT INTO folder (name,group_id) VALUES ('$nom',$id_proprietaire)";
	mysqli_query($sql,$query);
	$id=mysqli_insert_id($sql);
	$query = "INSERT INTO `group` (name, description, root, id_creator) VALUES ('$nom', '$description', $id, $id_proprietaire)";//, $avatar )";
	mysqli_query($sql, $query);
	$id=mysqli_insert_id($sql);
	mysqli_close($sql);
	join_group($id,$id_proprietaire);
	return $id;
}

function recupere_id_group_par_proprietaire($id_proprietaire) {

}


function recupere_id_group_par_membre($id_utilisateur) {

}


function supprime_group($id_proprietaire, $id_group) {

}

function valide_membre($id_utilisateur, $id_proprietaire, $id_group) {

}


function supprime_membre($id_utilisateur, $id_group) {

}

function recup_group_id($id) {

    // retourne les info du group passé en paramètre sous forme d'un tableau
    
    $sql = sqlconnect();
    $query = "SELECT * FROM `group` WHERE id = $id";
    $res = mysqli_query($sql, $query);
	$group_data=mysqli_fetch_assoc($res);
    mysqli_close($sql);
    return $group_data;
}

function recup_group_nom($nom) {

    // retourne les info du group passé en paramètre sous forme d'un tableau
    
    $sql = sqlconnect();
    $query = "SELECT * FROM `group` WHERE name = '$nom'";

    $resq = mysqli_query($sql, $query);
    mysqli_close($sql);

    $grouplist = array();
    while($row = mysqli_fetch_assoc($resq)) {
        $grouplist[] = $row;
    }
    return $grouplist;
}
	function recherche_par_nom_ou_description($needle, $page, $nb_element_page){
		$sql = sqlconnect();
		$offset = $nb_element_page * $page;
	
		$query = "SELECT * FROM `group` WHERE name LIKE '%$needle%' OR description LIKE '%$needle%' LIMIT $nb_element_page OFFSET $offset";
		$resq = mysqli_query($sql, $query);
		mysqli_close($sql);
		$grouplist=array();
			while($row = mysqli_fetch_assoc($resq)) {
				$grouplist[] = $row;
			}
		return $grouplist;
	} 

	function nb_group(){

		$sql = sqlconnect();
		$query = "SELECT COUNT(*) FROM group";
		$resq = mysqli_query($sql, $query);
		mysqli_close($sql);
		return mysqli_fetch_assoc($resq)["COUNT"];
		
	}

	function recup_status_by_user_and_group($id_user, $id_group){

		$sql = sqlconnect();
		$query = "SELECT status FROM groupUser WHERE user_id = $id_user AND group_id = $id_group ";
	
		$resq = mysqli_query($sql, $query);
		mysqli_close($sql);
		$res = mysqli_fetch_assoc($resq)["status"];
		if($res==NULL) $res="left";
		return $res;
		
	}

function recup_id_dossier_racine($id_group){

	$sql = sqlconnect();
    $query = "SELECT id FROM folder WHERE group_id = $id_group";
    $resq = mysqli_query($sql, $query);

    mysqli_close($sql);

    return mysqli_fetch_assoc($resq)["id"];
    

}

function join_group($id_group,$id_user){
	$sql=sqlconnect();
	$query = "INSERT INTO groupUser SET group_id = $id_group,user_id=$id_user,status='accepted'";
	$res=mysqli_query($sql,$query);
	mysqli_close($sql);
	return $res;
}

function recup_groups_since ($id_user,$time){
	$sql = sqlconnect();
		$query = "SELECT g.id,g.name,g.last_update,g.description FROM `group` g JOIN groupUser gu ON g.id=gu.group_id WHERE gu.user_id=$id_user AND g.last_update>$time";
		$resq = mysqli_query($sql, $query);
		mysqli_close($sql);
		$grouplist=array();
			while($row = mysqli_fetch_assoc($resq)) {
				$grouplist[] = $row;
			}
		return $grouplist;
}
?>