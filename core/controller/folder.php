<?php

$time_start = microtime(true);
session_start();
header("Content-Type: application/json");
$time_session_start = microtime(true) - $time_start;

$time_start = microtime(true);
require_once dirname(__FILE__)."/../folderFunction.php";
$time_for_get_db = microtime(true) - $time_start;

$time_start = microtime(true);
$_post = json_decode(file_get_contents("php://input"));
$time_parse_data = microtime(true) - $time_start;

$_SESSION["folder"]=array();
$res = array(
    "success" => false,
    "error" => 4000
);

switch($_post->action){
    case "pull":
        if ($_post->id == NULL) {
            $res["error"] = 0002; //id vide
        } else {
            $time_start = microtime(true);
            $folder = recup_folder_id($_post->id);
            $time_recup_folder = microtime(true) - $time_start;

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
                $time_start = microtime(true);
                $res["success"] = true;
                $res["folder"] = array(
                    //"id" => $folder["id"],
                    "nom" => $folder["name"],
                    "description" => $folder["description"],
                    "folders" => recupere_dossiers_dans_dossier($_post->id),
                    "files" => recupere_fichiers_dans_dossier($_post->id),
                    "lastUpdate" => $folder["last_update"]
                );
                $time_for_iterate = microtime(true) - $time_start;
            }

            $res["time_for_get_db"] = $time_for_get_db;
            $res["time_parse_data"] = $time_parse_data;
            $res["time_recup_folder"] = $time_recup_folder;
            $res["time_session_start"] = $time_session_start;
            $res["time_for_iterate"] = $time_for_iterate;
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