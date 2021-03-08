<?php

include_once("sql.php");

function creation_utilisateur($login, $email, $mot_de_passe)
{

	//créer un utilisateur 

	$sql = sqlconnect();

	$querry = "INSERT INTO `user` (`idUser`, `userName`, `passWord`, `email`) VALUES (NULL, '$login', '$mot_de_passe', '$email')";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);
}

function connecte_utilisateur($login, $mot_de_passe)
{

	// retourne true si le combo user + mot de passe existe

	$sql = sqlconnect();

	$querry = "SELECT * FROM `user` WHERE `userName` = '$login' AND `passWord` = '$mot_de_passe'";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);

	if (mysqli_num_rows($res) == 1) {
		return true;
	}

	return false;
}

function recupere_nom_utilisateur($id)
{

	//retourne une chaine de caractère contenant le nom, retourne une chaine vide si l'id n'existe pas

	$sql = sqlconnect();

	$querry = "SELECT * FROM `user` WHERE `idUser` = $id";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);

	if (mysqli_num_rows($res) == 1) {
		return mysqli_fetch_assoc($res);
	}

	return "";
}

function modifie_information_utilisateur($id, $login, $email)
{

	$sql = sqlconnect();

	$querry = "UPDATE `user` SET `userName` = '$login', `email` = '$email' WHERE `user`.`idUser` = $id;";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);
}

function modifie_mot_de_passe_utilisateur($id, $ancien_mot_de_passe, $mot_de_passe)
{

	$sql = sqlconnect();

	$querry_get_password = "SELECT passWord FROM `user` WHERE `userName` = 'id_to_username($id)' AND `passWord` = '$ancien_mot_de_passe'";
	$res = mysqli_query($sql, $querry_get_password);

	if (mysqli_num_rows($res) == 1) {

		$querry_set_password = "UPDATE `user` SET `passWord` = '$mot_de_passe' WHERE `user`.`idUser` = $id;";
		mysqli_query($sql, $querry_set_password);
	}

	mysqli_close($sql);
}
