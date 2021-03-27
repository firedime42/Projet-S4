<?php

define('ROLE_ADD_MESSAGE', "add_message");
define('ROLE_REMOVE_MESSAGE', "remove_message");
define('ROLE_ADD_FILE', "add_file");
define('ROLE_REMOVE_FILE', "remove_file");
define('ROLE_REMOVE_FILE_OTHERS', "remove_file_others");
define('ROLE_DOWNLOAD_FILES', "download_files");
define('ROLE_RENAME_FILES_OTHERS', "rename_files_others");
define('ROLE_DELETE_GROUP', "delete_group");
define('ROLE_SEE_MESSAGES', "see_messages");
define('ROLE_ADD_ROLES', "add_roles");
define('ROLE_DELETE_ROLE', "delete_role");
define('ROLE_SUPPR_ROLE', "suppr_role");
define('ROLE_INVITE_USER', "invite_user");
define('ROLE_REMOVE_USER', "remove_user");
define('ROLE_VALIDATE_USER', "validate_user");
define('ROLE_ADD_FOLDER', "add_folder");
define('ROLE_REMOVE_FOLDER', "remove_folder");
define('ROLE_DELETE_FOLDER', "delete_folder");
define('ROLE_RENAME_FILE', "rename_file");
define('ROLE_RENAME_FOLDER', "rename_folder");
define('ROLE_RENAME_GROUP', "rename_group");
define('ROLE_REWRITE_DESCRIPTION_GROUP', "rewrite_description_group");


function is_owner($user_id,$group_id){
	global $database;
	$query="SELECT * FROM `group` WHERE id= $group_id AND id_creator=$user_id";
	$res=mysqli_query($database,$query);
	$res=mysqli_num_rows($res);
	return $res==1;
}

function is_allowed($user_id, $group_id, $action){
    global $database;
    $query = "SELECT `$action` FROM `groupUser` gj JOIN `role` r ON gj.role = r.id WHERE gj.id = $user_id AND gj.group_id = $group_id";
    $res = mysqli_query($database, $query);
    $res = mysqli_fetch_array($res);
    return ($res == null) ? null : ($res[0] == '1');
}

function create_role_color($group_id,$nom_role,$couleur){
	global $database;
	$query = "INSERT INTO `role` group_id,name,couleur VALUES('$group_id','$nom_role','$couleur')";
	$res=mysqli_query($database,$query);
	return $res;
}

function create_role($group_id,$nom_role){
	global $database;
	$query = "INSERT INTO `role` group_id,name VALUES('$group_id','$nom_role')";
	$res=mysqli_query($database,$query);
	return $res;
}

function add_role($group_id,$user_id,$role_id){
	global $database;
	$query="UPDATE groupUser SET `role`=$role_id WHERE group_id=$group_id AND user_id=$user_id";
	$res=mysqli_query($database,$query);
	return $res;
}

function edit_role($id,$name,$color,$perms){
	global $database;
	$query = "UPDATE `role` SET name='$name', color = '$color'";
	foreach($perms as $key => $value){
		$query.=", $key='$value'";
	}
	$query.=" WHERE id = $id";
	$res=mysqli_query($database,$query);
	return $res;
}

function default_role($group_id) {
    global $database;
    $query="SELECT default_role FROM groupe WHERE id= $group_id";
    $result=mysqli_query($database,$query);
    $res=mysqli_fetch_array($result);
    return $res[0];
}

function remove_role($group_id,$user_id){
    $res= add_role($group_id,$user_id,default_role($group_id));
	return $res;
}

function delete_role($id){ // à finir
	global $database;
	$query="DELETE FROM role WHERE id=$id";
	$res=mysqli_query($database,$query);
	return $res;
}

function nb_roles_group($role){
    global $database;
    $query="SELECT * FROM groupUser WHERE `role`=$role";
    $result=mysqli_query($database,$query);
    $res=mysqli_num_rows($result);
    return $res;
}

function format_color($color) {
	return preg_match('/[0-9A-Fa-f]{6}/', $color);
}
?>