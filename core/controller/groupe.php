<?php
header("Content-Type: application/json");
require_once dirname(__FILE__)."/../groupFunction.php";
require_once dirname(__FILE__) . "/../session.php";
require_once dirname(__FILE__)."/../roleFunction.php";
require_once dirname(__FILE__)."/../accountFunction.php";
require_once dirname(__FILE__)."/../messageFunction.php";
require_once dirname(__FILE__)."/../folderFunction.php";

$_post = json_decode(file_get_contents("php://input"));

$res = array(
    "success" => false
);


switch ($_post->action) {
    case "list":
        if (!isset($_post->time))
            $res["error"]=0003; //temps invalide
        else{
            $res["success"]=true;
            $group_data=recup_groups_since($_session["user"]["id"],$_post->time);
            $groups=array();
            $nb_groups = count($group_data);
            for ($i = 0; $i < $nb_groups; $i++) {
                $group = $group_data[$i];
                $groups[]=array(
                    "id" => $group["id"],
                    "nom" => $group["name"],
                    "status" => $group["user_status"],//recup_status_by_user_and_group($_session["user"]["id"],$group["id"]),
                    "description" => $group["description"],
                    "creator_id" => $group["id_creator"],
                    "lastUpdate" => $group["last_update"],
                    "notif_folder" => (bool) $group["notif_folder"]
                );
            }
            $res["groups"] = $groups; 
        }
        break;
    case "info":
        if(!isset($_post->id)) $res["error"]=2; //id vide
        else{
            $group=recup_group_id($_post->id);
            if (!$group) $res["error"]=2002; //groupe inexistant
            elseif (!isset($_post->time)) $res["error"]=0003; //temps invalide
            elseif( $_post->time==$group["last_update"]){
            $res["success"]=true;
            $res["groupe"]=NULL;
            }
            elseif (est_dans_groupe($_post->id,$_session["user"]["id"])) {
                $dash=recup_info($_post->id);
                $res["success"]=true;
                $group=recup_group($_post->id,$_session["user"]["id"]);
                $notif=notifs($group["id"],$_session["user"]["id"]);
                $res["groupe"]= array(
                    "id" => (int) $group["id_group"],
                    "nom" => $group["group_name"],
                    "status" => recup_status_by_user_and_group($_session["user"]["id"],$group["id_group"]),
                    "description" => $group["description"],
                    "root" => (int) $group["root"], //???
                    "nb_members" => (int) $dash["nb_members"],//nb_members($group["id"]),
                    "nb_messages" => (int) $dash["nb_messages"],//(int) $group["nb_messages"],
                    "nb_files" => (int) $dash["nb_files"],
                    "creator" => [
                        "id" => (int) $group["id_creator"],
                        "name" => $group["creator_name"]
                    ],
                    "lastUpdate" => $group["last_update"],
                    "notif_folder" => (bool) $notif["notif_folder"]>0,
                    "notif_message" => (bool) $notif["notif_message"]>0,
                    "permissions" => array(
                        "write_message" => ($group["write_message"]==1),
                        "remove_message" => ($group["remove_message"]==1),
                        "remove_any_message" => ($group["remove_any_message"]==1),
                        // file
                        "download_file" => ($group["download_file"]==1),
                        "create_file" => ($group["create_file"]==1),
                        "rename_file" => ($group["rename_file"]==1),
                        "remove_file" => ($group["remove_file"]==1),
                        "remove_any_file" => ($group["remove_any_file"]==1),
                        // folder
                        "create_folder" => ($group["create_folder"]==1),
                        "rename_folder" => ($group["rename_folder"]==1),
                        "remove_folder" => ($group["remove_folder"]==1),
                        // user
                        "accept_user" => ($group["accept_user"]==1),
                        "kick_user" => ($group["kick_user"]==1),
                        "manage_role" => ($group["manage_role"]==1),
                        // role
                        "edit_role" => ($group["edit_role"]==1),
                        // groupe
                        "edit_name" => ($group["edit_name"]==1),
                        "edit_description" => ($group["edit_description"]==1)
                    )
                );
            }else{
                $dash=recup_info($_post->id);
                $res["success"]=true;
                $res["groupe"]= array(
                    "id" => (int) $group["id"],
                    "nom" => $group["name"],
                    "status" => recup_status_by_user_and_group($_session["user"]["id"],$group["id"]),
                    "description" => $group["description"],
                    "root" => (int) $group["root"], //???
                    "nb_members" => (int) $dash["nb_members"],//nb_members($group["id"]),
                    "nb_messages" => (int) $dash["nb_messages"],//(int) $group["nb_messages"],
                    "nb_files" => (int) $dash["nb_files"],
                    "creator" => [
                        "id" => (int) $group["id_creator"],
                        "name" => $group["creator_name"]
                    ],
                    "lastUpdate" => $group["last_update"]
                );
            }
        }
        break;
    case "search":
        if((int)$_post->nb_results <= 0){
            $res["error"]=2004; //Nombre de resulats invalide
        }
        elseif(isset($_post->query)){
            $res["success"]=true;
            $group_data=recherche_par_nom_ou_description($_post->query, $_post->page_first, (int)$_post->nb_results);
            $groups=array();
            foreach($group_data as $group){
                $groups[]=array(
                    "id" => (int) $group["id"],
                    "nom" => $group["name"],
                    "description" => $group["description"],
                    "avatar" => $group["avatar"],
                    "nb_members"=> (int) $group["nb_members"],//nb_members($group["id"]),//$group["nb_members"],
                    "nb_messages" => 0,//$group["nb_messages"]
                );
            }
            $res["results"] = $groups; 
        }else{
            $res["error"]=2005; //Recherche invalide(champ vide)
        }
        break;
    case "join":
        if(!isset($_post->id)){
            $res["error"]=0001;
        }elseif(empty(recup_group_id($_post->id))){
            $res["error"]=0001;
        }else{
            $res["success"]=apply_group($_post->id,$_session["user"]["id"]);
            $res["status"]="pending";
        }
        break;
    case "leave":
        if(!isset($_post->id)){
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
        if(!isset($_post->nom)){
            $res["error"]=2100;
        }
        elseif (!isset($_post->description)) {
            $res["error"]=2100;
        }else{
            $res["success"]=true;
            $res["groupe"]= create_group($_post->nom, $_post->description, $_session["user"]["id"]);
        }
        break;
    case "kickUser":
        if(!isset($_post->id)){
            $res["error"]=2001;
        }elseif (!isset($_post->group)) {
            $res["error"]=2002;
        }elseif(empty(recup_user_id($_post->id))){
            $res["error"]=0001;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2003;
        }elseif(is_owner($_post->id,$_post->group)){ //Si la personne est proprietaire du groupe
            $res["error"]=0001; //Il faut transferer la possession du groupe avant de le retirer du groupe
        }else{
            if ($_session["user"]["id"]==$_post->id) {
                $res["success"]=leave_group($_post->group,$_post->id);
             } else{
                if (!is_allowed($_session["user"]["id"],$_post->group,ROLE_KICK_USER)) {
                    $res["error"]=2004;
                }else{
                    $res["success"]=leave_group($_post->group,$_post->id);
                }
            }
        }
        break;
    case "acceptUser":
        if(!isset($_post->id)){
            $res["error"]=2000;
        }elseif (!isset($_post->group)) {
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }elseif (!is_allowed($_session["user"]["id"],$_post->group,ROLE_ACCEPT_USER)) {
            $res["error"]=2000;
        }else{
            $res["success"]=join_group($_post->group,$_post->id);
        }
        break;
    case "getRoles":
        if(!isset($_post->group_id)){
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
        break;
    case "editRoles":
        if(!isset($_post->group_id)){
            $res["error"]=2000;
        }elseif(empty(recup_group_id($_post->group_id))){
            $res["error"]=2000; 
        }elseif(!is_allowed($_session["user"]["id"],$_post->group_id,ROLE_EDIT_ROLE)){
            $res["error"]=2008;
        }else{
            if(isset($_post->edited)){
                foreach($_post->edited as $value){
                    edit_role($value->id,
                    $value->nom,(int)$value->read_message,
                    (int)$value->write_message,(int)$value->remove_message,(int)$value->remove_any_message,
                    (int)$value->download_file,(int)$value->create_file,(int)$value->rename_file,
                    (int)$value->remove_file,(int)$value->remove_any_file,
                    (int)$value->create_folder,(int)$value->rename_folder,(int)$value->remove_folder,
                    (int)$value->accept_user,(int)$value->kick_user,(int)$value->manage_role,
                    (int)$value->edit_role,(int)$value->edit_name,(int)$value->edit_description);
                }
            }
            if(isset($_post->removed)){
                delete_role_tab($_post->group_id,$_post->removed);
            }
            if(isset($_post->added)){
                foreach($_post->added as $value){
                    create_role($_post->group_id,$value->nom,(int)$value->read_message,(int)$value->write_message,(int)$value->remove_message,(int)$value->remove_any_message,(int)$value->download_file,
                    (int)$value->create_file,(int)$value->rename_file,(int)$value->remove_file,(int)$value->remove_any_file,(int)$value->create_folder,(int)$value->rename_folder,(int)$value->remove_folder,
                    (int)$value->accept_user,(int)$value->kick_user,(int)$value->manage_role,(int)$value->edit_role,(int)$value->edit_name,(int)$value->edit_description);}
            }
            $res["success"]=true;
        }
        break;
    case "setRole":
        if(!isset($_post->group)){
            $res["error"]=2003;
        }elseif (!isset($_post->user)) {
            $res["error"]=2004;
        }elseif (!isset($_post->role)) {
            $res["error"]=2005;
        }elseif (empty(recup_user_id($_post->user))) {
            $res["error"]=2006;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2007;
        }elseif (empty(recup_role_id($_post->role))) {
            $res["error"]=2008;
        }elseif (!is_allowed($_session["user"]["id"],$_post->group,ROLE_MANAGE_ROLE)) {
            $res["error"]=2002;
        }else{
            $res["success"]=add_role($_post->group,$_post->user,$_post->role);
        }
        break;
    case "getMembers":
        if(!isset($_post->group)){
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }else{
            $res["success"]=true;
            $res["members"]=recup_membres($_post->group);
        }
        break;
    case "getApplications":
        if(!isset($_post->group)){
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }else{
            $res["success"]=true;
            $res["applications"]=recup_applications($_post->group);
        }
        break;
    case "push":
        if(!isset($_post->id)){
            $res["error"]=0001;
        }elseif (empty(recup_group_id($_post->id))) {
            $res["error"]=0001;
        }elseif(isset($_post->nom)){
            if(is_allowed($_session["user"]["id"],$_post->id,ROLE_EDIT_NAME))
                $res["success"]=modif_nom_group($_post->id,$_post->nom);
            else
                $res["error"]=2008;
        }elseif(isset($_post->description)){
            if(is_allowed($_session["user"]["id"],$_post->id,ROLE_EDIT_DESCRIPTION))
                $res["success"]=modif_description_group($_post->id,$_post->description);
            else
                $res["error"]=2008;
        }else
            $res["error"]=2000;
        break;
    case "getDashboard":
        if(!isset($_post->group)){

        }elseif (empty(recup_group_id($_post->group))) {
            # code...
        }else{
            $res["success"]=true;
            $dashboard=recup_dashboard($_post->group);
            $info=recup_info($_post->group);
            $res["group"]=array(
                "nb_members" => (int)$info["nb_members"], 
                "nb_messages" => (int)$info["nb_messages"],
                "nb_files"=> (int)$info["nb_files"],
                "dashboard"=>array(
                    "repart_type"=> recup_repart($_post->group),
                    "total_space"=> (int)$info["total_space"], 
                    "avg_space"=> (int)$info["total_space"]/(int)$info["nb_files"],
                    "most_liked"=> recup_most_liked($_post->group),
                    "most_commented"=> recup_most_commented($_post->group),
                    "files"=>recup_file_dashboard($_post->group),
                    "users"=>recup_membres_dashboard($_post->group),
                    "messages"=>recup_date_messages($_post->group) 
                )
            );// liste des dates de publications de tous les messages du site
        }
        
        break;
    default: $res["error"] = 2000; //Erreur inconnue générée par groupe
    break;
}

echo json_encode($res);

?>