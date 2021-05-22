<?php

require_once dirname(__FILE__) . "/../session.php";

$path_base = dirname(__FILE__) . "/../../avatar";

$max_width = 500;

if (isset($_session['user'])) {
    $user_id = $_session['user']['id'];

    list($img_l_width, $img_l_height) = getimagesize('php://input');

    if ($img_l_width > $max_width || $img_l_height > $max_width) exit();

    $img = imagecreatefrompng('php://input');

    imagepng($img, "$path_base/$user_id\_l.png");

    //$img_small = imagecreatetruecolor(50, 50);
    //imagecopyresampled($img_small, $img, 0, 0, 0, 0, 50, 50, $img_l_width, $img_l_height);

    $img_small = imagescale($img, 50, 50, IMG_NEAREST_NEIGHBOUR);

    imagepng($img_small, "$path_base/$user_id\_s.png");
}