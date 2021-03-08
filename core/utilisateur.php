<?php

include_once("sql.php");
//TODO
function inscrit_utilisateur($login, $mot_de_passe, $email) {

	//créer un utilisateur 

	$sql = sqlconnect();

	$querry = "INSERT INTO `user` (`idUser`, `userName`, `passWord`, `email`) VALUES (NULL, '$login', '$mot_de_passe', '$email')";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);

}

function connecte_utilisateur_login($login, $mot_de_passe_hash,$timestamp) {

	// retourne true si le combo user + mot de passe existe

	$sql = sqlconnect();

	$querry = "SELECT * FROM `user` WHERE `userName` = '$login' AND `passWord` = '$mot_de_passe_hash'";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);

	if (mysqli_num_rows($res) == 1) {
		return true;
	}

	return false;
}

function recupere_nom_utilisateur($id) {

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

function modifie_information_utilisateur($id, $login, $email) {
	
	$sql = sqlconnect();

	$querry = "UPDATE `user` SET `userName` = '$login', `email` = '$email' WHERE `user`.`idUser` = $id;";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);

}

function modifie_mot_de_passe_utilisateur($id, $ancien_mot_de_passe, $mot_de_passe) {

	$sql = sqlconnect();

	$querry_get_password = "SELECT passWord FROM `user` WHERE `userName` = 'id_to_username($id)' AND `passWord` = '$ancien_mot_de_passe'";
	$res = mysqli_query($sql, $querry_get_password);

	if (mysqli_num_rows($res) == 1) {

		$querry_set_password = "UPDATE `user` SET `passWord` = '$mot_de_passe' WHERE `user`.`idUser` = $id;";
		mysqli_query($sql, $querry_set_password);

	}

	mysqli_close($sql);

}

function email_to_id($email){
	$sql = sqlconnect();

	$querry = "SELECT `id` FROM `user` WHERE `email` = '$email'";

	return $querry;
}  //renvoie l'email associé a l'id
function username_to_id($login){
	$sql = sqlconnect();

	$querry = "SELECT `id` FROM `user` WHERE `userName` = '$login'";

	return $querry;
}   //renvoie l'username associé a l'id

function id_to_email($id){
	$sql = sqlconnect();

	$querry = "SELECT `email` FROM `user` WHERE `id` = '$id'";

	return $querry;
}    //renvoie l'id associé a l'email
function id_to_username($id) {
	$sql = sqlconnect();

	$querry = "SELECT `userName` FROM `user` WHERE `id` = '$id'";

	return $querry;
}  //renvoie l'id associé au login

function connecte_utilisateur_email($email, $mot_de_passe) {
	$sql = sqlconnect();

	$querry = "SELECT * FROM `user` WHERE `mail` = '$email' AND `passWord` = '$mot_de_passe'";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);

	return (mysqli_num_rows($res) == 1);
}   //renvoie un bool : true si la combinaison mot de passe/login est juste
function connecte_utilisateur_username($login, $mot_de_passe){
	// retourne true si le combo user + mot de passe existe

	$sql = sqlconnect();

	$querry = "SELECT * FROM `user` WHERE `userName` = '$login' AND `passWord` = '$mot_de_passe'";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);

	return (mysqli_num_rows($res) == 1);
}    //renvoie un bool

function cherche_username($login){
	$sql = sqlconnect();

	$querry = "SELECT * FROM `user` WHERE `userName` = '$login'";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);

	return (mysqli_num_rows($res) == 1);
}  //renvoie un bool
function cherche_email($email){
	$sql = sqlconnect();

	$querry = "SELECT * FROM `user` WHERE `email` = '$email'";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);

	return (mysqli_num_rows($res) == 1);
}   //renvoie un bool

function force_password($password) {
	return true;
}   //renvoie un bool : true si le mdp est suffisament fort
function format_mail($email){
	$res=false;
	return $res;
} //renvoie un bool : true si le mail est au bon format
function format_username($login){
	$res=true;
	$log=str_split($login);
	foreach($log as $element){
		if ($element) {
			$res=false;
		}
	}
	return $res;
} //renvoie un bool : true si l'username est au bon format

?>