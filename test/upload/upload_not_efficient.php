<?php

/**
 * 
 * Ne marche pas : erreur sur la ligne contentant $r->bsize
 * 
 * unpack(): Type V: not enough input, need 4, have 0 in E:\Megaport\Documents\GitHub\Projet-S4\test\upload\upload.php on line 13
 * 
 * 
 */




define('BLOC_SIZE', 4096); // 4 kio

/**
 * Fonction qui permet de lire les données d'entête
 * depend du model de donnée
 */
function readHeader($input) {
    $r = new class { public $id; public $bsize; };

    $r->id = unpack('V', fread($input, 4), 0)[1];
    $r->bsize = unpack('V', fread($input, 4), 4)[1];

    return $r;
}

function writePacket($header, $input, $path) {

    $output = fopen($path, 'a');

    $nb_blocs = floor($header->bsize / BLOC_SIZE);
    $nb_bytes_restant = $header->bsize - $nb_blocs * BLOC_SIZE;
    
    for ($i = 0; $i < $nb_blocs; $i++) {
        $bloc = fread($input, BLOC_SIZE);
        fwrite($output, $bloc);
    }
    
    if ($nb_bytes_restant > 0) {
        $bloc = fread($input, $nb_bytes_restant);
        fwrite($output, $bloc);
    }

    fclose($output);
    
}


$input = fopen('php://input', 'r');

$header = readHeader($input);

// faire les traitements et refuser si jamais
writePacket($header, $input, dirname(__FILE__).'/'.$header->id.'.data');
