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
        if ($_post->time===NULL)
            $res["error"]=0003; //temps invalide
        else{
            $res["success"]=true;
            $group_data=recup_groups_since($_session["user"]["id"],$_post->time);
            $groups=array();
            foreach($group_data as $group){
                $groups[]=array(
                    "id" => $group["id"],
                    "nom" => $group["name"],
                    "status" => recup_status_by_user_and_group($_session["user"]["id"],$group["id"]),
                    "new_docs" => 0,
                    "unread_docs" => 0,
                    "new_messages" => 0,
                    "description" => $group["description"],
                    "creator_id" => $group["id_creator"],
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
                    "status" => recup_status_by_user_and_group($_session["user"]["id"],$group["id"]),
                    "description" => $group["description"],
                    "avatar" => $group["avatar"],
                    "root" => $group["root"], //???
                    "nb_membres" => nb_members($group["id"]),
                    "nb_messages" => 0,//(int) $group["nb_messages"],
                    "nb_files" => 0,//(int) $group["nb_files"],
                    "creator_id" => $group["id_creator"],
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
        }elseif(!empty(recup_group_id($_post->id))){
            $res["error"]=0001;
        }else{
            $res["success"]=apply_group($_post->id,$_session["user"]["id"]);
            $res["status"]="pending";
        }
        break;
    case "leave":
        if($_post->id==NULL){
            $res["error"]=0001;
        }elseif (empty(recup_group_id($_post->id))){
            $res["error"]=0001;
        }elseif(is_owner($_session["user"]["id"],$_post->id)) {
            $res["error"]=0001;
        }else{
            $res["success"]=leave_group($_post->id,$_session["user"]["id"]);
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
            $res["groupe"]= create_group($_post->nom, $_post->description, $_session["user"]["id"]);
        }
        break;
    case "kickUser":
        if($_post->id==NULL){
            $res["error"]=2000;
        }elseif ($_post->group==NULL) {
            $res["error"]=2000;
        }elseif(empty(recup_user_id($_post->id))){
            $res["error"]=0001;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }elseif(is_owner($_post->id,$_post->group)){ //Si la personne est proprietaire du groupe
            $res["error"]=0001; //Il faut transferer la possesion du groupe avant de le retirer du groupe
        }elseif (!is_allowed($_session["user"]["id"],$_post->group,ROLE_KICK_USER)) {
            $res["error"]=2000;
        }else{
            $res["success"]=leave_group($_post->id,$_post->group);
        }
        break;
    case "acceptUser":
        if($_post->id==NULL){
            $res["error"]=2000;
        }elseif ($_post->group==NULL) {
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }elseif (!is_allowed($_session["user"]["id"],$_post->group,ROLE_ACCEPT_USER)) {
            $res["error"]=2000;
        }else{
            $res["success"]=join_group($_post->group,$_session["user"]["id"],$_post->id);
        }
        break;
    case "getRoles":
        if($_post->group_id==NULL){
            $res["error"]=0001;
        }elseif (empty(recup_group_id($_post->group_id))) {
            $res["error"]=0001;
        }else{
            $res["success"]=true;
            $perms=recup_roles($_post->group_id);
            foreach($perms as $val){
                $res["roles"][]=array(
                    "id" => (int)$val["id"],
                    "nom" => $val["name"],
                    // chat
                    "read_message" => ($val["read_message"]==1),
                    "write_message" => ($val["write_message"]==1),
                    "remove_message" => ($val["remove_message"]==1),
                    "remove_any_message" => ($val["remove_any_message"]==1),
                    // file
                    "download_file" => ($val["download_file"]==1),
                    "create_file" => ($val["create_file"]==1),
                    "rename_file" => ($val["rename_file"]==1),
                    "remove_file" => ($val["remove_file"]==1),
                    "remove_any_file" => ($val["remove_any_file"]==1),
                    // folder
                    "create_folder" => ($val["create_folder"]==1),
                    "rename_folder" => ($val["rename_folder"]==1),
                    "remove_folder" => ($val["remove_folder"]==1),
                    "remove_any_folder" => ($val["remove_any_folder"]==1),
                    // user
                    "accept_user" => ($val["accept_user"]==1),
                    "kick_user" => ($val["kick_user"]==1),
                    "manage_role" => ($val["manage_role"]==1),
                    // role
                    "edit_role" => ($val["edit_role"]==1),
                    // groupe
                    "edit_name" => ($val["edit_name"]==1),
                    "edit_description" => ($val["edit_description"]==1)
               );
            }
        }
    case "editRoles":
        if($_post->group_id==NULL){
            $res["error"]=2000;
        }elseif(empty(recup_group_id($_post->group_id))){
            $res["error"]=2000; 
        }elseif(!is_allowed($_session["user"]["id"],$_post->group_id,ROLE_MANAGE_ROLE)){
            $res["error"]=2000;
        }else{
            if($_post->edited!=NULL){
                foreach($_post->edited as $value){
                    edit_role($value["id"],$value["nom"],$value["read_message"],$value["write_message"],$value["remove_message"],$value["remove_any_message"],$value["download_file"],
                    $value["create_file"],$value["rename_file"],$value["remove_file"],$value["remove_any_file"],$value["create_folder"],$value["rename_folder"],$value["remove_folder"],$value["remove_any_folder"],
                    $value["accept_user"],$value["kick_user"],$value["manage_role"],$value["edit_role"],$value["edit_name"],$value["edit_description"]);
                }
            }
            if($_post->removed!=NULL){
                delete_role_tab($_post->group_id,$_post->removed);
            }
            if($_post->added!=NULL){
                foreach($_post->added as $value){
                    create_role($value["nom"],$_post->group_id,$value["read_message"],$value["write_message"],$value["remove_message"],$value["remove_any_message"],$value["download_file"],
                    $value["create_file"],$value["rename_file"],$value["remove_file"],$value["remove_any_file"],$value["create_folder"],$value["rename_folder"],$value["remove_folder"],$value["remove_any_folder"],
                    $value["accept_user"],$value["kick_user"],$value["manage_role"],$value["edit_role"],$value["edit_name"],$value["edit_description"]);
                }
            }
            $res["success"]=true;
        }
        break;
    case "getMembers":
        if($_post->group){
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }else{
            $res["success"]=true;
            $res["members"]=recup_membres($_post->group);
        }
        break;
    case "getApplications":
        if($_post->group){
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }else{
            $res["success"]=true;
            $res["applications"]=recup_applications($_post->group);
        }
        break;
    case "add-role":
        if($_post->user==NULL){
            $res["error"]=2000;
        }elseif ($_post->group==NULL) {
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }elseif (!is_allowed($_session["user"]["id"],$_post->group,ROLE_MANAGE_ROLE)) {
            $res["error"]=2000;
        }else{
            $res["success"]=add_role($_post->group,$_post->user,$_post->role);
        }
        break;
    case "push":
        if($_post->id == NULL){
            $res["error"]=0001;
        }elseif (empty(recup_group_id($_post->id))) {
            $res["error"]=0001;
        }elseif ($_post->nom == NULL) {
            $res["error"]=0001;
        }elseif ($_post->description == NULL) {
            $res["error"]=0001;
        }elseif (is_allowed($_session["user"]["id"],$_post->id,ROLE_EDIT_NAME) && is_allowed($_session["user"]["id"],$_post->id,ROLE_EDIT_DESCRIPTION)) {
            $res["error"]=0001;
        }else {
            $res["success"]=modif_groupe($_post->id,$_post->nom,$_post->description);
        }
        break;
   /* case "remove-role":
        if($_post->user==NULL){
            $res["error"]=2000;
        }elseif ($_post->group==NULL) {
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }elseif (!is_allowed($_session["user"]["id"],$_post->group,ROLE_EDIT_ROLE)) {
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
        }elseif (!is_allowed($_session["user"]["id"],$_post->group,ROLE_EDIT_ROLE)) {
            $res["error"]=2000;
        }elseif (nb_roles_group($_post->role)) {
            $res["success"]=2000;
        }else{
            $res["success"]=delete_role($_post->role);
        }
        break;*/
    default: $res["error"] = 2000; //Erreur inconnu généré par groupe
    break;
}

echo json_encode($res);
?>