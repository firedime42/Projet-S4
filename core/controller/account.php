<?php
session_start();
header("Content-Type: application/json");
require_once dirname(__FILE__)."/../accountFunction.php";
$_post = json_decode(file_get_contents("php://input"));

$res = array(
    "success" => false
);

switch ($_post->action) {
    case "login":
        global $database;
        $password=mysqli_real_escape_string($database,$_post->password);
        if(($_post->time+10)<time()){
            $res["error"]=1102; //Le timestamp est trop vieux
        } elseif (isset($_post->email)) { //Connexion par mail  
            $email=mysqli_real_escape_string($database,$_post->email); 
            $user = recup_user_email($email);
            if(empty($user)){
                $res["error"]=1104; //Erreur l'email ne correspond à aucun utilisateur
            } elseif (!(hash('sha256', "$_post->time".$user["password"])==$password)){
                $res["error"]=1101; //Le mot de passe ne correspond pas
            } else {
                $res["success"]=true;
                $res["user"] = array(
                    "id" => (int) $user["id"],
                    "email" => $email,
                    "username" => $user["username"]
                );
                $_SESSION["user"]=$user;
            }       
        }elseif(isset($_post->username)) { //Connexion par username
            $username=mysqli_real_escape_string($database,$_post->username);
            $user = recup_user_username($username);
            if(empty($user)){
                $res["error"]=1103; //Erreur l'identifiant ne correspond à aucun utilisateur
            }
                
            elseif (!(hash('sha256', "$_post->time".$user["password"])==$password)){
                $res["error"]=1101; //Le mot de passe ne correspond pas
            }
            else{
                $res["success"]=true;
                $res["user"] = array(
                   "id" => (int) $user["id"],
                   "email" => $user["email"],
                   "username" => $username
                );
                $_SESSION["user"]=$user;
            }
        }else{
            $res["error"]=1100; //Erreur inconnue liée à la connexion
        }
        break;
    case "register":
        global $database;
        if (!format_mail($_post->email)){
            $res["error"] = 1201; //email invalide (mauvais format)
        }
        elseif (!format_username($_post->username)) {
            $res["error"] = 1202; //username invalide (mauvais format)
        }
        elseif (!empty(recup_user_email($_post->email))) {
            $res["error"] = 1203; //email déjà utilisé par un autre compte
        }
            
        elseif (!empty(recup_user_username($_post->username))) {
            $res["error"] = 1204; //username déjà utilisé par un autre compte
        }
        elseif (!isset($_post->password)) {
            $res["error"] = 1205; //le mot de passe est vide
        } 
        else {
            $email=mysqli_real_escape_string($database,$_post->email);
            $username=mysqli_real_escape_string($database,$_post->username);
            $password=mysqli_real_escape_string($database,$_post->password);
            $res["success"] = creation_utilisateur($username, $email, $password);
            $_SESSION["user"] = recup_user_username($username);
            $res["user"] = array(
                "id" => (int) $_SESSION["user"]["id"],
                "email" => $email,
                "username" => $username
            );
        }
        break;
    case "retrieve":
        if(isset($_SESSION["user"])){
            $user = recup_user_id($_SESSION["user"]["id"]);
            if (!isset($user)) $res["error"] = 1103;
            else {
                $res["success"] = true;
                $res["user"] = array(
                    "id" => (int) $_SESSION["user"]["id"],
                    "email" => $user["email"],
                    "username" => $user["username"]
                );
            }
        }else{
            $res["error"] = 101;
        }
        break;
    case "logout":
        $res["success"] = true;
        session_destroy();
        //unset($_session);
        break;
    case "editLogin":
        if (!isset($_post->login)){
            $res["error"]=1000;
        } elseif (!isset($_SESSION["user"])) {
            $res["error"]=1000;
        } else {
            $login=mysqli_real_escape_string($database, $_post->login);
            $user = recup_user_id($_SESSION["user"]["id"]);
            if(empty($user)){
                $res["error"]=1103; //Erreur l'identifiant ne correspond à aucun utilisateur
            }elseif (!format_username($login)) {
                $res["error"]=1102; 
            }else {
                $res["success"]=edit_login($_SESSION["user"]["id"],$login);
            }
        }
        break;
    case "editBiography":
        if (!isset($_post->biography)){
            $res["error"]=1000;
        } elseif (!isset($_SESSION["user"])) {
            $res["error"]=1000;
        } else {
            $biography=mysqli_real_escape_string($database,$_post->biography);
            $user = recup_user_id($_SESSION["user"]["id"]);
            if(empty($user)){
                $res["error"]=1103; //Erreur l'identifiant ne correspond à aucun utilisateur
            }else {
                $res["success"]=true;
                edit_biography($_SESSION["user"]["id"],$biography);
            }
        }
        break;
    case "editPassword":
        if (!isset($_post->password)){
            $res["error"]=1000;
        } elseif (!isset($_post->time)){
            $res["error"]=1000;
        } elseif (!isset($_post->new_password)){
            $res["error"]=1000;
        } elseif (!isset($_SESSION["user"])) {
            $res["error"]=1000;
        } elseif(($_post->time + 10) < time()){
            $res["error"]=1102; //Le timestamp est trop vieux
        } else {
            $user = recup_user_id($_SESSION["user"]["id"]);
            $password=mysqli_real_escape_string($database,$_post->password);
            $new_password=mysqli_real_escape_string($database,$_post->new_password);
            if(empty($user)){
                $res["error"]=1103; //Erreur l'identifiant ne correspond à aucun utilisateur
            } elseif (!(hash('sha256', "$_post->time".$user["password"])==$password)){
                $res["error"]=1101; //Le mot de passe ne correspond pas
            } else {
                $res["success"]=true;
                edit_password($_SESSION["user"]["id"], $new_password);
            }
        }
        break;
    default:
        $res["error"] = 1000; //Erreur inconnue générée par account
        break; 
}

echo json_encode($res);
?>