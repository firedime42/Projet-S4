<?php
header("Content-Type: application/json");
require_once dirname(__FILE__) . "/../fileFunction.php";
require_once dirname(__FILE__) . "/../folderFunction.php";
require_once dirname(__FILE__) . "/../session.php";

$_post = json_decode(file_get_contents("php://input"));

$_SESSION["file"]=array(

);
$res = array(
    "success" => false
);

switch ($_post->action) {
    case "create":
        if ($_post->nom == NULL) {
            $res["error"] = 3001; //Nom de fichier vide
        } elseif ($_post->folder == NULL) {
            $res["error"] = 3002; //Dossier vide
        } elseif (empty(recup_folder_id($_post->folder))) {
            $res["error"]=3003;
        /*}elseif (!empty(recup_file_filename($_post->nom))) {
            $res["error"] = 3004; //un fichier a le meme nom*/
        }else{
            $res["success"]=true;
            $res["id"]=create_file($_post->folder,$_post->nom,$_post->type,$_post->size,$_post->description,$_session["user"]["id"]);
        }
        break;
    case "end-upload":
        if ($_post->id == NULL) {
            $res["error"] = 0002; //id vide
        }else {
            $res["success"]=finish_upload($_post->id);
        }
        break;
    case "pull":
        if ($_post->id == NULL) {
            $res["error"] = 0002; //id vide
        } else {
            $file = recup_file_id($_post->id);
            if (empty($file)){
                $res["error"] = 3006; //Fichier inexistant
            }
            elseif ($_post->lastUpdate === NULL){
                $res["error"] = 0003; //temps invalide
            }            
            elseif ($_post->lastUpdate == $file["last_update"]) {
                $res["success"] = true;
                $res["file"] = NULL;
            } else {
                $res["success"] = true;
                $res["file"] = array(
                    //"id" => $file["id"],
                    "nom" => $file["name"],
                    "description" => $file["description"],
                    "auteur" => $file["creator_id"],
                    "type" => $file["extension"],
                    "size" => $file["size"],
                    
                    "etat" => $file["status"],
                    "nb_comments" => $file["nb_comments"],
                    "nb_likes" => $file["nb_likes"],

                    "renamed" => $file["rename"],
                    "delete" => $file["delete"],
                    "liked" => $file["like"],
                    "lastUpdate" => $file["last_update"]
                );
            }
        }
        break;
    case "remove":
        session_destroy();
        if ($_post->id == NULL) {
            $res["error"] = 0002; //id vide
        } elseif (empty(recup_file_id($id))) {
            $res["error"] = 3006; //Fichier inexistant
        } else {
            $res["success"]=supprime_file($_post->id);
        }
        break;
    case "push":
        if ($_post->id == NULL) {
            $res["error"] = 0002; //id vide
        } elseif($_post->nom == NULL){
            $res["error"] = 3001; //nom vide
        }elseif($_post->description == NULL){
            $res["error"] = 3005; //description vide
        }elseif (empty(recup_file_id($id))) {
            $res["error"] = 3006; //Fichier inexistant
        } else {
            $res["success"] = modifie_file($_post->id,$_post->nom,$_post->description);
        }
        break;
    case "search":
        if((int)$_post->nb_results <= 0){
            $res["error"]=3000; //Nombre de resulats invalide
        }
        elseif($_post->query!=NULL){
            $res["success"]=true;
            $res["results"] = search_files($_post->query, $_post->page_first, (int)$_post->nb_results);
        }else{
            $res["error"]=2005; //Recherche invalide(champ vide)
        }
        break;    
    default:
        $ers["error"] = 3000; //Erreur inconnu généré par file
        break;
}


echo json_encode($res);
?>
