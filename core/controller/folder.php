<?php
header("Content-Type: application/json");

require_once dirname(__FILE__)."/../folderFunction.php";
require_once dirname(__FILE__) . "/../session.php";
<<<<<<< HEAD
=======
require_once dirname(__FILE__) . "/../messageFunction.php";
>>>>>>> Matteo

$_post = json_decode(file_get_contents("php://input"));

$res["folder"]=array();
$res = array(
    "success" => false
);

switch($_post->action){
    case "pull":
<<<<<<< HEAD
        if ($_post->id == NULL) {
=======
        if (!isset($_post->id)) {
>>>>>>> Matteo
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
<<<<<<< HEAD
                $res["groupe"] = NULL;
=======
>>>>>>> Matteo
            } else {
                $res["success"] = true;
                $res["folder"] = array(
                    //"id" => $folder["id"],
                    "nom" => $folder["name"],
                    "description" => $folder["description"],
<<<<<<< HEAD
                    "folders" => recupere_dossiers_dans_dossier($_post->id),
                    "files" => recupere_fichiers_dans_dossier($_post->id),
=======
                    "groupe" => (int) $folder["group_id"],
                    "parent" => is_numeric($folder["parent_id"]) ? (int) $folder["parent_id"] : null,
                    "folders" => recupere_dossiers_dans_dossier($_post->id),
                    "files" => recupere_fichiers_dans_dossier($_post->id),
                    "chat"=> (int) $folder["chat_id"],
                    "nb_messages" => (int) $folder["nb_messages"],
>>>>>>> Matteo
                    "lastUpdate" => $folder["last_update"]
                );
            }
        }
        break;
    case "create":
<<<<<<< HEAD
        if ($_post->nom == NULL) {
            $res["error"] = 4001; //nom de dossier invalide
        } elseif ($_post->parent == NULL) {
            $res["error"] = 4002; //dossier parent vide
        } elseif (!empty(recup_folder_id($_post->parent))){
            $res["error"] = 4004; //dossier parent inexistant
        }else{
            $res["success"]=create_folder($_post->nom,$_post->parent,$_post->description);
            $res["id"]=recup_folder_nom_descr($_post->nom,$_post->description);
=======
        if (!isset($_post->nom)) {
            $res["error"] = 4001; //nom de dossier invalide
        } elseif (!isset($_post->parent)) {
            $res["error"] = 4002; //dossier parent vide
        } else {
            $parents = recup_folder_id($_post->parent);
            
            if (empty($parents)){
                $res["error"] = 4004; //dossier parent inexistant
            }elseif (!is_allowed($_session["user"]["id"],recup_group_folder($_post->parent),ROLE_CREATE_FOLDER)) {
                $res["error"] = 3004;
            }else{
                global $database;
                $nom=mysqli_real_escape_string($database,$_post->nom);
                $description=mysqli_real_escape_string($database,$_post->description);
                $res["parent"] = $parents;
                $res["success"]=create_folder($nom,$parents['group_id'],$_post->parent,$description);
                $res["id"]=mysqli_insert_id($database);
            }
>>>>>>> Matteo
        }
        break;
    default:
        $res["error"]=4000; //Erreur inconnu généré par folder
    break;
}

echo json_encode($res);
?>