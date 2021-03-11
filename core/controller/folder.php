<?php
session_start();
require_once dirname(__FILE__)."/../folderFunction.php";
$_post = json_decode(file_get_contents("php://input"));

$_SESSION["folder"]=array(
);
$res = array(
    "success" => false,
    "error" => -1
);

switch($_post->action){
    case "content":
        
        break;
    case "info":
        if ($_post->id == NULL) {
            $res["error"] = 0002; //id vide
        } else {
            $folder = recup_folder_id($_post->id)[0];
            if (empty($folder)){
                $res["error"] = 4002; //dossier inexistant
            }
            elseif ($post->lastUpdate == NULL){
                $res["error"] = 0003; //temps invalide
            }            
            elseif ($post->lastUpdate == $folder["lastUpdate"]) {
                $res["success"] = true;
                $res["groupe"] = NULL;
            } else {
                $res["success"] = true;
                $res["folder"] = array(
                    //"id" => $folder["id"],
                    "nom" => $folder["folderName"],
                    "description" => $folder["description"],
                    "lastUpdate" => $file["lastUpdate"]
                );
            }
        }
        break;
    case "create":
        if ($_post->nom == NULL) {
            $res["error"] = 4001; //nom de dossier invalide
        } elseif ($_post->parent == NULL) {
            $res["error"] = 4002; //dossier parent vide
        } elseif (!empty(recup_folder_id($_post->parent)[0])) {
            $res["error"] = 4004; //dossier parent inexistant
        }else{
            $res["success"]=true;
            $res["id"]=create_folder($_post->nom,$_post->parent);
        }
        break;
        break;
    default:
    $res["error"]=4000; //Erreur inconnu généré par folder
    break;
}

echo json_encode($res);
?>