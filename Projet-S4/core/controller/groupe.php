<?php

$_post = json_decode(file_get_contents("php://input"));

$res = array(
    "success" => false,
    "error" => -1
);

$groups = array(
    array("id" =>  0, "nom" => "CCG",               "time" => 1613909869, "status" => "membre", "descr" => "Organisation gouvernementale d'enquêtes dans les cas de crimes liés aux goules.", "root" => 0, "nb_membres" => 10),
    array("id" =>  1, "nom" => "NERV",              "time" => 1613909869, "status" => "membre", "descr" => "Organisation privée. Notre mission est de défendre l'humanité face à la menace liée aux anges.", "root" => 0, "nb_membres" => 10),
    array("id" =>  2, "nom" => "Systeme Sibyl",     "time" => 1613954609, "status" => "membre", "descr" => "Organisation privée de gestion de la criminalité au Japon.", "root" => 0, "nb_membres" => 10),
    array("id" =>  3, "nom" => "Future Gadget Lab", "time" => 1613909869, "status" => "membre", "descr" => "Founded by Rintaro Okabe in the year 2010. Our main objective is the creation of Future Gadget that are to be used to plunge the world into chaos !", "root" => 0, "nb_membres" => 10),
    array("id" =>  4, "nom" => "SERN",              "time" => 1613909869, "status" => "membre", "descr" => "Global research organization secretly controlled by the Committee of 300.", "root" => 0, "nb_membres" => 10),
    array("id" =>  5, "nom" => "Dark Reunion",      "time" => 1613909869, "status" => "membre", "descr" => "A secret society which plans to weed out unnecessary elements from humankind.", "root" => 0, "nb_membres" => 10),
    array("id" =>  6, "nom" => "Groupama",          "time" => 1613909869, "status" => "ex", "descr" => "Toujours là pour toi.", "root" => 0, "nb_membres" => 10),
    array("id" =>  7, "nom" => "Le coté obscure de la force",          "time" => 1613909869, "status" => "ex", "descr" => "Rejoins nous on a des cookies.", "root" => 0, "nb_membres" => 10),
    array("id" =>  8, "nom" => "L'espada",          "time" => 1613909869, "status" => "ex", "descr" => "", "root" => 0, "nb_membres" => 13),
    array("id" =>  9, "nom" => "La brigade fantome","time" => 1613909869, "status" => "ex", "descr" => "Spécialisé dans diverses activités illicites : assassinats, trafics, corruption etc... ", "root" => 0, "nb_membres" => 13),
    array("id" => 10, "nom" => "SOS fantome",       "time" => 1613909869, "status" => "ex", "descr" => "Ghostbuster! If there's something strange in your neighborhood.. Who you gonna call? Ghostbusters!", "root" => 0, "nb_membres" => 10),
    array("id" => 11, "nom" => "L'Alliance des super-vilains",      "time" => 1613909869, "status" => "ex", "descr" => "", "root" => 0, "nb_membres" => 5),
    array("id" => 12, "nom" => "L'Akatsuki",        "time" => 1613909869, "status" => "ex", "descr" => "Organisation criminelle la plus activement recherchée dans le monde ninja. Le but initial du groupe créé par Yahiko, Pain et Konan était d'apporter la paix par le dialogue et non par la force. ", "root" => 0, "nb_membres" => 10),
    array("id" => 13, "nom" => "Ordre des Capitaines Corsaires",      "time" => 1613909869, "status" => "ex", "descr" => "Pirates très puissants ayant fait un marché avec les autorités.", "root" => 0, "nb_membres" => 10),
    array("id" => 14, "nom" => "Homonculus",        "time" => 1613909869, "status" => "ex", "descr" => "Humains artificiels qui portent les noms des sept péchés capitaux. Mission : assurer son plan de suprématie sur la race humaine.", "root" => 0, "nb_membres" => 10),
    array("id" => 15, "nom" => "L'Armée du Ruban Rouge",      "time" => 1613909869, "status" => "ex", "descr" => "Organisation militaire souhaitant acquérir les Sept Boules de Cristal.", "root" => 0, "nb_membres" => 10),
    array("id" => 16, "nom" => "Pourfendeurs",      "time" => 1613909869, "status" => "ex", "descr" => "Organisation qui a pour mission de protéger les hommes des démons et les guerriers qui la compose.", "root" => 0, "nb_membres" => 10),
    array("id" => 17, "nom" => "arbre aogiri",      "time" => 1613909869, "status" => "ex", "descr" => "", "root" => 0, "nb_membres" => 10),
    array("id" => 18, "nom" => "phamtom lord",      "time" => 1613909869, "status" => "ex", "descr" => "Fairy Tail", "root" => 0, "nb_membres" => 10),
    array("id" => 19, "nom" => "les taureaux noir",      "time" => 1613909869, "status" => "ex", "descr" => "Black clover", "root" => 0, "nb_membres" => 10),
);


function distanceLevenshtein($str1, $str2, $max_cout) {
    $len_str1 = strlen($str1);
    $len_str2 = strlen($str2);

    $prev_line = array();

    for ($j = 0; $j < $len_str2; $j++)
        $prev_line[] = $j;

    for ($i = 1; $i < $len_str1; $i++) {
        $line = array($i);
        for ($j = 1; $j < $len_str2; $j++) {
            $coutSubstitution = ($str1[$i] == $str2[$j]) ? 0 : 1;

            $line[] = min(
                $prev_line[$j] + 1,
                $line[$j - 1] + 1,
                $prev_line[$j - 1] + $coutSubstitution
            );

            if ($line[$j] >= $max_cout) return $max_cout; // exit if cout exceeds limit
        }
        $prev_line = $line;
    }

    return $prev_line[$len_str2 - 1];
}

function scoreQuery($query, $data) {
    $score = 0.0;

    $nb_mot_query = count($query);
    $nb_mot_data = count($data);

    for ($i = 0; $i < $nb_mot_query; $i++) {
        $mot_score = 0.0;
        $len_mot = strlen($query[$i]);
        $max_diff = .5 * $len_mot;

        for ($j = 0; $j < $nb_mot_data && $mot_score < 2.0; $j++)
            if ($query[$i] == $data[$j])
                $mot_score += 1.0;
            else if (stripos($data[$j], $query[$i]) !== false || stripos($query[$i], $data[$j]) !== false )
                $mot_score += 0.25;
            else if (strlen($data[$j]) >= 2) {
                $d = distanceLevenshtein($query[$i], $data[$j], $max_diff);
                if ($d < $max_diff)
                    $mot_score += ($len_mot - $d) / $len_mot;
            }

        $score += $mot_score;
    }

    return $score;
}



switch ($_post->action) {
    case "list":
        $res["success"] = true;
        $res["groups"] = array_filter($groups, function($v) {
            global $_post;
            return $v["time"] > $_post->time;
        });
    break;
    case "search":
        $res["success"] = true;

        $query = explode(" ", strtolower($_post->query));
        
        $scores = array_map(function ($g) {
            global $query;
            return scoreQuery(
                $query,
                explode(" ", strtolower($g["nom"]." ".$g["descr"]))
            );
        }, $groups);

        array_multisort($scores, SORT_DESC, $groups);

        $res["results"] = array_slice(array_filter($groups, function ($pos) {
            global $scores;
            return $scores[$pos] > 0;
        }, ARRAY_FILTER_USE_KEY), $_post->page_first, $_post->nb_results);
        $res["scores"] = $scores;
    default: $res["error"] = 0; break;
}

echo json_encode($res);
?>