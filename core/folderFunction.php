<?php 

include_once("sql.php");
include("config.php");

function ajoute_dossier_racine($groupId){

	$sql = sqlconnect();
	$querry = "INSERT INTO 
				`folder` (
					`idFolder`, 
					`folderName`, 
					`parentFolderId`, 
					`groupId`, 
					`rootFoldrer`) 
				VALUES (
					NULL, 
					'root', 
					NULL, 
					'$groupId', 
					b'1');
				";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);
}

function ajoute_dossier($name, $idParentFolder,$groupId){

	$sql = sqlconnect();
	$querry = "INSERT INTO 
				folder (
					`idFolder`, 
					`folderName`, 
					`parentFolderId`, 
					`groupId`, 
					`rootFoldrer`) 
				VALUES ('$name', '$idParentFolder', '$groupId', b'0');
				";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);         	
}
function recupere_fichiers_dans_dossier($folderId){

	// Retourne les information concernant les fichiers contenus dans le dossier donné

	$sql = sqlconnect();
	$querry = "SELECT id FROM file WHERE location = $folderId";

	$resq = mysqli_query($sql, $querry);
	mysqli_close($sql);
	$folderarray=array();
	while($row = mysqli_fetch_assoc($resq)) {
		$folderarray[] = (int) $row["id"];
	}
	return $folderarray;	
}

function recupere_dossiers_dans_dossier($folderId){

	// Retourne les information concernant les fichiers contenus dans le dossier donné

	$sql = sqlconnect();
	$querry = "SELECT id FROM folder WHERE parent_id = $folderId";

	$resq = mysqli_query($sql, $querry);
	mysqli_close($sql);
	$folderarray=array();
	while($row = mysqli_fetch_assoc($resq)) {
		$folderarray[] = (int) $row["id"];
	}
	return $folderarray;
}

function renomer_dossier($folderId, $name){

	//pour une raison débile, il est impossible de faire fonctionner la requette qi on update pas en même temps le bit du root folder

	$sql = sqlconnect();
	$querry = "UPDATE `folder` SET `folderName` = '$name', `rootFoldrer` = b'0' WHERE `folder`.`idFolder` = $folderId;";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);
}

// function storeFile($folderId, $fileName, $creatorId, $rawFile){

// 	// $rawFile viens du résultat d'un retour de formulaire $file['{nomDeInput}']['tmp_name']
// 	//à revoir

// 	$sql = sqlconnect();
// 	$querry = "";

// 	$res = mysqli_query($sql, $querry);
// 	mysqli_close($sql);
// 	return $res;	
// }

function recup_folder_id($id){
	$sql=sqlconnect();
    $folder =  "SELECT * FROM folder WHERE id = $id";
	$result = mysqli_query($sql, $folder);
	$folder_data=mysqli_fetch_assoc($result);
	mysqli_close($sql);
	return $folder_data;
}

function create_folder($nom,$parent){
	$sql = sqlconnect();

	$querry = "INSERT INTO folder (name,root) VALUES ('$nom',$parent)";
	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);
	return $res;
}

function recup_folder_nom_descr($nom,$description){
	$sql=sqlconnect();
    $user =  "SELECT * FROM folder WHERE name = '$nom' ";//AND description='$description";
	$result = mysqli_query($sql, $user);
	$file_data=mysqli_fetch_assoc($result);
	mysqli_close($sql);
	return $file_data;
}

function supprimer_dossier($folderId){
   $sql = sqlconnect();
    $querry = "DELETE FROM folder f WHERE f.id = $folderId";

    $res = mysqli_query($sql, $querry);
    mysqli_close($sql);
}

function supprimer_dossier_rec($folderId){

    $contentfolder = recupere_dossier_dans_dossier($folderId);
    $contentfile = recupere_fichier_dans_dossier($folderId);


    if (sizeof($contentfolder) > 0) {
        foreach ($contentfolder as $key) {
            supprimer_dossier_rec($contentfolder[$key]["idFolder"]);
        }
    }
    if (sizeof($contentfile) > 0) {
        foreach ($contentfile as $key) {
            supprime_file($contentfile[$key]["id"]);
        }
    }

    supprimer_dossier($folderId);

}

function stocker_fichier($fileName, $fileExtention, $creatorId, $folderId){

    $sql = sqlconnect();
    $querry = "INSERT INTO `file` (`id`, `location`, `name`, `extension`, `creatorId`) VALUES (NULL, '$folderId', '$fileName', '$fileExtention', '$creatorId');";

    $res = mysqli_query($sql, $querry);
    mysqli_close($sql);

}

function deplacer_fichier($fileId, $newFolderId){

    $sql = sqlconnect();
    $querry = "UPDATE `File` SET `location` = '$newFolderId' WHERE `File`.`id` = $fileId;";

    $res = mysqli_query($sql, $querry);
    mysqli_close($sql);
}

function deplacer_dossier($folderId, $newFolderId){

    $sql = sqlconnect();
    $querry = "UPDATE `folder` SET `parentFolderId` = '$newFolderId', `rootFoldrer` = b'0' WHERE `folder`.`idFolder` = $folderId;";

    $res = mysqli_query($sql, $querry);
    mysqli_close($sql);
}