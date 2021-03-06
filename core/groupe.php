<?php

include_once("sql.php");

function ajoute_groupe($nom, $description, $id_proprietaire) {

	$sql = sqlconnect();
	$querry = "INSERT INTO `groupe` (`idGroupe`, `groupName`, `groupDescription`, `idCreator`) VALUES (NULL, '$nom', '$description', '$id_proprietaire')";
	$res = mysqli_query($sql, $querry);

	mysqli_close($sql);

}


function recupere_groupe_par_id($id) {

	// retourne les info du groupe passé en paramètre sous forme d'un tableau
	
	$sql = sqlconnect();
	$querry = "SELECT * FROM `groupe` WHERE `idGroupe` = $id";

	$res = mysqli_query($sql, $querry);

	mysqli_close($sql);

	return mysqli_fetch_assoc($res);
}



function recupere_groupe_par_id_proprietaire($id_proprietaire) {

	// retourne sous forme d'un tableau les info des groupe créé par le proriétaire passé en paramètre 

	$sql = sqlconnect();
	$querry = "SELECT * FROM `Groupe` WHERE `idCreator` = $id_proprietaire";
	$resq = mysqli_query($sql, $querry);

	mysqli_close($sql);

	$grouplist = array();

	while($row = mysqli_fetch_assoc($resq)) {
		$grouplist[] = $row;
	}
	return $grouplist;
}


function recupere_membre_par_groupe($id_groupe) {

	//retourne un tableau contenant toute les info des utilisateurs dans un groupe donné

	$sql = sqlconnect();
	$querry = "ELECT * FROM `GroupeJoin` WHERE `groupId` = $id_groupe AND `status` = 'accepted';";
	$resq = mysqli_query($sql, $querry);

	mysqli_close($sql);

	$grouplist = array();

	while($row = mysqli_fetch_assoc($resq)) {
		$grouplist[] = $row;
	}
	return $grouplist;


}


function supprime_groupe($id_proprietaire, $id_groupe) {

}


function ajoute_membre($id_utilisateur, $id_groupe) {

}


function valide_membre($id_utilisateur, $id_groupe) {

	//

	$sql = sqlconnect();
	$querry = "INSERT INTO `GroupeJoin` (`id`, `groupId`, `userId`, `status`) VALUES (NULL, '$id_groupe', '$id_utilisateur', 'accepted');";
	$resq = mysqli_query($sql, $querry);

	mysqli_close($sql);

}


function supprime_membre($id_utilisateur, $id_groupe) {

}