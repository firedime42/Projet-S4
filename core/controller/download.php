<?php
$_post = json_decode(file_get_contents("php://input"));

require_once dirname(__FILE__)."/../downloadFunction.php";
require_once dirname(__FILE__)."/../fileFunction.php";
require_once dirname(__FILE__) . "/../session.php";

if($_post->id != NULL){
    $file_bdd= recup_file_id($_post->id);
    if(!empty($file_bdd)){
        $filepath = dirname(__FILE__)."/../../files/".$_post->id.".bin";
        $file = fopen($filepath, 'rb');
        header("Content-Type: ".$file["extension"]);
        streamData($file, 0, filesize($filepath) - 1);
        fclose($file);
    }
}
?>