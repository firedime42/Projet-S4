<?php

require_once dirname(__FILE__)."/../uploadFunction.php";
require_once dirname(__FILE__)."/../fileFunction.php";
require_once dirname(__FILE__) . "/../session.php";

# on parse les données de la requete
$_post = getUploadRequestData();

if($_post->bsize<=0){
    $res["error"]=0001;
}elseif (empty(recup_file_id($_post->id))) {
    $res["error"]=0001;
}elseif (is_int($_post->id)) {
    $res["error"]=0001;
}else{
    # on ecrit dans le fichier
    writeInFile(dirname(__FILE__)."/../../files/".$_post->id.".bin", $_post->bdata);
}
?>