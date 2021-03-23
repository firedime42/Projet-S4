<?php

function getUploadRequestData() {
    $r = new class { public $id; public $bsize; public $bdata; };

    # on récupère les données envoyés par l'utilisateur
    $input_data = file_get_contents('php://input');

    # on sépare les données
    $r->id = unpack('V', $input_data, 0)[1];
    $r->bsize = unpack('V', $input_data, 4)[1];
    $r->s_octet = unpack('V', $input_data, 8)[1];
    $r->bdata = substr($input_data, 12, 12 + $r->bsize);

    # on retourne un objet contenant les données
    return $r;
}

function writeInFile($filename, $data) {
    # on ouvre le fichier en mode ajout
    $file = fopen($filename, 'ab');

    # on ajoute le bloc de données à la fin du fichier
    fwrite($file, $data);

    # on ferme le fichier
    fclose($file);
}

$_post = getUploadRequestData();

writeInFile(dirname(__FILE__)."/".$request->id.".png", $request->bdata);