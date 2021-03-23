<?php

require_once("sql.php");

function creation_utilisateur($login, $email, $password) {

	//créer un utilisateur 
	global $database;
	$querry = "INSERT INTO user (username, password, email) VALUES ('$login', '$password', '$email')";
	$res = mysqli_query($database, $querry);
	return $res;
}

/*function modifie_information_utilisateur($id, $login, $email) {
	
	$database = sqlconnect();

	$querry = "UPDATE user u SET username = '$login', email = '$email' WHERE u.id = $id";

	$res = mysqli_query($database, $querry);

}

function modifie_mot_de_passe_utilisateur($id, $ancien_mot_de_passe, $mot_de_passe) {

	$database = sqlconnect();

	$querry_get_password = "SELECT password FROM `user` WHERE `username` = 'id_to_username($id)' AND `password` = '$ancien_mot_de_passe'";
	$res = mysqli_query($database, $querry_get_password);

	if (mysqli_num_rows($res) == 1) {

		$querry_set_password = "UPDATE `user` SET `password` = '$mot_de_passe' WHERE `user`.`iduser` = $id;";
		mysqli_query($database, $querry_set_password);

	}


}*/

function format_mail($email){
	$res=true;
	$log=str_split($email);
	foreach($log as $element){
		if ($element==" ") {
			$res=false;
		}
	}
	return $res;
} //renvoie un bool : true si le mail est au bon format
function format_username($login){
	$res=true;
	$log=str_split($login);
	foreach($log as $element){
		if ($element==" ") {
			$res=false;
		}
	}
	return $res;
} //renvoie un bool : true si l'username est au bon format

function recup_user_username($login){
	global $database;
    $user =  "SELECT * FROM user WHERE username = '$login'";
    $result = mysqli_query($database, $user);
	$user_data=mysqli_fetch_assoc($result);
	return $user_data;
}
function recup_user_email($mail){
	global $database;
    $user =  "SELECT * FROM user WHERE email = '$mail'";
	$result = mysqli_query($database, $user);
	$user_data=mysqli_fetch_assoc($result);
	return $user_data;
}
function recup_user_id($id){
	global $database;
    $user =  "SELECT * FROM user WHERE id = $id";
	$result = mysqli_query($database, $user);
	$user_data=mysqli_fetch_assoc($result);
	return $user_data;
}
?>