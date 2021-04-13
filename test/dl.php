<?php

function distanceLevenshtein($str1, $str2) {
    $len_str1 = strlen($str1);
    $len_str2 = strlen($str2);

    $d = array();

    for ($i = 0; $i < $len_str1; $i++) {
        $d[] = array();
        $d[$i][] = $i;
    }

    for ($j = 1; $j < $len_str2; $j++) {
        $d[0][] = $j;
    }

    for ($i = 1; $i < $len_str1; $i++)
        for ($j = 1; $j < $len_str2; $j++) {
            $coutSubstitution = ($str1[$i] == $str2[$j]) ? 0 : 1;

            $d[$i][$j] = min(
                $d[$i - 1][$j] + 1,
                $d[$i][$j - 1] + 1,
                $d[$i - 1][$j - 1] + $coutSubstitution
            );
        }

    return $d[$len_str1 - 1][$len_str2 - 1];
}

$str1 = "toi est coucou";
$str2 = "c'est moi coucou et toi ?";

echo distanceLevenshtein($str1, $str2) - strlen($str2) + strlen($str1);
