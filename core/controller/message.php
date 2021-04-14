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
	case "send":
		if(!isset($_post->id)){
			$res["error"]=5000;
		}elseif (!isset($_post->group_id)) {
			$res["error"]=5000;
		}elseif (empty(recup_group_id($_post->group_id))) {
			$res["error"]=5000;
		}elseif (!is_allowed($_session["user"]["id"],$_post->group_id,ROLE_WRITE_MESSAGE)) {
			$res["error"]=5000;
		}elseif (!isset($_post->file)) {
			$res["success"]=ajouter_message_group($_post->message,$_post->group,$_session["user"]["id"]);
		}elseif (empty(recup_file_id($_post->file))) {
			$res["error"]=5000;
		}else{
			$res["success"]=ajouter_message($_post->message,$_post->file,$_post->group,$_session["user"]["id"]);
		}
		break;
	case "delete":
		if(!isset($_post->id)){
			$res["error"]=5000;
		}elseif (!isset($_post->user)) {
			$res["error"]=5000;
		}elseif ($_post->user==$_session["user"]["id"]) {
			if(is_allowed($_session["user"]["id"],$_post->group_id,ROLE_REMOVE_MESSAGE)){
			$res["success"]=supprimer_message($_post->id);
			}else {
				$res["error"]=5000;
			}
		}elseif (!is_allowed($_session["user"]["id"],$_post->group_id,ROLE_REMOVE_ANY_MESSAGE)) {
			$res["error"]=5000;
		}else {
			$res["succes"]=supprimer_message($_post->id);
		}
		break;
	case "getMessages":
		if(isset($_post->file)){
			$res["success"]=true;
			$list_message=recup_messages_file($_post->file);
            $messages=array();
            foreach($list_message as $message){
                $messages[]=array(
                    "id" => $message["id"],
					"author" => $message["author"],
					"message" => $message["message"],
					"file_id" => $message["file_id"],
					"group_id" => $message["group_id"]
                );
            }
            $res["messages"] = $messages; 
		}elseif (isset($_post->group)) {
			$res["success"]=true;
			$list_message=recup_messages_group($_post->group);
            $messages=array();
            foreach($list_message as $message){
                $messages[]=array(
					"id" => $message["id"],
					"author" => $message["author"],
					"message" => $message["message"],
					"file_id" => $message["file_id"],
					"group_id" => $message["group_id"]
                );
            }
            $res["messages"] = $messages; 
		}
		break;
	case "info":
		if(!isset($_post->id)) 
			$res["error"]=2; //id vide
        else{
            $message=recup_message($_post->id);
            if (empty($message)) $res["error"]=2002; //message inexistant
            elseif( $_post->time==$message["last_update"]){
            $res["success"]=true;
            $res["message"]=NULL;
            }
            else {
                $res["success"]=true;
                $res["message"]= array(
                    "id" => $message["id"],
					"author" => $message["author"],
					"message" => $message["message"],
					"file_id" => $message["file_id"],
					"group_id" => $message["group_id"]
                );
            }
        }
		break;
	case "edit":
        if (!isset($_post->id)) {
            $res["error"] = 0002; //id vide
		}elseif (!isset($_post->group)) {
			$res["error"] = 0002;
        }elseif(!isset($_post->message)){
            $res["error"] = 3005; //description vide
        }elseif (empty(recup_message($_post->id))){
            $res["error"] = 3006; //Fichier inexistant
		}elseif(!is_author($_session["session"]["id"],$_post->group)){
			$res["error"] = 5000;
        }else {
            $res["success"] = edit_message($_post->id,$_post->message);
        }
        break;
	case "search":
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
		break;
	default :
		$res["error"]=5000;
		break;
}


	
