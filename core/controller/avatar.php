<?php
/**
 * 
 */
$MAX_AGE = 3600;// 1h
$path_base = dirname(__FILE__).'/../../avatar';
$user_id = 'default';
$size = 's';

// test de la valeur d'identifiant
if (isset($_GET['id']) && isNumeric($_GET['id'])) {
    $user_id = $_GET['id'];
}

// taille
if (isset($_GET['size']) && ($_GET['size'] == 's' || $_GET['size'] == 'l')) {
    $size = $_GET['size'];
}

// l'image existe
if (!file_exists($path_base.$user_id.'_'.$size.'.png')) {
    $user_id = 'default';
}

$etag = md5_file($path_base.$user_id.'_s.png');

function cacheMatch($etag) {
    return isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == "\"$etag\"";
}

if (cacheMatch($etag)) {
    // retourne le cache est identique
    header("HTTP/1.1 304 Not Modified");
    header("Cache-Control: private, max-age=$MAX_AGE");
    header("ETag: \"$etag\"");
    exit();
} else {
    header("Cache-Control: private, max-age=$MAX_AGE");
    header("ETag: \"$etag\"");
    header("Content-Type: image/png");

    echo file_get_contents($path_base.$user_id.'_'.$size.'.png');
}


?>