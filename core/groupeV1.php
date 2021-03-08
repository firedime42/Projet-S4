<?php

include_once("sql.php");

function ajoute_groupe($nom, $description, $id_proprietaire) {

	$sql = sqlconnect();
	$querry = "INSERT INTO `groupe` (`idGroupe`, `groupName`, `groupDescription`, `idCreator`) VALUES (NULL, '$nom', '$description', '$id_proprietaire')";
	$res = mysqli_query($sql, $querry);

	mysqli_close($sql);

	return $res;
}


function cherche_groupe_id($id) {
	$querry = "SELECT * FROM `groupe` WHERE `idGroupe` = $id";
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