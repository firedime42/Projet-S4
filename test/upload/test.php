<?php

$data = file_get_contents("php://input");

$id    = unpack("V", $data, 0)[1];
$psize = unpack("V", $data, 4)[1];
$bsize = unpack("V", $data, 8)[1];

var_dump($id, $psize, $bsize);