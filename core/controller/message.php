<?php
	header("Content-Type: application/json");
	require_once dirname(__FILE__)."/../groupFunction.php";
	require_once dirname(__FILE__) . "/../session.php";
	require_once dirname(__FILE__)."/../roleFunction.php";
	$_post = json_decode(file_get_contents("php://input"));
	
	$res = array(
		"success" => false
	);


?>
