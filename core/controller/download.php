<?php
$_post = json_decode(file_get_contents("php://input"));

require_once dirname(__FILE__)."/../downloadFunction.php";
require_once dirname(__FILE__)."/../fileFunction.php";
require_once dirname(__FILE__)."/../roleFunction.php";
require_once dirname(__FILE__)."/../groupFunction.php";
require_once dirname(__FILE__) . "/../session.php";
$res=array(
    "success"=>false
);
if(!isset($_post->id)){
    $res["error"]=0001;
}elseif (!is_allowed($_session["user"]["id"],recup_group_file($_post->id),ROLE_DOWNLOAD_FILE)) {
    $res["error"]=0008;
}elseif (empty(recup_file_id($_post->id))) {
    $res["error"]=0002;
}else{
        $filepath = dirname(__FILE__)."/../../files/".$_post->id.".bin";
        $file = fopen($filepath, 'rb');
        header("Content-Type: ".$file["extension"]);
        streamData($file, 0, filesize($filepath) - 1);
        fclose($file);
        $res["success"]=true;
    }

/*
if($_post->id != NULL){
    $file_bdd= recup_file_id($_post->id);
    if(!empty($file_bdd)){
        $filepath = dirname(__FILE__)."/../../files/".$_post->id.".bin";
        $file = fopen($filepath, 'rb');
        header("Content-Type: ".$file["extension"]);
        streamData($file, 0, filesize($filepath) - 1);
        fclose($file);
    }
}*/
?>