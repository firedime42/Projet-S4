<?php

include_once("sql.php");

function creation_utilisateur($login, $email, $password) {

	//créer un utilisateur 

	$sql = sqlconnect();

	$querry = "INSERT INTO user (username, password, email) VALUES ('$login', '$password', '$email')";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);
	return $res;
}

/*function modifie_information_utilisateur($id, $login, $email) {
	
	$sql = sqlconnect();

	$querry = "UPDATE user u SET username = '$login', email = '$email' WHERE u.id = $id";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);

}

function modifie_mot_de_passe_utilisateur($id, $ancien_mot_de_passe, $mot_de_passe) {

	$sql = sqlconnect();

	$querry_get_password = "SELECT password FROM `user` WHERE `username` = 'id_to_username($id)' AND `password` = '$ancien_mot_de_passe'";
	$res = mysqli_query($sql, $querry_get_password);

	if (mysqli_num_rows($res) == 1) {

		$querry_set_password = "UPDATE `user` SET `password` = '$mot_de_passe' WHERE `user`.`iduser` = $id;";
		mysqli_query($sql, $querry_set_password);

	}

	mysqli_close($sql);

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
	$sql=sqlconnect();
    $user =  "SELECT * FROM user WHERE username = '$login'";
    $result = mysqli_query($sql, $user);
	$user_data=mysqli_fetch_assoc($result);
	mysqli_close($sql);
	return $user_data;
}
function recup_user_email($mail){
	$sql=sqlconnect();
    $user =  "SELECT * FROM user WHERE email = '$mail'";
	$result = mysqli_query($sql, $user);
	$user_data=mysqli_fetch_assoc($result);
	mysqli_close($sql);
	return $user_data;
}
function recup_user_id($id){
    $sql=sqlconnect();
    $user =  "SELECT * FROM user WHERE id = $id";
	$result = mysqli_query($sql, $user);
	$user_data=mysqli_fetch_assoc($result);
	mysqli_close($sql);
	return $user_data;
}
?>