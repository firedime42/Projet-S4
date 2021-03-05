<?php

$_post = json_decode(file_get_contents("php://input"));

$res = array(
    "success" => false,
    "error" => -1
);

$groups = array(
    array("id" => 0, "nom" => "Groupe 1", "time" => 1613909869, "status" => "membre"),
    array("id" => 1, "nom" => "Groupe 2", "time" => 1613909869, "status" => "membre"),
    array("id" => 2, "nom" => "Groupe 3", "time" => 1613954609, "status" => "ex"),
    array("id" => 3, "nom" => "Groupe 4", "time" => 1613909869, "status" => "membre"),
    array("id" => 4, "nom" => "Groupe 5", "time" => 1613909869, "status" => "membre"),
    array("id" => 5, "nom" => "Groupe 6", "time" => 1613909869, "status" => "membre")
);


switch ($_post->action) {
    case "list":
        $res["success"] = true;
        $res["groups"] = array_filter($groups, function($v) {
            global $_post;
            return $v["time"] > $_post->time;
        });
    break;
    default: $res["error"] = 0; break;
}

echo json_encode($res);
?>