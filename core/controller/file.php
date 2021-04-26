<?php
header("Content-Type: application/json");
require_once dirname(__FILE__) . "/../fileFunction.php";
require_once dirname(__FILE__) . "/../folderFunction.php";
require_once dirname(__FILE__) . "/../messageFunction.php";
require_once dirname(__FILE__) . "/../session.php";

$_post = json_decode(file_get_contents("php://input"));

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
        /*}elseif (!is_allowed($_session["user"]["id"],,ROLE_CREATE_FILE)) {
            $res["error"] = 3004;*/
        }else{
            $res["success"]=true;
            $res["id"]=create_file($_post->folder,$_post->nom,$_post->type,$_post->size,$_post->description,$_session["user"]["id"]);
        }
        break;
    case "end-upload":
        if (!isset($_post->id)) {
            $res["error"] = 0002; //id vide
        }else {
            $res["success"]=finish_upload($_post->id);
        }
        break;
    case "pull":
        if (!isset($_post->id)) {
            $res["error"] = 0002; //id vide
        } else {
            $file = recup_file_id($_post->id);
            if (empty($file)){
                $res["error"] = 3006; //Fichier inexistant
            }
            elseif (!isset($_post->lastUpdate)){
                $res["error"] = 0003; //temps invalide
            }            
            elseif ($_post->lastUpdate == $file["last_update"]) {
                $res["success"] = true;
                $res["file"] = NULL;
            } else {
                $res["success"] = true;
                $res["file"] = array(
                    "nom" => $file["name"],
                    "description" => $file["description"],
                    "auteur" => $file["creator_id"],
                    "type" => $file["extension"],
                    "size" => $file["size"],
                    
                    "etat" => $file["status"],
                    "nb_comments" => $file["nb_comments"],
                    "nb_likes" => (int)$file["nb_likes"],

                    "chat"=>$file["chat_id"],
                    "renamed" => true,//is_allowed($_session["user"],,ROLE_RENAME_FILE),
                    "delete" => true,//is_allowed($_session["user"],,ROLE_REMOVE_FILE),
                    "liked" => is_liked($_post->id,$_session["user"]["id"]),
                    "lastUpdate" => $file["last_update"]
                );
            }
        }
        break;
    case "remove":
        if (!isset($_post->id)) {
            $res["error"] = 0002; //id vide
        } elseif (empty(recup_file_id($_post->id))) {
            $res["error"] = 3006; //Fichier inexistant
        }else {
            $file=recup_file_id($_post->id);
            if(empty($file)){
                $res["error"] = 3006;
            /*}elseif ((!is_allowed($_session["user"]["id"],$test,ROLE_REMOVE_FILE))&&(!is_allowed($_session["user"]["id"],$test,ROLE_REMOVE_ANY_FILE))) {
                $res["error"] = 3006;*/
            }else {
                $res["success"]=supprime_file($_post->id);
            }
        }
        break;
    case "push":
        if (!isset($_post->id)) {
            $res["error"] = 0002; //id vide
        } elseif($_post->nom == NULL){
            $res["error"] = 3001; //nom vide
        }elseif(!isset($_post->description)){
            $res["error"] = 3005; //description vide
        }elseif (empty(recup_file_id($id))) {
            $res["error"] = 3006; //Fichier inexistant
        /*}elseif (!is_allowed($_session["user"]["id"],,ROLE_RENAME_FILE)) {
            $res["error"] = 3004;*/
        } else {
            $res["success"] = modifie_file($_post->id,$_post->nom,$_post->description);
        }
        break;
    case "search":
        if((int)$_post->nb_results <= 0){
            $res["error"]=3000; //Nombre de resulats invalide
        }
        elseif(!isset($_post->query)){
            $res["error"]=2005; //Recherche invalide(champ vide)
        }else{
            $res["success"]=true;
            $res["results"] = search_files($_post->query, $_post->page_first, (int)$_post->nb_results);
        }
        break;
    case "like":
        if(!isset($_post->id)){
            $res["error"]=3000;
        }elseif(empty(recup_file_id($_post->id))){
            $res["error"]=3000;
        }elseif (is_liked($_post->id,$_session["user"]["id"])) {
            $res["error"]=3008;
        }else{
            modif_nombre_like($_post->id,1);
            $res["success"]=like_file($_post->id,$_session["user"]["id"]);
        }
        break;
    case "unlike":
        if(!isset($_post->id)){
            $res["error"]=3000;
        }elseif(empty(recup_file_id($_post->id))){
            $res["error"]=3000;
        }elseif (!is_liked($_post->id,$_session["user"]["id"])) {
            $res["error"]=3008;
        }else{
            modif_nombre_like($_post->id,-1);
            $res["success"]=unlike_file($_post->id,$_session["user"]["id"]);
        }
        break;
    default:
        $res["error"] = 3000; //Erreur inconnu généré par file
        break;
}


echo json_encode($res);
?>
