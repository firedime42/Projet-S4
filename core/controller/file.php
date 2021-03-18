<?php
session_start();
header("Content-Type: application/json");
require_once dirname(__FILE__) . "/../fileFunction.php";
require_once dirname(__FILE__) . "/../folderFunction.php";
$_post = json_decode(file_get_contents("php://input"));

$_SESSION["file"]=array(

);
$res = array(
    "success" => false,
    "error" => -1
);

switch ($_post->action) {
    case "create":
        if ($_post->nom == NULL) {
            $res["error"] = 3001; //Nom de fichier vide
        } elseif ($_post->folder == NULL) {
            $res["error"] = 3002; //Dossier vide
        } elseif (!empty(recup_folder_id($_post->folder)[0])) {
            $res["error"] = 4003; //Dossier inexistant
        }elseif (!empty(recup_file_filename($_post->nom)[0])) {
            $res["error"] = 3004; //un fichier a le meme nom
        }else{
            create_file($_post->folder,$_post->nom,$_post->type,$_post->size,$_post->description);
        }
        break;
    case "pull":
        if ($_post->id == NULL) {
            $res["error"] = 0002; //id vide
        } else {
            $file = recup_file_id($_post->id)[0];
            if (empty($file)){
                $res["error"] = 3006; //Fichier inexistant
            }
            elseif ($post->lastUpdate == NULL){
                $res["error"] = 0003; //temps invalide
            }            
            elseif ($post->lastUpdate == $file["lastUpdate"]) {
                $res["success"] = true;
                $res["file"] = NULL;
            } else {
                $res["success"] = true;
                $res["file"] = array(
                    //"id" => $file["id"],
                    "nom" => $file["fileName"],
                    "description" => $file["description"],
                    "auteur" => $file["auteur"],
                    "type" => $file["type"],
                    "size" => $file["size"],
                    
                    "etat" => $file["etat"],
                    "nb_comments" => $file["nb_comments"],
                    "nb_likes" => $file["nb_likes"],

                    "renamed" => $file["renamed"],
                    "delete" => $file["delete"],
                    "liked" => $file["liked"],
                    "lastUpdate" => $file["lastUpdate"]
                );
            }
        }
        break;
    case "remove":
        session_destroy();
        if ($_post->id == NULL) {
            $res["error"] = 0002; //id vide
        } elseif (empty(recup_file_id($id)[0])) {
            $res["error"] = 3006; //Fichier inexistant
        } else {
            supprime_file($_post->id);
            $res["success"] = true;
        }
        break;
    case "push":
        if ($_post->id == NULL) {
            $res["error"] = 0002; //id vide
        } elseif($_post->nom == NULL){
            $res["error"] = 3001; //nom vide
        }elseif($_post->description == NULL){
            $res["error"] = 3005; //description vide
        }elseif (empty(recup_file_id($id)[0])) {
            $res["error"] = 3006; //Fichier inexistant
        } else {
            modifie_file($_post->id,$_post->nom,$_post->description);
            $res["success"] = true;
        }
        break;
    default:
        $ers["error"] = 3000; //Erreur inconnu généré par file
        break;
}


echo json_encode($res);
?>
