<?php

require_once dirname(__FILE__)."/../core/folderFunction.php";
require_once dirname(__FILE__)."/../core/roleFunction.php";
require_once dirname(__FILE__)."/../core/groupFunction.php";

# creation du groupe
$group_id = create_group("Le teste", "", 5);

# recuperation de l'identifiant du dossier root
$root_id = recup_group_id($group_id)["root"];

# creer un sous dossier
$A = create_folder($group_id, "A", "", $root_id);
$B = create_folder($group_id, "B", "", $root_id);

# creer un sous-sous dossier
$C = create_folder($group_id, "C", "", $B);

# recuperer le chemin de C:
echo "<div>chemin du dossier C :</div>";

$path_C = getFolderPath($C);

var_dump($path_C);

# recuperer des infos sur A
echo "<div>description root:</div>";

$root_descr = getFolder($root_id, 5);

var_dump($root_descr);

# recuperation des infos des enfants de A
echo "<div>dossiers de root :</div>";

$root_fold = getSubFolders($root_id, 5);
var_dump($root_fold);


echo "<div>fichiers de root :</div>";

$root_files = getSubFiles($root_id, 5);
var_dump($root_files);

