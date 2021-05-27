<?php
header("Content-Type: application/json");

require_once dirname(__FILE__)."/../folderFunction.php";
require_once dirname(__FILE__) . "/../session.php";
require_once dirname(__FILE__) . "/../messageFunction.php";
require_once dirname(__FILE__) . "/../roleFunction.php";
require_once dirname(__FILE__) . "/../groupFunction.php";

$_post = json_decode(file_get_contents("php://input"));

$res["folder"]=array();
$res = array(
    "success" => false
);

switch($_post->action){
    case "pull":
        if (!isset($_post->id)) {
            $res["error"] = 0002; //id vide
        } elseif ($_post->lastUpdate === NULL) {
            $res["error"] = 0003; //temps invalide
        } else {
            $folder = getFolder($_post->id, $_session["user"]["id"]);

            if ($folder === NULL){
                $res["error"] = 4002; //dossier inexistant
            }
            elseif ($_post->lastUpdate == $folder["last_update"]) {
                $res["success"] = true;
                $res["folder"] = null;
            }
            else {
                $path = getFolderPath($_post->id);
                $folders = getSubFolders($_post->id, $_session["user"]["id"]);
                $files = getSubFiles($_post->id, $_session["user"]["id"]);

                $res["success"] = true;
                $res["folder"] = array(
                    "nom" => $folder["name"],
                    "description" => $folder["description"],
                    "path" => $path,
                    "groupe" => $folder["group_id"],
                    "chat"=> $folder["chat_id"],
                    "nb_messages" => $folder["nb_messages"],
                    "notif_messages" => $folder["notif_messages"],
                    "folders" => $folders,
                    "files" => $files,
                    "lastUpdate" => $folder["last_update"]
                );
                visitFolder($_post->id, $_session["user"]["id"]);
            }
        }
        break;
    case "create":
        if (!isset($_post->nom)) {
            $res["error"] = 4001; //nom de dossier invalide
        } elseif (!isset($_post->parent)) {
            $res["error"] = 4002; //dossier parent vide
        } else {
            $parent = getIdentFolder($_post->parent);
            
            if (!$parent){
                $res["error"] = 4004; //dossier parent inexistant
            }elseif (!is_allowed($_session["user"]["id"], $parent['group_id'], ROLE_CREATE_FOLDER)) {
                $res["error"] = 3004;
            }else{
                $res["success"]=true;
                $res["id"]=create_folder($parent['group_id'],$_post->nom,$_post->description,$_post->parent);
            }
        }
        break;
    case "remove":
        if(!isset($_post->id)){
            $res["error"]=4000;
        }else {
            $folder = getIdentFolder($_post->id);
            if (!$folder) {
                $res["error"] = 4004; // dossier inexistant
            } elseif (!is_allowed($_session["user"]["id"],$folder['group_id'],ROLE_REMOVE_FOLDER)) {
                $res["error"] = 4000;
            }else{
                $res["success"] = remove_folder($_post->id);
            }
        }
        break;
    default:
        $res["error"]=4000; //Erreur inconnue générée par folder
    break;
}

echo json_encode($res);
?>