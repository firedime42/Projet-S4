<?php
header("Content-Type: application/json");

require_once dirname(__FILE__)."/../folderFunction.php";
require_once dirname(__FILE__) . "/../session.php";
require_once dirname(__FILE__) . "/../messageFunction.php";


$_post = json_decode(file_get_contents("php://input"));

$res["folder"]=array();
$res = array(
    "success" => false
);

switch($_post->action){
    case "pull":
        if (!isset($_post->id)) {
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
                    "chat"=> $folder["chat_id"],
                    "lastUpdate" => $folder["last_update"]
                );
            }
        }
        break;
    case "create":
        if (!isset($_post->nom)) {
            $res["error"] = 4001; //nom de dossier invalide
        } elseif (!isset($_post->parent)) {
            $res["error"] = 4002; //dossier parent vide
        } elseif (empty(recup_folder_id($_post->parent))){
            $res["error"] = 4004; //dossier parent inexistant
        /*}elseif (!is_allowed($_session["user"]["id"],,ROLE_CREATE_FOLDER)) {
            $res["error"] = 3004;*/
        }else{
            $res["success"]=create_folder($_post->nom,$_post->parent,$_post->description);
            $res["id"]=recup_folder_nom_descr($_post->nom,$_post->description); // wtf !?
        }
        break;
    default:
        $res["error"]=4000; //Erreur inconnu généré par folder
    break;
}

echo json_encode($res);
?>