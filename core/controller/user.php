<?php

require dirname(__FILE__).'/../sql.php';
require dirname(__FILE__).'/../userFunctions.php';

$_post = json_decode(file_get_contents("php://input"));

$res = array( "success" => false );

switch ($_post->action) {
    case "pull":
        if (!isset($_post->id)) $res["error"] = 1; // si pas d'identifiant
        else if (!(is_numeric($_post->id) && $_post->id >= 0)) $res["error"] = 2; // si identifiant invalide
        else {
            $user = getUserById($_post->id);

            if ($user == null) $res["error"] = 3;
            else {
                $res["success"] = true;
                $res["user"] = $user;
            }
        }
        break;
    default:
        $res["error"] = 0;
        break;
}

echo json_encode($res);

?>