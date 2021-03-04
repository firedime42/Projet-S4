<?php

$_post = json_decode(file_get_contents("php://input"));

$res = array(
    "success" => false,
    "error" => -1
);

switch ($_post->action) {
    case "login":
        $res["success"] = true;
        $res["user"] = array(
            "id" => 0,
            "username" => $_post->username
        );
    break;
    case "retrieve":
        $res["success"] = true;
        $res["user"] = array(
            "id" => 0,
            "username" => "Eleni Richard",
            "email" => "eleni.richard@coeur.2lion.com"
        );
    break;
    case "logout": $res["success"] = true; break;
    case "register":
        $res["success"] = true;
        $res["user"] = array(
            "id" => 0,
            "username" => $_post->username
        );
    break;
    default: $res["error"] = 0; break;
}

echo json_encode($res);
?>