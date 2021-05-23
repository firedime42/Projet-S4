<?php
//$_post = json_decode(file_get_contents("php://input"));

require_once dirname(__FILE__)."/../downloadFunction.php";
require_once dirname(__FILE__)."/../fileFunction.php";
require_once dirname(__FILE__) . "/../session.php";

$MAX_AGE = 24 * 3600; // 24h

function generateEtag($file) {
    return md5($file['id'].'.'.$file["last_update"].".".time());
}

function cacheMatch($etag) {
    return isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == "\"$etag\"";
}

if ($_GET['file_id'] != NULL) {
    $file_bdd = recup_file_id($_GET['file_id']);
    if(!empty($file_bdd)){
        $etag = generateEtag($file_bdd);

        // test de l'etag (version du cache)
        if (!isset($_SERVER['HTTP_RANGE']) && cacheMatch($etag)) {
            header("HTTP/1.1 304 Not Modified");
            header("Cache-Control: private, max-age=$MAX_AGE");
            header("ETag: \"$etag\"");
            exit();
        } else {
            $filepath = dirname(__FILE__)."/../../files/".$_GET['file_id'].".bin";
            $size = filesize($filepath);

            $from = 0;
            $to = $size;

            if (isset($_SERVER['HTTP_RANGE'])) {
                $range_field = explode('=', $_SERVER['HTTP_RANGE']);

                if ($range_field[0] != 'bytes') {
                    // on ne peut gérer que les interval de type bytes.
                    header('HTTP/1.1 400 Bad Request');
                    exit();
                }

                $range = explode('-', $range_field[1]);
                $from = (int) $range[0];
                $to = (isset($range[1]) && $range[1] != "") ? ((int) $range[1]) + 1 : min($size, $from + 512 * 1024);
                
                header('HTTP/1.1 206 Partial Content');
                $_to = $to - 1;
                header("Content-Range: bytes {$from}-{$_to}/{$size}");
            }

            header('Content-Length: '.($to - $from));

            header("Cache-Control: private, max-age=$MAX_AGE");
            header("ETag: \"$etag\"");

            header('Accept-Ranges: bytes');
            header("Content-Type: ".$file_bdd["extension"]);

            streamData($filepath, $from, min($to, $size));
        }
    }
}
?>