<?php

define('ROLE_READ_MESSAGE',"read_message"); //???
define('ROLE_WRITE_MESSAGE',"write_message");
define('ROLE_REMOVE_MESSAGE',"remove_message");
define('ROLE_REMOVE_ANY_MESSAGE',"remove_any_message");
// file
define('ROLE_DOWNLOAD_FILE',"download_file");
define('ROLE_CREATE_FILE',"create_file");
define('ROLE_RENAME_FILE',"rename_file");
define('ROLE_REMOVE_FILE',"remove_file");
define('ROLE_REMOVE_ANY_FILE',"remove_any_file");
// folder
define('ROLE_CREATE_FOLDER',"create_folder");
define('ROLE_RENAME_FOLDER',"rename_folder");
define('ROLE_REMOVE_FOLDER',"remove_folder");
define('ROLE_REMOVE_ANY_FOLDER',"remove_any_folder");
// user
define('ROLE_ACCEPT_USER',"accept_user");
define('ROLE_KICK_USER',"kick_user");
define('ROLE_MANAGE_ROLE',"manage_role");
// role
define('ROLE_EDIT_ROLE',"edit_role");
// groupe
define('ROLE_EDIT_NAME',"edit_name");
define('ROLE_EDIT_DESCRIPTION',"edit_description");


function is_owner($user_id,$group_id){
	global $database;
	$query="SELECT * FROM `group` WHERE id= $group_id AND id_creator=$user_id";
	$res=mysqli_query($database,$query);
	$res=mysqli_num_rows($res);
	return $res==1;
}

function is_allowed($user_id, $group_id, $action){
    global $database;
    $query = "SELECT r.$action FROM `groupUser` gj JOIN `role` r ON gj.role_id = r.id WHERE gj.user_id = $user_id AND gj.group_id = $group_id";
    $res = mysqli_query($database, $query);
    $res = mysqli_fetch_array($res);
    return $res[0]== '1';
}

function create_role_color($group_id,$nom_role,$couleur,$read_message,$write_message,$remove_message,$remove_any_message,$download_file,
$create_file,$rename_file,$remove_file,$remove_any_file,$create_folder,$rename_folder,$remove_folder,$remove_any_folder,
$accept_user,$kick_user,$manage_role,$edit_role,$edit_name,$edit_description){
	global $database;
	$query = "INSERT INTO `role` (group_id,name,color,read_message,write_message,remove_message,remove_any_message,download_file,
	create_file,rename_file,remove_file,remove_any_file,create_folder,rename_folder,remove_folder,remove_any_folder,
	accept_user,kick_user,manage_role,edit_role,edit_name,edit_description) VALUES($group_id,'$nom_role','$couleur',$read_message,$write_message,$remove_message,$remove_any_message,$download_file,
	$create_file,$rename_file,$remove_file,$remove_any_file,$create_folder,$rename_folder,$remove_folder,$remove_any_folder,
	$accept_user,$kick_user,$manage_role,$edit_role,$edit_name,$edit_description)";
	$res=mysqli_query($database,$query);
	return $res;
}

function create_role($group_id,$nom_role,$read_message,$write_message,$remove_message,$remove_any_message,$download_file,
$create_file,$rename_file,$remove_file,$remove_any_file,$create_folder,$rename_folder,$remove_folder,$remove_any_folder,
$accept_user,$kick_user,$manage_role,$edit_role,$edit_name,$edit_description){
	global $database;
	$query = "INSERT INTO `role` (group_id,name,read_message,write_message,remove_message,remove_any_message,download_file,
	create_file,rename_file,remove_file,remove_any_file,create_folder,rename_folder,remove_folder,remove_any_folder,
	accept_user,kick_user,manage_role,edit_role,edit_name,edit_description) VALUES($group_id,'$nom_role',$read_message,$write_message,$remove_message,$remove_any_message,$download_file,
	$create_file,$rename_file,$remove_file,$remove_any_file,$create_folder,$rename_folder,$remove_folder,$remove_any_folder,
	$accept_user,$kick_user,$manage_role,$edit_role,$edit_name,$edit_description)";
	$res=mysqli_query($database,$query);
	return $res;
}

function add_role($group_id,$user_id,$role_id){
	global $database;
	$query="UPDATE groupUser SET `role`=$role_id WHERE group_id=$group_id AND user_id=$user_id";
	$res=mysqli_query($database,$query);
	return $res;
}

function edit_role($id,$name,$read_message,$write_message,$remove_message,$remove_any_message,$download_file,
$create_file,$rename_file,$remove_file,$remove_any_file,$create_folder,$rename_folder,$remove_folder,$remove_any_folder,
$accept_user,$kick_user,$manage_role,$edit_role,$edit_name,$edit_description){
	global $database;
	$query = "UPDATE `role` SET name='$name',read_message=$read_message,write_message=$write_message,remove_message=$remove_message,remove_any_message=$remove_any_message,download_file=$download_file,
	create_file=$create_file,rename_file=$rename_file,remove_file=$remove_file,remove_any_file=$remove_any_file,create_folder=$create_folder,rename_folder=$rename_folder,remove_folder=$remove_folder,remove_any_folder=$remove_any_folder,
	accept_user=$accept_user,kick_user=$kick_user,manage_role=$manage_role,edit_role=$edit_role,edit_name=$edit_name,edit_description=$edit_description WHERE id = $id";
	$res=mysqli_query($database,$query);
	return $res;
}

function default_role($group_id) {
    global $database;
    $query="SELECT default_role FROM `group` WHERE id= $group_id";
    $result=mysqli_query($database,$query);
    $res=mysqli_fetch_array($result);
    return $res[0];
}

function remove_role($group_id,$user_id){
    $res= add_role($group_id,$user_id,default_role($group_id));
	return $res;
}

function delete_role_tab($group_id,$ids){
	global $database;
	$idslist=implode(',',$ids);
	$query="DELETE FROM role WHERE id IN ($idslist)";
	$res=mysqli_query($database,$query);
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

function recup_roles($group_id){
	global $database;
	$query="SELECT * FROM `role` WHERE group_id=$group_id";
	$res=mysqli_query($database,$query);
	$list_roles=array();
	while($row=mysqli_fetch_assoc($res))
		$list_roles[]=$row;
	return $list_roles;
}

function format_color($color) {
	return preg_match('/[0-9A-Fa-f]{6}/', $color);
}
?>