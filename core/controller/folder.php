<?php
header("Content-Type: application/json");

require_once dirname(__FILE__)."/../folderFunction.php";
require_once dirname(__FILE__) . "/../session.php";

$_post = json_decode(file_get_contents("php://input"));

$res["folder"]=array();
$res = array(
    "success" => false
);

switch($_post->action){
    case "pull":
        if ($_post->id == NULL) {
            $res["error"] = 0002; //id vide
        } else {
            $folder = recup_folder_id($_post->id);

            if (empty($folder)){
                $res["error"] = 4002; //dossier inexistant
            }
            elseif ($_post->lastUpdate === NULL){
                $res["error"] = 0003; //temps invalide
            }            
            elseif ($_post->lastUpdate == $folder["last_update"]) {
                $res["success"] = true;
                $res["groupe"] = NULL;
            } else {
                $res["success"] = true;
                $res["folder"] = array(
                    //"id" => $folder["id"],
                    "nom" => $folder["name"],
                    "description" => $folder["description"],
                    "folders" => recupere_dossiers_dans_dossier($_post->id),
                    "files" => recupere_fichiers_dans_dossier($_post->id),
                    "lastUpdate" => $folder["last_update"]
                );
            }
        }
        break;
    case "create":
        if ($_post->nom == NULL) {
            $res["error"] = 4001; //nom de dossier invalide
        } elseif ($_post->parent == NULL) {
            $res["error"] = 4002; //dossier parent vide
        } elseif (!empty(recup_folder_id($_post->parent))){
            $res["error"] = 4004; //dossier parent inexistant
        }else{
            $res["success"]=create_folder($_post->nom,$_post->parent,$_post->description);
            $res["id"]=recup_folder_nom_descr($_post->nom,$_post->description);
        }
        break;
    default:
        $res["error"]=4000; //Erreur inconnu généré par folder
    break;
}

echo json_encode($res);
?>