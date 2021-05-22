<?php
header("Content-Type: application/json");
require_once dirname(__FILE__) . "/../fileFunction.php";
require_once dirname(__FILE__) . "/../roleFunction.php";
require_once dirname(__FILE__) . "/../folderFunction.php";
require_once dirname(__FILE__) . "/../messageFunction.php";
require_once dirname(__FILE__) . "/../groupFunction.php";
require_once dirname(__FILE__) . "/../session.php";

$_post = json_decode(file_get_contents("php://input"));

$res = array(
    "success" => false
);

switch ($_post->action) {
    case "create":
        if (!isset($_post->nom)) {
            $res["error"] = 3001; //Nom de fichier vide
        } elseif (!isset($_post->folder)) {
            $res["error"] = 3002; //Dossier vide
        } elseif (empty(getIdentFolder($_post->folder))) {
            $res["error"]=3003;
        }elseif (!is_allowed($_session["user"]["id"],recup_group_folder($_post->folder),ROLE_CREATE_FILE)) {
            $res["error"] = 3004;
        }else{
            global $database;
            $nom=mysqli_real_escape_string($database,$_post->nom);
            $description=mysqli_real_escape_string($database,$_post->description);
            $res["success"]=true;
            $res["id"]=create_file(recup_group_folder($_post->folder),$_post->folder,$nom,$_post->type,$_post->size,$description,$_session["user"]["id"]);
            update_folder($_post->folder);
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
            $file = recup_file($_post->id,$_session["user"]["id"]);
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
                    "auteur" => [
                        "id" => (int) $file["creator_id"],
                        "name" => $file["creator_name"]
                    ],
                    "type" => $file["extension"],
                    "size" => (int) $file["size"],
                    
                    "etat" => $file["status"],
                    "nb_comments" => (int)$file["nb_comments"],
                    "nb_likes" => (int)$file["nb_likes"],

                    "chat"=> (int) $file["chat_id"],
                    "liked" =>(bool) $file["liked"],
                    "publish_date" => (int) $file["creation_date"],
                    "lastUpdate" => (int) $file["last_update"]
                );
                update_folder($file["location"],$_session["user"]["id"]);
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
            }elseif (!is_allowed($_session["user"]["id"],recup_group_file($_post->id),ROLE_REMOVE_ANY_FILE)) {
                $res["error"] = 3006;
            }if(is_creator($_session["user"]["id"],$_post->id)&&(!is_allowed($_session["user"]["id"],recup_group_file($_post->id),ROLE_REMOVE_FILE))){
                $res["error"] = 3006;
            }else {
                $res["success"]=supprime_file($_post->id);
            }
        }
        break;
    case "push":
        if (!isset($_post->id)) {
            $res["error"] = 0002; //id vide
        } elseif(!isset($_post->nom) || strlen($_post->nom) < 3) {
            $res["error"] = 3001; //nom vide
        }elseif(!isset($_post->description)) {
            $res["error"] = 3005; //description vide
        }elseif (empty(recup_file_id($_post->id))) {
            $res["error"] = 3006; //Fichier inexistant
        }elseif (!is_allowed($_session["user"]["id"],recup_group_file($_post->id),ROLE_RENAME_FILE)) {
            $res["error"] = 3004;
        } else {
            global $database;
            $nom = $_post->nom;
            $description = $_post->description;
            $res["success"] = modifie_file($_post->id,$nom,$description);
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
            global $database;
            $query=mysqli_real_escape_string($database,$_post->query);
            $res["results"] = search_files($query, $_post->page_first, (int)$_post->nb_results);
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
