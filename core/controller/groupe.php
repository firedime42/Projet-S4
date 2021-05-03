<?php
header("Content-Type: application/json");
require_once dirname(__FILE__)."/../groupFunction.php";
require_once dirname(__FILE__) . "/../session.php";
require_once dirname(__FILE__)."/../roleFunction.php";
require_once dirname(__FILE__)."/../accountFunction.php";
<<<<<<< HEAD
=======
require_once dirname(__FILE__)."/../messageFunction.php";
require_once dirname(__FILE__)."/../folderFunction.php";

>>>>>>> Matteo
$_post = json_decode(file_get_contents("php://input"));

$res = array(
    "success" => false
);


switch ($_post->action) {
    case "list":
<<<<<<< HEAD
        if ($_post->time===NULL)
=======
        if (!isset($_post->time))
>>>>>>> Matteo
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
<<<<<<< HEAD
        if($_post->id==NULL) $res["error"]=2; //id vide
        else{
            $group=recup_group_id($_post->id);
            if (empty($group)) $res["error"]=2002; //groupe inexistant
            elseif ($_post->time===NULL) $res["error"]=0003; //temps invalide
=======
        if(!isset($_post->id)) $res["error"]=2; //id vide
        else{
            $group=recup_group($_post->id,$_session["user"]["id"]);
            if (empty($group)) $res["error"]=2002; //groupe inexistant
            elseif (!isset($_post->time)) $res["error"]=0003; //temps invalide
>>>>>>> Matteo
            elseif( $_post->time==$group["last_update"]){
            $res["success"]=true;
            $res["groupe"]=NULL;
            }
            else {
                $res["success"]=true;
                $res["groupe"]= array(
<<<<<<< HEAD
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
=======
                    "id" => $group["id_group"],
                    "nom" => $group["group_name"],
                    "status" => recup_status_by_user_and_group($_session["user"]["id"],$group["id_group"]),
                    "description" => $group["description"],
                    "avatar" => $group["avatar"],
                    "root" => $group["root"], //???
                    "nb_membres" => $group["root"],//nb_members($group["id"]),
                    "nb_messages" => 0,//(int) $group["nb_messages"],
                    "nb_files" => 0,//(int) $group["nb_files"],
                    "creator_id" => $group["id_creator"],
                    "lastUpdate" => $group["last_update"],
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
                        "remove_any_folder" => ($group["remove_any_folder"]==1),
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
>>>>>>> Matteo
                );
                }
        }
        break;
    case "search":
        if((int)$_post->nb_results <= 0){
            $res["error"]=2004; //Nombre de resulats invalide
        }
<<<<<<< HEAD
        elseif($_post->query!=NULL){
=======
        elseif(isset($_post->query)){
>>>>>>> Matteo
            $res["success"]=true;
            $group_data=recherche_par_nom_ou_description($_post->query, $_post->page_first, (int)$_post->nb_results);
            $groups=array();
            foreach($group_data as $group){
                $groups[]=array(
                    "id" => $group["id"],
                    "nom" => $group["name"],
                    "description" => $group["description"],
                    "avatar" => $group["avatar"],
<<<<<<< HEAD
                    "nb_membres"=> nb_members($group["id"]),//$group["nb_membres"],
=======
                    "nb_membres"=> $group["nb_members"],//nb_members($group["id"]),//$group["nb_membres"],
>>>>>>> Matteo
                    "nb_messages" => 0,//$group["nb_messages"]
                );
            }
            $res["results"] = $groups; 
        }else{
            $res["error"]=2005; //Recherche invalide(champ vide)
        }
        break;
    case "join":
<<<<<<< HEAD
        if($_post->id==NULL){
=======
        if(!isset($_post->id)){
>>>>>>> Matteo
            $res["error"]=0001;
        }elseif(empty(recup_group_id($_post->id))){
            $res["error"]=0001;
        }else{
            $res["success"]=apply_group($_post->id,$_session["user"]["id"]);
            $res["status"]="pending";
        }
        break;
    case "leave":
<<<<<<< HEAD
        if($_post->id==NULL){
=======
        if(!isset($_post->id)){
>>>>>>> Matteo
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
<<<<<<< HEAD
        if($_post->nom==NULL){
            $res["error"]=2100;
        }
        elseif ($_post->description==NULL) {
=======
        if(!isset($_post->nom)){
            $res["error"]=2100;
        }
        elseif (!isset($_post->description)) {
>>>>>>> Matteo
            $res["error"]=2100;
        }else{
            $res["success"]=true;
            $res["groupe"]= create_group($_post->nom, $_post->description, $_session["user"]["id"]);
        }
        break;
    case "kickUser":
<<<<<<< HEAD
        if($_post->id==NULL){
            $res["error"]=2001;
        }elseif ($_post->group==NULL) {
=======
        if(!isset($_post->id)){
            $res["error"]=2001;
        }elseif (!isset($_post->group)) {
>>>>>>> Matteo
            $res["error"]=2002;
        }elseif(empty(recup_user_id($_post->id))){
            $res["error"]=0001;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2003;
        }elseif(is_owner($_post->id,$_post->group)){ //Si la personne est proprietaire du groupe
            $res["error"]=0001; //Il faut transferer la possesion du groupe avant de le retirer du groupe
        }else{
            if ($_session["user"]["id"]==$_post->id) {
                $res["success"]=leave_group($_post->group,$_post->id);
             } else{
<<<<<<< HEAD
                /*if (!is_allowed($_session["user"]["id"],$_post->group,ROLE_KICK_USER)) {
                    $res["error"]=2004;
                }else{*/
                    $res["success"]=leave_group($_post->group,$_post->id);
                //}
=======
                if (!is_allowed($_session["user"]["id"],$_post->group,ROLE_KICK_USER)) {
                    $res["error"]=2004;
                }else{
                    $res["success"]=leave_group($_post->group,$_post->id);
                }
>>>>>>> Matteo
            }
        }
        break;
    case "acceptUser":
<<<<<<< HEAD
        if($_post->id==NULL){
            $res["error"]=2000;
        }elseif ($_post->group==NULL) {
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        /*}elseif (!is_allowed($_session["user"]["id"],$_post->group,ROLE_ACCEPT_USER)) {
            $res["error"]=2000;*/
        }else{
            $res["success"]=join_group($_post->group,$_post->id,$_session["user"]["id"]);
        }
        break;
    case "getRoles":
        if($_post->group_id==NULL){
=======
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
>>>>>>> Matteo
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
<<<<<<< HEAD
                    "read_message" => ($val["read_message"]==1),
=======
>>>>>>> Matteo
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
        break;
    case "editRoles":
<<<<<<< HEAD
        if($_post->group_id==NULL){
            $res["error"]=2000;
        }elseif(empty(recup_group_id($_post->group_id))){
            $res["error"]=2000; 
       /* }elseif(!is_allowed($_session["user"]["id"],$_post->group_id,ROLE_MANAGE_ROLE)){
            $res["error"]=2000;*/
        }else{
            if($_post->edited!=NULL){
                foreach($_post->edited as $value){
                    edit_role($value->id,$value->nom,(int)$value->read_message,(int)$value->write_message,(int)$value->remove_message,(int)$value->remove_any_message,(int)$value->download_file,
=======
        if(!isset($_post->group_id)){
            $res["error"]=2000;
        }elseif(empty(recup_group_id($_post->group_id))){
            $res["error"]=2000; 
        }elseif(!is_allowed($_session["user"]["id"],$_post->group_id,ROLE_EDIT_ROLE)){
            $res["error"]=2008;
        }else{
            if(isset($_post->edited)){
                foreach($_post->edited as $value){
                    edit_role($value->id,$value->nom,(int)$value->write_message,(int)$value->remove_message,(int)$value->remove_any_message,(int)$value->download_file,
>>>>>>> Matteo
                    (int)$value->create_file,(int)$value->rename_file,(int)$value->remove_file,(int)$value->remove_any_file,(int)$value->create_folder,(int)$value->rename_folder,(int)$value->remove_folder,(int)$value->remove_any_folder,
                    (int)$value->accept_user,(int)$value->kick_user,(int)$value->manage_role,(int)$value->edit_role,(int)$value->edit_name,(int)$value->edit_description);
                }
            }
<<<<<<< HEAD
            if($_post->removed!=NULL){
                delete_role_tab($_post->group_id,$_post->removed);
            }
            if($_post->added!=NULL){
                foreach($_post->added as $value){
                    create_role($value->nom,$_post->group_id,(int)$value->read_message,(int)$value->write_message,(int)$value->remove_message,(int)$value->remove_any_message,(int)$value->download_file,
                    (int)$value->create_file,(int)$value->rename_file,(int)$value->remove_file,(int)$value->remove_any_file,(int)$value->create_folder,(int)$value->rename_folder,(int)$value->remove_folder,(int)$value->remove_any_folder,
                    (int)$value->accept_user,(int)$value->kick_user,(int)$value->manage_role,(int)$value->edit_role,(int)$value->edit_name,(int)$value->edit_description);
                }
=======
            if(isset($_post->removed)){
                delete_role_tab($_post->group_id,$_post->removed);
            }
            if(isset($_post->added)){
                foreach($_post->added as $value){
                    create_role($_post->group_id,$value->nom,(int)$value->write_message,(int)$value->remove_message,(int)$value->remove_any_message,(int)$value->download_file,
                    (int)$value->create_file,(int)$value->rename_file,(int)$value->remove_file,(int)$value->remove_any_file,(int)$value->create_folder,(int)$value->rename_folder,(int)$value->remove_folder,(int)$value->remove_any_folder,
                    (int)$value->accept_user,(int)$value->kick_user,(int)$value->manage_role,(int)$value->edit_role,(int)$value->edit_name,(int)$value->edit_description);}
>>>>>>> Matteo
            }
            $res["success"]=true;
        }
        break;
<<<<<<< HEAD
    case "getMembers":
        if($_post->group){
=======
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
>>>>>>> Matteo
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }else{
            $res["success"]=true;
            $res["members"]=recup_membres($_post->group);
        }
        break;
    case "getApplications":
<<<<<<< HEAD
        if($_post->group==NULL){
=======
        if(!isset($_post->group)){
>>>>>>> Matteo
            $res["error"]=2000;
        }elseif (empty(recup_group_id($_post->group))) {
            $res["error"]=2000;
        }else{
            $res["success"]=true;
            $res["applications"]=recup_applications($_post->group);
        }
        break;
<<<<<<< HEAD
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
=======
    case "push":
        if(!isset($_post->id)){
            $res["error"]=0001;
        }elseif (empty(recup_group_id($_post->id))) {
            $res["error"]=0001;
        }elseif (!isset($_post->nom)) {
            $res["error"]=0001;
        }elseif (!isset($_post->description)) {
            $res["error"]=0001;
        }elseif (!is_allowed($_session["user"]["id"],$_post->id,ROLE_EDIT_NAME) || !is_allowed($_session["user"]["id"],$_post->id,ROLE_EDIT_DESCRIPTION)) {
>>>>>>> Matteo
            $res["error"]=0001;
        }else {
            $res["success"]=modif_groupe($_post->id,$_post->nom,$_post->description);
        }
        break;
<<<<<<< HEAD
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
=======
    case "dashboard":
        $dashboard=recup_dashboard($_post->group);
        $res["dashboard"]=array(
        "nb_members" => (int)$dashboard["nb_members"], 
        "nb_messages" => (int)$dashboard["nb_messages_files"]+(int)$dashboard["nb_messages_folder"],
        "nb_membres_rejoint" => $dashboard["nb_members_overall"],
        "nb_messages_sent" => (int)$dashboard["nb_messages_overall"],
        "nb_files_uploaded" => (int)$dashboard["nb_files_overall"],
        "nb_files" => (int)$dashboard["nb_files"],
        "nb_folders" => (int)$dashboard["nb_folders"],
        "nb_folders_created" => (int)$dashboard["nb_folders_overall"]
        );
        break;
>>>>>>> Matteo
    default: $res["error"] = 2000; //Erreur inconnu généré par groupe
    break;
}

echo json_encode($res);
?>