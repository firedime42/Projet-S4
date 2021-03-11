<?php

include_once("sql.php");

function ajoute_groupe($nom, $description, $id_proprietaire) {

	$sql = sqlconnect();
	$querry = "INSERT INTO `groupe` (`idGroupe`, `groupName`, `groupDescription`, `idCreator`) VALUES (NULL, '$nom', '$description', '$id_proprietaire')";
	$res = mysqli_query($sql, $querry);

	mysqli_close($sql);

	return $res;
}

function recupere_id_groupe_par_proprietaire($id_proprietaire) {

}


function recupere_id_groupe_par_membre($id_utilisateur) {

}


function supprime_groupe($id_proprietaire, $id_groupe) {

}


function ajoute_membre($id_utilisateur, $id_groupe) {

}


function valide_membre($id_utilisateur, $id_proprietaire, $id_groupe) {

}


function supprime_membre($id_utilisateur, $id_groupe) {

}

function recup_group_id($id) {

    // retourne les info du groupe passé en paramètre sous forme d'un tableau
    
    $sql = sqlconnect();
    $querry = "SELECT * FROM `groupe` WHERE `idGroupe` = $id";

    $res = mysqli_query($sql, $querry);

    mysqli_close($sql);
    $table_res = array();
    $table_res[] = mysqli_fetch_assoc($res);

    return $table_res;
}

function recup_group_nom($nom) {

    // retourne les info du groupe passé en paramètre sous forme d'un tableau
    
    $sql = sqlconnect();
    $querry = "SELECT * FROM `groupe` WHERE `groupName` = '$nom'";

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
	
		$querry = "SELECT * FROM `Groupe` WHERE `groupName` LIKE '%$needle%' OR `groupDescription` LIKE '%$needle%' LIMIT $nb_element_page OFFSET $offset;";
		$resq = mysqli_query($sql, $querry);
		mysqli_close($sql);
		
		$grouplist = array();
		if($resq!=false){
			while($row = mysqli_fetch_assoc($resq)) {
				$grouplist[] = $row;
			}
		}
		return $grouplist;
	} 

	function nb_groupe(){

		$sql = sqlconnect();
		$querry = "SELECT COUNT(*) FROM `Groupe`";
		$resq = mysqli_query($sql, $querry);
	
		mysqli_close($sql);
	
		return mysqli_fetch_assoc($resq)["COUNT"];
		
	}

	function recup_status_by_user_and_group($id_user, $id_groupe){

		$sql = sqlconnect();
		$querry = "SELECT * FROM `GroupeJoin` WHERE `groupId` = 2 AND `userId` = 2";
	
		$resq = mysqli_query($sql, $querry);
		$res = "left";
		mysqli_close($sql);
	
		if (mysqli_num_rows($resq) > 0) {
			$res = mysqli_fetch_assoc($resq);
		}
	
		return $res;
	
	}

function recup_id_dossier_racine($id_groupe){



    $sql = sqlconnect();
    $querry = "SELECT * FROM `Folder` WHERE `groupId` = 2 AND `rootFoldrer` = b'1'";
    $resq = mysqli_query($sql, $querry);

    mysqli_close($sql);

    return mysqli_fetch_assoc($resq)["id"];
    

}
?>