<?php

include_once("sql.php");

function recup_file_id($id){
    $sql=sqlconnect();
    $user =  "SELECT * FROM file WHERE id = $id";
	$result = mysqli_query($sql, $user);
	$file_data=mysqli_fetch_assoc($result);
	mysqli_close($sql);
	return $file_data;
}
function recup_file_filename($filename){
    $sql=sqlconnect();
    $user =  "SELECT * FROM file WHERE name = '$filename'";
    $result = mysqli_query($sql, $user);
	$user_data=mysqli_fetch_assoc($result);
	mysqli_close($sql);
	return $user_data;
}
function supprime_file($id){
    $sql = sqlconnect();
    $querry = "DELETE FROM file WHERE id=$id";
    $res=mysqli_query($sql, $querry);
    mysqli_close($sql);
    return $res;
}
function create_file($folder,$filename,$content_type,$size,$descr,$id_creator){
//créer un fichier 

$sql = sqlconnect();

$querry = "INSERT INTO file (location, name, extension, creator_id,size,description) VALUES ($folder, '$filename', '$content_type', $id_creator,$size,'$descr   '";
mysqli_query($sql, $querry);
$querry = "SELECT id FROM file WHERE name='$filename' AND location=$folder AND description='$descr' AND extension='$content_type'";
$res=mysqli_query($sql,$querry);
mysqli_close($sql);
return $res;
}

function modifie_file($id,$nom,$description){
    $sql = sqlconnect();
    $querry = "UPDATE file SET name=$nom, description='$description' WHERE id=$id";//description";
    $res=mysqli_query($sql, $querry);
    mysqli_close($sql);
    return $res;
}
?>