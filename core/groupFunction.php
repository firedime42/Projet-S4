<?php

include_once("sql.php");

function ajoute_group($nom, $description, $id_proprietaire,$avatar) {

	$sql = sqlconnect();
	$querry = "INSERT INTO group (name, description, id_creator, avatar) VALUES ('$nom', '$description', $id_proprietaire, $avatar )";
	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);
	return $res;
}

function recupere_id_group_par_proprietaire($id_proprietaire) {

}


function recupere_id_group_par_membre($id_utilisateur) {

}


function supprime_group($id_proprietaire, $id_group) {

}


function ajoute_membre($id_utilisateur, $id_group) {

}


function valide_membre($id_utilisateur, $id_proprietaire, $id_group) {

}


function supprime_membre($id_utilisateur, $id_group) {

}

function recup_group_id($id) {

    // retourne les info du group passé en paramètre sous forme d'un tableau
    
    $sql = sqlconnect();
    $querry = "SELECT * FROM `group` WHERE id = $id";
    $res = mysqli_query($sql, $querry);
	$group_data=mysqli_fetch_assoc($res);
    mysqli_close($sql);
    return $group_data;
}

function recup_group_nom($nom) {

    // retourne les info du group passé en paramètre sous forme d'un tableau
    
    $sql = sqlconnect();
    $querry = "SELECT * FROM `group` WHERE name = '$nom'";

    $resq = mysqli_query($sql, $querry);
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
	
		$querry = "SELECT * FROM `group` WHERE name LIKE '%$needle%' OR description LIKE '%$needle%' LIMIT $nb_element_page OFFSET $offset";
		$resq = mysqli_query($sql, $querry);
		mysqli_close($sql);
		
			while($row = mysqli_fetch_assoc($resq)) {
				$grouplist[] = $row;
			}
		return $grouplist;
	} 

	function nb_group(){

		$sql = sqlconnect();
		$querry = "SELECT COUNT(*) FROM group";
		$resq = mysqli_query($sql, $querry);
		mysqli_close($sql);
		return mysqli_fetch_assoc($resq)["COUNT"];
		
	}

	function recup_status_by_user_and_group($id_user, $id_group){

		$sql = sqlconnect();
		$querry = "SELECT * FROM groupUser WHERE user_id = $id_user AND group_id = $id_group ";
	
		$resq = mysqli_query($sql, $querry);
		$res = "left";
		mysqli_close($sql);
	
		if (mysqli_num_rows($resq) > 0) {
			$res = mysqli_fetch_assoc($resq);
		}
	
		return $res;
	
	}

function recup_id_dossier_racine($id_group){

	$sql = sqlconnect();
    $querry = "SELECT id FROM folder WHERE group_id = $id_group";
    $resq = mysqli_query($sql, $querry);

    mysqli_close($sql);

    return mysqli_fetch_assoc($resq)["id"];
    

}
?>