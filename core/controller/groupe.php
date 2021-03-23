<?php
header("Content-Type: application/json");
require_once dirname(__FILE__)."/../groupFunction.php";
require_once dirname(__FILE__) . "/../session.php";
require_once dirname(__FILE__)."/../roleFunction.php";
$_post = json_decode(file_get_contents("php://input"));

$res = array(
    "success" => false
);


switch ($_post->action) {
    case "list":
        if ($_post->time===NULL) $res["error"]=0003; //temps invalide
        else{
            $res["success"]=true;
            $group_data=recup_groups_since($_SESSION["user"]["id"],$_post->time);
            $groups=array();
            foreach($group_data as $group){
                $groups[]=array(
                    "id" => $group["id"],
                    "nom" => $group["name"],
                    "status" => recup_status_by_user_and_group($_SESSION["user"]["id"],$group["id"]),
                    "new_docs" => 0,
                    "unread_docs" => 0,
                    "new_messages" => 0,
                    "description" => $group["description"],
                    "lastUpdate" => $group["last_update"]
                );
            }
            $res["groups"] = $groups; 
        }
        break;
    case "info":
        if($_post->id==NULL) $res["error"]=2; //id vide
        else{
            $group=recup_group_id($_post->id);
            if (empty($group)) $res["error"]=2002; //groupe inexistant
            elseif ($_post->time===NULL) $res["error"]=0003; //temps invalide
            elseif( $_post->time==$group["last_update"]){
            $res["success"]=true;
            $res["groupe"]=NULL;
            }
            else {
                $res["success"]=true;
                $res["groupe"]= array(
                    "id" => $group["id"],
                    "nom" => $group["name"],
                    "status" => recup_status_by_user_and_group($_SESSION["user"]["id"],$group["id"]),
                    "description" => $group["description"],
                    "avatar" => $group["avatar"],
                    "root" => $group["root"], //???
                    "nb_membres" => nb_members($group["id"]),
                    "nb_messages" => 0,//(int) $group["nb_messages"],
                    "nb_files" => 0,//(int) $group["nb_files"],
                    "lastUpdate" => $group["last_update"]
                );
                }
        }
        break;
    case "search":
        if((int)$_post->nb_results <= 0){
            $res["error"]=2004; //Nombre de resulats invalide
        }
        elseif($_post->query!=NULL){
            $res["success"]=true;
            $group_data=recherche_par_nom_ou_description($_post->query, $_post->page_first, (int)$_post->nb_results);
            $groups=array();
            foreach($group_data as $group){
                $groups[]=array(
                    "id" => $group["id"],
                    "nom" => $group["name"],
                    "description" => $group["description"],
                    "avatar" => $group["avatar"],
                    "nb_membres"=> nb_members($group["id"]),//$group["nb_membres"],
                    "nb_messages" => 0,//$group["nb_messages"]
                );
            }
            $res["results"] = $groups; 
        }else{
            $res["error"]=2005; //Recherche invalide(champ vide)
        }
        break;
    case "join":
        if($_post->id==NULL){
            $res["error"]=0001;
        }else{
            $res["success"]=apply_group($_post->id,$_SESSION["user"]["id"]);//apply ?
            $res["status"]="pending";
        }
        break;
    case "leave":
        if($_post->id==NULL){
            $res["error"]=0001;
        }else{
            $res["success"]=quit_group($_post->id,$_SESSION["user"]["id"]);
        }
        break;
    case "create":
        if($_post->nom==NULL){
            $res["error"]=2100;
        }
        elseif ($_post->description==NULL) {
            $res["error"]=2100;
        }else{
            $res["success"]=true;
            $res["groupe"]= create_group($_post->nom, $_post->description, $_SESSION["user"]["id"]);
        }

        break;
    case "remove-user":
        if($_post->id==NULL){
            $res["error"]=2000;
        }elseif ($_post->group==NULL) {
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }elseif (!is_owner($_session["user"]["id"],$_post->group)) {
            $res["error"]=2000;
        }else{
            $res["success"]=quit_group($_post->id,$_post->group);
        }
        break;
    case "accept-user":
        if($_post->id==NULL){
            $res["error"]=2000;
        }elseif ($_post->group==NULL) {
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }elseif (!is_owner($_session["user"]["id"],$_post->group)) {
            $res["error"]=2000;
        }else{
            $res["success"]=join_group($_post->id,$_post->group);
        }
        break;
    case "create-role":
        if($_post->group==NULL){
            $res["error"]=2000; 
        }elseif ($_post->name==NULL) {
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }else{
            if($_post->color==NULL){
                $res["success"]=create_role($_post->group,$_post->name);
            }elseif (format_color($_post->color)){
                $res["success"]=create_role_color($_post->group,$_post->name,$_post->color);
            }else{
                $res["error"]=2000;
            }
        }
        break;
    case "add-role":
        if($_post->user==NULL){
            $res["error"]=2000;
        }elseif(empty(recup_user_id($_post->id))){
            $res["error"]=2000;
        }elseif ($_post->group==NULL) {
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }elseif (!is_allowed($_session["user"]["id"],$_post->group,ROLE_RENAME_FILE)) {
            $res["error"]=2000;
        }else{
            $res["success"]=add_role($_post->group,$_post->user,$_post->role);
        }
        break;
    case "remove-role":
        if($_post->user==NULL){
            $res["error"]=2000;
        }elseif(empty(recup_user_id($_post->id))){
            $res["error"]=2000;
        }elseif ($_post->group==NULL) {
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }elseif (!is_allowed($_session["user"]["id"],$_post->group,ROLE_RENAME_FILE)) {
            $res["error"]=2000;
        }else{
            $res["success"]=remove_role($_post->group,$_post->user,$_post->role);
        }
        break;
    case "delete-role":
        if($_post->role==NULL){
            $res["error"]=2000;
        }elseif ($_post->group==NULL) {
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }elseif (!is_allowed($_session["user"]["id"],$_post->group,ROLE_RENAME_FILE)) {
            $res["error"]=2000;
        }elseif (nb_roles_group($_post->role)) {
            $res["success"]=2000;
        }else{
            $res["success"]=delete_role($_post->role);
        }
        break;
    default: $res["error"] = 2000; //Erreur inconnu généré par groupe
    break;
}

echo json_encode($res);
?>