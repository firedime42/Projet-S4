<?php
header("Content-Type: application/json");
require_once dirname(__FILE__)."/../groupFunction.php";
require_once dirname(__FILE__) . "/../session.php";
require_once dirname(__FILE__)."/../messageFunction.php";
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
			$res["error"]=5000; //Nombre de resulats invalide
		}elseif (!isset($_post->id)) {
			$res["error"]=5000;
		}elseif (empty(recup_chat($_post->id))) {
			$res["error"]=5000;
		}elseif (!isset($_post->oldest_message)) {
			$res["error"]=5000;
		}elseif (!isset($_post->newest_message)) {
			$res["error"]=5000;
		}elseif (isset($_post->lastUpdate)) {
			$res["error"]=5000;
		}else{
			$res["success"]=true;
			$messages_list=edition_suppresion($_post->id,$_post->oldest_message,$_post->newest_message,$_post->lastUpdate,0,0);
			$res["edited"]=$messages_list["edited"];
			$res["removed"]=$messages_list["removed"];
			$res["head"]=recherche_messages($_post->id,$_post->lastUpdate,$_post->resp_max,0,0,0);
			$res["lastUpdate"] = microtime(true); 
		}
		break;
	case "loadMore":
		if((int)$_post->resp_max <= 0){
			$res["error"]=5000; //Nombre de resulats invalide
		}elseif (!isset($_post->id)) {
			$res["error"]=5000;
		}elseif (empty(recup_chat($_post->id))) {
			$res["error"]=5000;
		}elseif (!isset($_post->oldest_message)) {
			$res["error"]=5000;
		}elseif (!isset($_post->newest_message)) {
			$res["error"]=5000;
		}elseif (isset($_post->lastUpdate)) {
			$res["error"]=5000;
		}elseif(isset($_post->direction)){
			$res["error"]=5000;
		}else{
			$res["success"]=true;
			$messages_list=edition_suppresion($_post->id,$_post->oldest_message,$_post->newest_message,$_post->lastUpdate,$_post->direction,$_post->resp_max);
			$res["edited"]=$messages_list["edited"];
			$res["removed"]=$messages_list["removed"];
			$res["head"]=recherche_messages($_post->id,$_post->lastUpdate,$_post->resp_max,$_post->direction);
			$res["lastUpdate"] = microtime(true); 
		}
		break;
	case "remove":
		/*if (!isset($_post->group_id)) {
            $res["error"] = 0002; //id vide
		}else*/if (!isset($_post->msg_id)) {
			$res["error"] = 0002;
		/*}elseif (empty(recup_group_id($_post->group_id))) {
			$res["error"] = 3005; //description vide*/
        }elseif (empty(recup_message($_post->msg_id))){
            $res["error"] = 3006; //Fichier inexistant
		/*}elseif(!is_allowed($_session["session"]["id"],$_post->group_id,ROLE_REMOVE_MESSAGE)){
			$res["error"] = 5000;*/
        }else {
            $res["success"] = supprimer_message($_post->msg_id);
        }
		break;
	case "edit":
		/*if (!isset($_post->group_id)) {
            $res["error"] = 0002; //id vide
		}else*/if (!isset($_post->msg_id)) {
			$res["error"] = 0002;
        }elseif(!isset($_post->content)){
            $res["error"] = 3005; //description vide
		/*}elseif (empty(recup_group_id($_post->group_id))) {
			$res["error"] = 3005; //description vide*/
        }elseif (empty(recup_message($_post->msg_id))){
            $res["error"] = 3006; //Fichier inexistant
		/*}elseif(!is_allowed($_session["session"]["id"],$_post->group_id,ROLE_WRITE_MESSAGE)){
			$res["error"] = 5000;*/
        }else {
            $res["success"] = edit_message($_post->msg_id,$_post->content);
        }
		break;
	case "send":
		if(!isset($_post->id)){
			$res["error"]=5000;
		/*}elseif (!isset($_post->group_id)) {
			$res["error"]=5000;
		}elseif (empty(recup_group_id($_post->group_id))) {
			$res["error"]=5000;
		}elseif (!is_allowed($_session["user"]["id"],$_post->group_id,ROLE_WRITE_MESSAGE)) {
			$res["error"]=5000;*/
		}elseif (empty(recup_chat($_post->id))) {
			$res["error"]=5000;
		}else{
			$res["success"]=true;
			$res["id"]=ajouter_message($_post->id,$_post->message,$_session["user"]["id"]);
		}
		break;
	default :
		$res["error"]=5000;
		break;
}


	
