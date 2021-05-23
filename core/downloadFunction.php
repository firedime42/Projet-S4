<?php

function streamData($filepath, $from, $to, $bufferSize = 1024*64) {
    #set_time_limit(60);
    
    $pos = $from;
    while($pos < $to) {
        $bytesToRead = ($pos + $bufferSize <= $to) ? $bufferSize : $to - $pos;
        
        $file = fopen($filepath, 'rb');
        fseek($file, $pos, 0);
        $data = fread($file, $bytesToRead);
        fclose($file);

        echo $data;

        ob_flush();
        flush();

        $pos += $bytesToRead;
    }

    ob_end_flush();
}
?>