<?php
header("Content-Type: application/json");
require_once dirname(__FILE__)."/../groupFunction.php";
require_once dirname(__FILE__) . "/../session.php";
require_once dirname(__FILE__)."/../messageFunction.php";
require_once dirname(__FILE__)."/../roleFunction.php";
require_once dirname(__FILE__)."/../folderFunction.php";
$_post = json_decode(file_get_contents("php://input"));

$res = array(
    "success" => false
);


switch ($_post->action) {
	/*case "search":
		if((int)$_post->nb_results <= 0){
			$res["error"]=5000; //Nombre de resulats invalide
		}
		elseif(!isset($_post->query)){
			$res["error"]=5000; //Recherche invalide(champ vide)
		}else{
			$res["success"]=true;
			$messages_list=recherche_message($_post->query, $_post->page_first, (int)$_post->nb_results);
			$messages=array();
			foreach($messages_list as $message){
				$messages[]=array(
					"id" => $message["id"],
					"file" => $message["file_id"],
					"author" => $message["author"],				
				);
			}
			$res["results"] = $messages; 
		}
		break;*/
	case "update":
		if((int)$_post->resp_max <= 0){
			$res["error"]=5000; //Nombre de resulats invalides
		}elseif (!isset($_post->id)) {
			$res["error"]=5000;
		}elseif (empty(recup_chat($_post->id))) {
			$res["error"]=5000;
		}elseif (!isset($_post->oldest_message)) {
			$res["error"]=5000;
		}elseif (!isset($_post->newest_message)) {
			$res["error"]=5000;
		}elseif (!isset($_post->lastUpdate)) {
			$res["error"]=5000;
		}else{
			$res["success"]=true;
			read_chat($_post->id,$_session["user"]["id"]);
			$res["lastUpdate"] = (int) microtime(true) * 1000;
			$updates = getHeadEditedRemovedMessage($_post->id, $_post->lastUpdate, $_post->resp_max, $_post->newest_message, $_post->oldest_message);
			$res["edited"] = $updates["edited"];
			$res["removed"] = $updates["removed"];
			$res["head"] = $updates["head"];
		}
		break;
	case "loadMore":
		if((int)$_post->resp_max <= 0){
			$res["error"]=5000; //Nombre de resulats invalides
		}elseif (!isset($_post->id)) {
			$res["error"]=5000;
		}elseif (empty(recup_chat($_post->id))) {
			$res["error"]=5000;
		}elseif (!isset($_post->oldest_message)) {
			$res["error"]=5000;
		}elseif (!isset($_post->newest_message)) {
			$res["error"]=5000;
		}elseif (!isset($_post->lastUpdate)) {
			$res["error"]=5000;
		}elseif(!isset($_post->direction)){
			$res["error"]=5000;
		}else{
			$res["success"]=true;
			$res["lastUpdate"] = (int) microtime(true) * 1000;
			$messages = loadMore($_post->id, $_post->resp_max, $_post->newest_message, $_post->oldest_message, $_post->direction);
			$updates = getHeadEditedRemovedMessage($_post->id, $_post->lastUpdate, $_post->resp_max, $_post->newest_message, $_post->oldest_message);
			$res["messages"] = $messages;
			$res["edited"] = $updates["edited"];
			$res["removed"] = $updates["removed"];
			$res["head"] = $updates["head"];
		}
		break;
	case "remove":
		if (!isset($_post->msg_id)) {
			$res["error"] = 0002;
		}elseif (empty(recup_message($_post->msg_id))){
            $res["error"] = 3006; //Fichier inexistant
		}elseif(!is_allowed($_session["session"]["id"],recup_group_msg($_post->msg_id),ROLE_REMOVE_MESSAGE)){
			$res["error"] = 5000;
		}elseif (is_author($_session["session"]["id"],$_post->msg_id) && !is_allowed($_session["user"]["id"],recup_group_msg($_post->msg_id),ROLE_REMOVE_ANY_MESSAGE)) {
			$res["error"]=5008;
        }else {
            $res["success"] = supprimer_message($_post->msg_id);
        }
		break;
	case "edit":
		if (!isset($_post->msg_id)) {
			$res["error"] = 0002;
        }elseif(!isset($_post->content)){
            $res["error"] = 3005; //description vide
        }elseif (empty(recup_message($_post->msg_id))){
            $res["error"] = 3006; //Fichier inexistant
		}elseif(!is_allowed($_session["session"]["id"],recup_group_msg($_post->msg_id),ROLE_WRITE_MESSAGE)){
			$res["error"] = 5000;
        }else {
            $res["success"] = edit_message($_post->msg_id,$_post->content);
        }
		break;
	case "send":
		if(!isset($_post->id)){
			$res["error"]=5000;
		}elseif (!is_allowed($_session["user"]["id"],recup_group_chat($_post->id),ROLE_WRITE_MESSAGE)) {
			$res["error"]=5000;
		}elseif (empty(recup_chat($_post->id))) {
			$res["error"]=5000;
		}else{
			$res["success"]=true;
			$res["id"]=ajouter_message(recup_group_chat($_post->id),$_post->id,$_post->content,$_session["user"]["id"]);
			read_chat($_post->id, $_session["user"]["id"]);
		}
		break;
	default :
		$res["error"]=5000;
		break;
}

echo json_encode($res);

?>