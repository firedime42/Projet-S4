<?php

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
?>