<?php
header("Content-Type: application/json");
require_once dirname(__FILE__) . "/../fileFunction.php";
require_once dirname(__FILE__) . "/../folderFunction.php";
<<<<<<< HEAD
=======
require_once dirname(__FILE__) . "/../messageFunction.php";
require_once dirname(__FILE__) . "/../groupFunction.php";
>>>>>>> Matteo
require_once dirname(__FILE__) . "/../session.php";

$_post = json_decode(file_get_contents("php://input"));

<<<<<<< HEAD
$_SESSION["file"]=array(

);
=======
>>>>>>> Matteo
$res = array(
    "success" => false
);

switch ($_post->action) {
    case "create":
<<<<<<< HEAD
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
=======
        if (isset($_post->nom)) {
            $res["error"] = 3001; //Nom de fichier vide
        } elseif (isset($_post->folder)) {
            $res["error"] = 3002; //Dossier vide
        } elseif (empty(recup_folder_id($_post->folder))) {
            $res["error"]=3003;
        }elseif (!is_allowed($_session["user"]["id"],recup_group_folder($_post->folder),ROLE_CREATE_FILE)) {
            $res["error"] = 3004;
        }else{
            global $database;
            $nom=mysqli_real_escape_string($database,$_post->nom);
            $description=mysqli_real_escape_string($database,$_post->description);
            $res["success"]=true;
            $res["id"]=create_file(recup_group_folder($_post->folder),$_post->folder,$nom,$_post->type,$_post->size,$description,$_session["user"]["id"]);
        }
        break;
    case "end-upload":
        if (!isset($_post->id)) {
>>>>>>> Matteo
            $res["error"] = 0002; //id vide
        }else {
            $res["success"]=finish_upload($_post->id);
        }
        break;
    case "pull":
<<<<<<< HEAD
        if ($_post->id == NULL) {
            $res["error"] = 0002; //id vide
        } else {
            $file = recup_file_id($_post->id);
            if (empty($file)){
                $res["error"] = 3006; //Fichier inexistant
            }
            elseif ($_post->lastUpdate === NULL){
=======
        if (!isset($_post->id)) {
            $res["error"] = 0002; //id vide
        } else {
            $file = recup_file($_post->id,$_session["user"]["id"]);
            if (empty($file)){
                $res["error"] = 3006; //Fichier inexistant
            }
            elseif (!isset($_post->lastUpdate)){
>>>>>>> Matteo
                $res["error"] = 0003; //temps invalide
            }            
            elseif ($_post->lastUpdate == $file["last_update"]) {
                $res["success"] = true;
                $res["file"] = NULL;
            } else {
                $res["success"] = true;
                $res["file"] = array(
<<<<<<< HEAD
                    //"id" => $file["id"],
=======
>>>>>>> Matteo
                    "nom" => $file["name"],
                    "description" => $file["description"],
                    "auteur" => $file["creator_id"],
                    "type" => $file["extension"],
                    "size" => $file["size"],
                    
                    "etat" => $file["status"],
<<<<<<< HEAD
                    "nb_comments" => $file["nb_comments"],
                    "nb_likes" => $file["nb_likes"],

                    "renamed" => $file["rename"],
                    "delete" => $file["delete"],
                    "liked" => $file["like"],
=======
                    "nb_comments" => (int)$file["nb_comments"],
                    "nb_likes" => (int)$file["nb_likes"],

                    "chat"=>$file["chat_id"],
                    "liked" =>(bool) $file["liked"],
>>>>>>> Matteo
                    "lastUpdate" => $file["last_update"]
                );
            }
        }
        break;
    case "remove":
<<<<<<< HEAD
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
=======
        if (!isset($_post->id)) {
            $res["error"] = 0002; //id vide
        } elseif (empty(recup_file_id($_post->id))) {
            $res["error"] = 3006; //Fichier inexistant
        }else {
            $file=recup_file_id($_post->id);
            if(empty($file)){
                $res["error"] = 3006;
            }elseif (!is_allowed($_session["user"]["id"],recup_group_file($_post->id),ROLE_REMOVE_ANY_FILE)) {
                if(is_creator($_session["user"]["id"],$_post->id)&&(!is_allowed($_session["user"]["id"],recup_group_file($_post->id),ROLE_REMOVE_FILE)))
                    $res["error"] = 3006;
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
        }elseif (!is_allowed($_session["user"]["id"],recup_group_file($_post->id),ROLE_RENAME_FILE)) {
            $res["error"] = 3004;
        } else {
            global $database;
            $nom=mysqli_real_escape_string($database,$_post->nom);
            $description=mysqli_real_escape_string($database,$_post->description);
            $res["success"] = modifie_file($_post->id,$nom,$description);
>>>>>>> Matteo
        }
        break;
    case "search":
        if((int)$_post->nb_results <= 0){
            $res["error"]=3000; //Nombre de resulats invalide
        }
<<<<<<< HEAD
        elseif($_post->query!=NULL){
            $res["success"]=true;
            $res["results"] = search_files($_post->query, $_post->page_first, (int)$_post->nb_results);
        }else{
            $res["error"]=2005; //Recherche invalide(champ vide)
        }
        break;
    /*case "like":
        if($_post->id==NULL){
            $res["error"]=3000;
        }elseif(empty(recup_file_id($_post->id)){
            $res["error"]=3000;
        }else{
            $res["success"]=like_file($_post->id);
        }
        break;*/
    default:
        $ers["error"] = 3000; //Erreur inconnu généré par file
=======
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
>>>>>>> Matteo
        break;
}


echo json_encode($res);
?>
