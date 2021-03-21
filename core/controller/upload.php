<?php

session_start();
require_once dirname(__FILE__)."/../uploadFunction.php";
require_once dirname(__FILE__)."/../fileFunction.php";

# on parse les données de la requete
$_post = getUploadRequestData();

if($_post->bsize > 0 && !empty(recup_file_id($_post->id))){
    # on ecrit dans le fichier
    writeInFile(dirname(__FILE__)."/../../files/".$_post->id.".bin", $_post->bdata);
}
?>