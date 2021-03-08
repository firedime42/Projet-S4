<?php
session_start();
require_once("utilisateur.php");
$_post = json_decode(file_get_contents("php://input")); //Recuperation requete

$res = array(
    "success" => false,
    "error" => -1
);

switch ($_post->action) {
    case "login":
        $mot_de_passe = $_post->password;
        if ($_post->email != NULL) { //Connexion par mail           
            $trouve = cherche_email($_post->email);
            if ($trouve) {
                if (connecte_utilisateur_email($_post->email, $mot_de_passe)) {
                    $res["success"] = true;
                    $id = email_to_id($_post->login);
                    $res["user"] = array(
                        "id" => $id,
                        "email" => $_post->email,
                        "username" => id_to_username($id)
                    );
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = id_to_username($id);
                    $_SESSION["email"] = $_post->email;
                } else {
                    $res["error"] = 1101;
                }
            } else {
                $res["error"] = 1104;
            }
        } else if ($_post->login != NULL) { //Connexion par login
            $trouve = cherche_username($_post->login);
            if ($trouve) {
                if (connecte_utilisateur_username($_post->login, $mot_de_passe)) {
                    $res["success"] = true;
                    $id = username_to_id($_post->login);
                    $res["user"] = array(
                        "id" => $id,
                        "email" => id_to_email($id),
                        "username" => $_post->login
                    );
                } else {
                    $res["error"] = 1101;
                }
            } else {
                $res["error"] = 1103;
            }
        } else {
            $res["error"] = 1100;
        }
        break;
    case "register":
        if (format_mail($_post->email)) $res["error"] = 1201;
        elseif (format_username($_post->login)) $res["error"] = 1202;
        elseif (cherche_email($_post->email)) $res["error"] = 1203;
        elseif (cherche_username($_post->login)) $res["error"] = 1204;
        elseif ($_post->password == NULL) $res["error"] = 1205;
        elseif (force_password($_post->password)) $res["error"] = 1206;
        else {
            creation_utilisateur($_post->login, $_post->email, $_post->password);
            $res["success"] = true;
            $res["user"] = array(
                "id" => username_to_id($_post->login),
                "email" => $_post->email,
                "username" => $_post->login
            );
            $_SESSION["id"] = username_to_id($_post->login);
            $_SESSION["username"] = $_post->login;
            $_SESSION["email"] = $_post->email;
        }
        break;
    case "logout":
        $res["success"] = true;
        break;
    case "retrieve":
        $res["success"] = true;
        $res["user"] = array(
            "id" => $_SESSION["id"],
            "email" =>  $_SESSION["email"] = $_post->email,
            "username" => $_SESSION["username"]
        );
        break;
        /*
    case "login":
        $res["success"] = true;
        $res["user"] = array(
            "id" => 0,
            "username" => $_post->username
        );
    break;
    case "retrieve":
        $res["success"] = true;
        $res["user"] = array(
            "id" => 0,
            "username" => "Eleni Richard",
            "email" => "eleni.richard@coeur.2lion.com"
        );
    break;
    case "logout": $res["success"] = true; break;
    case "register":
        $res["success"] = true;
        $res["user"] = array(
            "id" => 0,
            "username" => $_post->username
        );
    break;
   */
    default:
        $res["error"] = 0;
        break;
}

echo json_encode($res);//Renvoie le res
