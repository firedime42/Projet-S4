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
				`folder` (
					`idFolder`, 
					`folderName`, 
					`parentFolderId`, 
					`groupId`, 
					`rootFoldrer`) 
				VALUES (
					NULL, 
					'$name', 
					'$idParentFolder', 
					'$groupId', 
					b'0');
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
	while($row = mysqli_fetch_assoc($resq)) {
		$folderarray[] = $row;
	}
	return $folderarray;	
}

function recupere_dossiers_dans_dossier($folderId){

	// Retourne les information concernant les fichiers contenus dans le dossier donné

	$sql = sqlconnect();
	$querry = "SELECT id FROM folder WHERE parent_id = $folderId";

	$resq = mysqli_query($sql, $querry);
	mysqli_close($sql);
	while($row = mysqli_fetch_assoc($resq)) {
		$folderarray[] = $row;
	}
	return $folderarray;
}
/*
function recupere_fichier_dans_dossier($folderId){

	// Retourne les information concernant les fichiers contenus dans le dossier donné

	/* format :

		{
			[0] => 
			{
				'idFile' 
				'location'
				'name'	
				'extention'
			},
			{...}
			[n] =>
			{
				'idFile' 
				'location'
				'name'	
				'extention'
			}
		}


	$sql = sqlconnect();
	$querry = "SELECT `idFile`,`location`,`name`,`extention` FROM `file` WHERE location = $folderId";

	$resq = mysqli_query($sql, $querry);
	mysqli_close($sql);

	$folderarray = array();

	while($row = mysqli_fetch_assoc($resq)) {
		$folderarray[] = $row;
	}
	return $folderarray;	
}

function recupere_dossier_dans_dossier($folderId){

	// Retourne les information concernant les fichiers contenus dans le dossier donné

	/* format :

		{
			[0] => 
			{
				'idFile' 
				'location'
				'name'	
				'extention'
			},
			{...}
			[n] =>
			{
				'idFile' 
				'location'
				'name'	
				'extention'
			}
		}

	$sql = sqlconnect();
	$querry = "SELECT * FROM `folder` WHERE parentFolderId = $folderId";

	$resq = mysqli_query($sql, $querry);
	mysqli_close($sql);

	$folderarray = array();

	while($row = mysqli_fetch_assoc($resq)) {
		$folderarray[] = $row;
	}
	return $folderarray;
}*/

function renomer_dossier($folderId, $name){

	//pour une raison débile, il est impossible de faire fonctionner la requette qi on update pas en même temps le bit du root folder

	$sql = sqlconnect();
	$querry = "UPDATE `folder` SET `folderName` = '$name', `rootFoldrer` = b'0' WHERE `folder`.`idFolder` = $folderId;";

	$res = mysqli_query($sql, $querry);
	mysqli_close($sql);
}

function supprimer_dossier($folderId){

	// Demander de voir comment on gère la suppression
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