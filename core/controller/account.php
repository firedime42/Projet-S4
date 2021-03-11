<?php
session_start();
require_once dirname(__FILE__)."/../accountFunction.php";
$_post = json_decode(file_get_contents("php://input"));

$_SESSION["user"]=array(
    "id" => -1,
    "userName" => "",
    "email" => ""
);
$res = array(
    "success" => false,
    "error" => -1
);

switch ($_post->action) {
    case "login":
        
        if(($_post->time+2)<time()){
            $res["error"]=1102; //Le timestamp est trop vieux
        }
        elseif ($_post->email != NULL) { //Connexion par mail   
               $user = recup_user_email($_post->email)[0];
                if(empty($user)){
                    $res["error"]=1104; //Erreur l'email ne correspond à aucun utilisateur
                }
                    
                elseif (!(hash('sha256', "$_post->time".$user["passWord"])==$_post->password)){
                    $res["error"]=1101; //Le mot de passe ne correspond pas
                }
                   
                else{
                    $res["success"]=true;
                    $res["user"] = array(
                        "id" => $user["id"],//$_SESSION["user"]["id"],
                        "email" => $_post->email,
                        "username" => $user["userName"]
                    );
                    $_SESSION["user"]=$user;
               }       
        }elseif($_post->username != NULL) { //Connexion par username   
            $user = recup_user_username($_post->username)[0];
            if(empty($user)){
                $res["error"]=1103; //Erreur l'identifiant ne correspond à aucun utilisateur
            }
                
            elseif (!(hash('sha256', "$_post->time".$user["passWord"])==$_post->password)){
                $res["error"]=1101; //Le mot de passe ne correspond pas
            }
            else{
                $res["success"]=true;
                $res["user"] = array(
                   "id" => $user["id"],
                   "email" => $user["email"],
                   "username" => $_post->username
                );
                $_SESSION["user"]=$user;
            }
        }else{
            $res["error"]=1100; //Erreur inconnu liée à la connexion
        }
        break;
    case "register":
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
    elseif ($_post->password == NULL) {
        $res["error"] = 1205; //le mot de passe est vide
    } 
    else {
        print("Inscription valide");
        creation_utilisateur($_post->username, $_post->email, $_post->password);
        $res["success"] = true;
        $res["user"] = array(
            "id" => recup_user_email($_post->email)[0]["id"],
            "email" => $_post->email,
            "username" => $_post->username
        );
        $_SESSION["user"] = recup_user_username($_post->username)[0];
    }
    break;
    case "retrieve":
        $res["success"] = true;
        $res["user"] = array(
            "id" => $_SESSION["user"]["id"],
            "email" => $_SESSION["user"]["email"],
            "username" => $_SESSION["user"]["userName"]
        );
        break;
    case "logout":
        $res["success"] = true;
        session_destroy();
        break;
    break;
    default:
        $res["error"] = 1000; //Erreur inconnu généré par account
        break; 
}

echo json_encode($res);
?>