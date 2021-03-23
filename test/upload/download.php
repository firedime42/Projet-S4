<?php

$_post = json_decode(file_get_contents("php://input"));


function streamData($file, $start, $end, $bufferSize = 1024 * 64) {
    $i = $start;

    set_time_limit(0);

    while(!feof($file) && $i <= $end) {
        $bytesToRead = $bufferSize;

        if(($i+$bytesToRead) > $end) $bytesToRead = $end - $i + 1;

        $data = fread($file, $bytesToRead);

        echo $data;
        flush();

        $i += $bytesToRead;
    }

}

$filepath = dirname(__FILE__)."/1615305803.mp4";
$file = fopen($filepath, 'rb');

header("Content-Type: video/mp4");

streamData($file, 0, filesize($filepath) - 1);

fclose($file);