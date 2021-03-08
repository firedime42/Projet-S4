<?php
require_once("groupe.php");
$_post = json_decode(file_get_contents("php://input"));

$res = array(
    "success" => false,
    "error" => -1
);

$groups = [
    array("id" =>  0, "nom" => "CCG",                            "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Organisation gouvernementale d'enquêtes dans les cas de crimes liés aux goules."),
    array("id" =>  1, "nom" => "NERV",                           "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Organisation privée. Notre mission est de défendre l'humanité face à la menace liée aux anges."),
    array("id" =>  2, "nom" => "Systeme Sibyl",                  "lastUpdate" => 1613954609, "root" => 0, "nb_membres" => 10, "descr" => "Organisation privée de gestion de la criminalité au Japon."),
    array("id" =>  3, "nom" => "Future Gadget Lab",              "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Founded by Rintaro Okabe in the year 2010. Our main objective is the creation of Future Gadget that are to be used to plunge the world into chaos !"),
    array("id" =>  4, "nom" => "SERN",                           "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Global research organization secretly controlled by the Committee of 300."),
    array("id" =>  5, "nom" => "Dark Reunion",                   "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "A secret society which plans to weed out unnecessary elements from humankind."),
    array("id" =>  6, "nom" => "Groupama",                       "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Toujours là pour toi."),
    array("id" =>  7, "nom" => "Le coté obscure de la force",    "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Rejoins nous on a des cookies."),
    array("id" =>  8, "nom" => "L'espada",                       "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 13, "descr" => ""),
    array("id" =>  9, "nom" => "La brigade fantome",             "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 13, "descr" => "Spécialisé dans diverses activités illicites : assassinats, trafics, corruption etc... "),
    array("id" => 10, "nom" => "SOS fantome",                    "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Ghostbuster! If there's something strange in your neighborhood.. Who you gonna call? Ghostbusters!"),
    array("id" => 11, "nom" => "L'Alliance des super-vilains",   "lastUpdate" => 1613909869, "root" => 0, "nb_membres" =>  5, "descr" => ""),
    array("id" => 12, "nom" => "L'Akatsuki",                     "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Organisation criminelle la plus activement recherchée dans le monde ninja. Le but initial du groupe créé par Yahiko, Pain et Konan était d'apporter la paix par le dialogue et non par la force. "),
    array("id" => 13, "nom" => "Ordre des Capitaines Corsaires", "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Pirates très puissants ayant fait un marché avec les autorités."),
    array("id" => 14, "nom" => "Homonculus",                     "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Humains artificiels qui portent les noms des sept péchés capitaux. Mission : assurer son plan de suprématie sur la race humaine."),
    array("id" => 15, "nom" => "L'Armée du Ruban Rouge",         "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Organisation militaire souhaitant acquérir les Sept Boules de Cristal."),
    array("id" => 16, "nom" => "Pourfendeurs",                   "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Organisation qui a pour mission de protéger les hommes des démons et les guerriers qui la compose."),
    array("id" => 17, "nom" => "arbre aogiri",                   "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => ""),
    array("id" => 18, "nom" => "phamtom lord",                   "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Fairy Tail"),
    array("id" => 19, "nom" => "les taureaux noir",              "lastUpdate" => 1613909869, "root" => 0, "nb_membres" => 10, "descr" => "Black clover")
];

$groupeJoin = [
    array("id" => 0, "user_id" => 0, "groupe_id" =>  0, "status" =>   "membre"),
    array("id" => 1, "user_id" => 0, "groupe_id" =>  1, "status" =>   "membre"),
    array("id" => 2, "user_id" => 0, "groupe_id" =>  5, "status" =>   "membre"),
    array("id" => 3, "user_id" => 0, "groupe_id" =>  6, "status" =>   "membre"),
    array("id" => 4, "user_id" => 0, "groupe_id" => 14, "status" =>   "membre"),
    array("id" => 5, "user_id" => 0, "groupe_id" => 13, "status" =>   "invite"),
    array("id" => 6, "user_id" => 0, "groupe_id" =>  7, "status" => "candidat")
];

function emulateJoin($tableA, $tableB, $propertyA, $propertyB, $propsA, $propsB) {
    $result = [];
    foreach ($tableA as $a) {
        foreach ($tableB as $b) {
            if ($a[$propertyA] == $b[$propertyB]) {
                $newrow = array();
                foreach ($propsA as $prop => $name) $newrow[$name] = $a[$prop];
                foreach ($propsB as $prop => $name) $newrow[$name] = $b[$prop];
                $result[] = $newrow;
            }
        }
    }
    return $result;
}

function scoreQuery($query, $data) {
    $score = 0.0;

    $nb_mot_query = count($query);
    $nb_mot_data = count($data);

    for ($i = 0; $i < $nb_mot_query; $i++) {
        $mot_score = 0.0;

        for ($j = 0; $j < $nb_mot_data && $mot_score < 2.0; $j++)
            if ($query[$i] == $data[$j])
                $mot_score += 1.0;
            else if (stripos($data[$j], $query[$i]) !== false || stripos($query[$i], $data[$j]) !== false )
                $mot_score += 0.25;

        $score += $mot_score;
    }

    return $score;
}



switch ($_post->action) {
    case "list":
        break;
    case "info":
        if($_post->id==NULL) $res["error"]=4000;
        elseif (cherche_groupe_id($post->id)) $res["error"]=4000;
        elseif ($post->time==NULL) $res["error"]=4000;
        elseif( $post->time==id_to_lastUpdate($_post->id)){
            $res["success"]=true;
            $res["groupe"]=NULL;
        }
        else {
            $res["success"]=true;
            $res["groupe"]= array(
                "id" => $_post->id,
                "nom" => id_to_nom($_post->id),
                "status" => id_to_status($_post->id),
                "descr" => id_to_descr($_post->id),
                "avatar" => id_to_avatar($_post->id),
                "root" => id_to_root($_post->id),
                "nb_membres" => id_to_membres($_post->id),
                "nb_messages" => id_to_messages($_post->id),
                "nb_files" => id_to_nbFiles($_post->id),
                "lastUpdate" => id_to_lastUpdate($_post->id)
            );
            }
        break;
    case "search":
        if($_post->nb_results<=0) $res["error"]=4001;
        elseif($_post->query!=NULL){
            for($i=0;$i<nb_groups();$i++){


                $group=array(
                    "id" => $id,
                    "nom" => id_to_nom($id),
                    "descr" => id_to_descr($id),
                    "avatar" => id_to_avatar($id),
                    "nb_membre" => id_to_membres($id)
                );
                $groups[$i]=$group;
            }
            
            $res["results"]=$groups;
        }else{
            $res["error"]=4000;
        }
        break;
    /*case "list":
        $res["success"] = true;

        $mesgroupes = array_filter($groupeJoin, function ($e) { return $e['user_id'] == 0; });
        $mesgroupes = emulateJoin(
            $mesgroupes, $groups, // les deux tables
            "groupe_id", "id",    // les deux propriétés à comparer
            array("status" => "status"), // les propriétés de la table A
            array("id" => "id", "nom" => "nom", "lastUpdate" => "lastUpdate") // les propriétés de la table B
        );

        $res["groups"] = array_filter($mesgroupes, function($v) {
            global $_post;
            return $v["lastUpdate"] > $_post->time;
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

        // faire simple recherche avec un "like" et un "or" ou quelque chose pour compter le nombre d'occurence...

        //$res["scores"] = $scores; // debug
    break;
    case "info":
        $r_groupe = array_values(array_filter($groups, function ($g) {
            global $_post;
            return $g['id'] == $_post->id;
        }));

        $r_groupejoin = array_values(array_filter($groupeJoin, function ($g) {
            global $_post;
            return $g['groupe_id'] == $_post->id;
        }));


        if (empty($r_groupe)) {
            $res["error"] = 2100;
        } else {
            $res["success"] = true;
            $groupe = $r_groupe[0];

            if ($groupe["lastUpdate"] > $_post->time) {// si la version cliente est plus vielle que la version sur le serveur
                $res["groupe"] = $groupe;
                if (empty($r_groupejoin)) $res["groupe"]["status"] = "left";
                else $res["groupe"]["status"] = $r_groupejoin[0]["status"];
            } else 
                $res["groupe"] = null;

        }

    break;*/
    default: $res["error"] = 0; break;
}

echo json_encode($res);
?>