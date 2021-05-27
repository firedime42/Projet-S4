<?php

require dirname(__FILE__) . "/../session.php";

$path_base = dirname(__FILE__) . "/../../avatar";

$max_width = 1000;

$res = array( "success" => false );

if (!isset($_session['user'])) $res["error"] = 0;
else {
    $user_id = $_session['user']['id'];

    $img_path = $_FILES['avatar']['tmp_name'];
    $img_type = $_FILES['avatar']['type'];
    list($img_l_width, $img_l_height) = getimagesize($img_path);

    if ($img_l_width > $max_width || $img_l_height > $max_width) $res["error"] = 1;
    else {
        $img = null;
        
        switch($img_type) {
            case "image/png" : $img = imagecreatefrompng($img_path); break;
            case "image/jpeg" : $img = imagecreatefromjpeg($img_path); break;
            case "image/bmp" : $img = imagecreatefrombmp($img_path); break;
        }

        if (!$img) $res["error"] = 2;
        else {
            imagepng($img, "$path_base/$user_id"."_l.png");

            $min = min($img_l_width, $img_l_height);
            $ratio = 50 / $min;
            $img_s_width = $ratio * $img_l_width;
            $img_s_height = $ratio * $img_l_height;

            $img_small = imagescale($img, $img_s_width, $img_s_height, IMG_NEAREST_NEIGHBOUR);

            imagepng($img_small, "$path_base/$user_id"."_s.png");

            $res["success"] = true;
        }
    }
}

echo json_encode($res);
?>