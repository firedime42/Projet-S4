<?php 

require_once("sql.php");

function ajoute_dossier_racine($groupId){

	global $database;
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

	$res = mysqli_query($database, $querry);
	
}

function ajoute_dossier($name, $idParentFolder,$groupId){

	global $database;
	$querry = "INSERT INTO 
				folder (
					`idFolder`, 
					`folderName`, 
					`parentFolderId`, 
					`groupId`, 
					`rootFoldrer`) 
				VALUES ('$name', '$idParentFolder', '$groupId', b'0');
				";

	$res = mysqli_query($database, $querry);
	         	
}
function recupere_fichiers_dans_dossier($folderId){

	// Retourne les information concernant les fichiers contenus dans le dossier donné

	global $database;
	$querry = "SELECT id FROM file WHERE location = $folderId";

	$resq = mysqli_query($database, $querry);
	
	$folderarray=array();
	while($row = mysqli_fetch_assoc($resq)) {
		$folderarray[] = (int) $row["id"];
	}
	return $folderarray;	
}

function recupere_dossiers_dans_dossier($folderId){

	// Retourne les information concernant les fichiers contenus dans le dossier donné

	global $database;
	$querry = "SELECT id FROM folder WHERE parent_id = $folderId";

	$resq = mysqli_query($database, $querry);
	
	$folderarray=array();
	while($row = mysqli_fetch_assoc($resq)) {
		$folderarray[] = (int) $row["id"];
	}
	return $folderarray;
}

function renomer_dossier($folderId, $name){

	//pour une raison débile, il est impossible de faire fonctionner la requette qi on update pas en même temps le bit du root folder

	global $database;
	$querry = "UPDATE `folder` SET `folderName` = '$name', `rootFoldrer` = b'0' WHERE `folder`.`idFolder` = $folderId;";

	$res = mysqli_query($database, $querry);
	
}

// function storeFile($folderId, $fileName, $creatorId, $rawFile){

// 	// $rawFile viens du résultat d'un retour de formulaire $file['{nomDeInput}']['tmp_name']
// 	//à revoir

// 	global $database;
// 	$querry = "";

// 	$res = mysqli_query($database, $querry);
// 	
// 	return $res;	
// }

function recup_folder_id($id){
	global $database;
    $folder =  "SELECT * FROM folder WHERE id = $id";
	$result = mysqli_query($database, $folder);
	$folder_data=mysqli_fetch_assoc($result);
	
	return $folder_data;
}

function create_folder($nom,$parent){
	global $database;
	$querry = "INSERT INTO folder (name,root) VALUES ('$nom',$parent)";
	$res = mysqli_query($database, $querry);
	ajouter_chat_folder(mysqli_insert_id($database));
	return $res;
}

function recup_folder_nom_descr($nom,$description){
	global $database;
    $user =  "SELECT * FROM folder WHERE name = '$nom' ";//AND description='$description";
	$result = mysqli_query($database, $user);
	$file_data=mysqli_fetch_assoc($result);
	
	return $file_data;
}

function supprimer_dossier($folderId){
   global $database;
    $querry = "DELETE FROM folder f WHERE f.id = $folderId";

    $res = mysqli_query($database, $querry);
    
}

/*function supprimer_dossier_rec($folderId){

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

}*/

function stocker_fichier($fileName, $fileExtention, $creatorId, $folderId){

    global $database;
    $querry = "INSERT INTO `file` (`id`, `location`, `name`, `extension`, `creatorId`) VALUES (NULL, '$folderId', '$fileName', '$fileExtention', '$creatorId');";

    $res = mysqli_query($database, $querry);
    

}

function deplacer_fichier($fileId, $newFolderId){

    global $database;
    $querry = "UPDATE `File` SET `location` = '$newFolderId' WHERE `File`.`id` = $fileId;";

    $res = mysqli_query($database, $querry);
    
}

function deplacer_dossier($folderId, $newFolderId){

    global $database;
    $querry = "UPDATE `folder` SET `parentFolderId` = '$newFolderId', `rootFoldrer` = b'0' WHERE `folder`.`idFolder` = $folderId;";

    $res = mysqli_query($database, $querry);
    
}