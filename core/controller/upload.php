<?php

require_once dirname(__FILE__)."/../uploadFunction.php";
require_once dirname(__FILE__)."/../fileFunction.php";
require_once dirname(__FILE__) . "/../session.php";

# on parse les données de la requete
$_post = getUploadRequestData();

<<<<<<< HEAD
if($_post->bsize > 0 && !empty(recup_file_id($_post->id))){
=======
if($_post->bsize > 0 && !empty(recup_file_id($_post->id)) && is_int($_post->id)){
>>>>>>> Matteo
    # on ecrit dans le fichier
    writeInFile(dirname(__FILE__)."/../../files/".$_post->id.".bin", $_post->bdata);
}
?>