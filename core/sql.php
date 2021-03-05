<?php 

function sqlconnect(){	

	//simple fonction pour open la connection sans retaper tout les mysqli conncet

	include("config.php");

	$database = mysqli_connect($_CONFIG["db_host"], $_CONFIG["db_user"], $_CONFIG["db_password"], $_CONFIG["db_name"]);
	if (mysqli_connect_errno()) {
		printf("Échec de la connexion : %s\n", mysqli_connect_error());
	}
	return $database;
}
 ?>