<?php

require_once dirname(__FILE__).'/../core.php';
require_once dirname(__FILE__).'/../model/session.m.php';



function openSession( $user_id ) {
    global $_CONFIG, $_DB;

    $session = new Session();
    $session->open = true;
    $session->user = $user_id;
    $session_secret_key = bin2hex(random_bytes(16));              # génère une clé aléatoire de 16 octets
    $session->key = hash('sha256', hex2bin($session_secret_key)); # stoque le hash de la clé sur la base de données

    # update session
    $req = $_DB->prepare("INSERT INTO `session` (`user`, `key`) VALUES (:user, UNHEX(:session_key))");
    $req->execute(array("user" => $session->user, "session_key" => $session->key));

    # remove old session
    $req2 = $_DB->prepare("SELECT (`opening_date`) FROM `session` WHERE `user` = :user ORDER BY `opening_date` DESC LIMIT ".$_CONFIG['max_concurrent_sessions'].",1");
    $req2->execute(array("user" => $session->user));

    if ($req2->rowCount() > 0) {
        $date = $req2->fetch();
        $req3 = $_DB->prepare("DELETE FROM `session` WHERE `user` = :user AND `opening_date` <= :date_timeout");
        $req3->execute(array("user" => $session->user, "date_timeout" => $date["date"]));
    }
    

    $time = time();
    setcookie('session_id', $session->id, $time + $_CONFIG['session_keepalive'], '/');
    setcookie('session_key', $session_secret_key, $time + $_CONFIG['session_keepalive'], '/');
}

function closeSession( $session ) {
    global $_DB;

    if ($session != null) {
        $session->open = false;

        # suppression de la session sur la base de donnée
        $req = $_DB->prepare("DELETE FROM `session` WHERE `id` = :id AND `key` = UNHEX(:session_key)");
        $req->execute(array('id' => $session->id, 'session_key' => $session->key));

        # suppression de la session sur le client
        setcookie('session_id', null, 0, '/');
        setcookie('session_key', null, 0, '/');
    }

}

function retrieveSession( ) {
    /* modifier cette fonciton  */

    global $_COOKIE, $_CONFIG, $_DB;

    $session_open = false;

    if (isset($_COOKIE['session_key']) && isset($_COOKIE['session_id'])) {
        $session_id = $_COOKIE['session_id'];
        $session_secret_key = $_COOKIE['session_key'];
        $session_key_hash = hash('sha256', hex2bin($session_secret_key));

        $req = $_DB->prepare("UPDATE `session` SET `opening_date` = CURRENT_TIMESTAMP() WHERE `user` = :id AND `sess_key` = UNHEX(:sess_key)");
        $req->execute(array('id' => $session_id, 'sess_key' => $session_key_hash));

        $session_open = $req->rowCount() > 0; # vérifier que la requête s'est correctement effectué = la session existe

        if ($session_open) {
            # on actualise les cookies
            $time = time();
            setcookie('session_uid', $session_id, $time + $_CONFIG['session_keepalive'], '/');
            setcookie('session_key', $session_secret_key, $time + $_CONFIG['session_keepalive'], '/');
        } else {
            # on supprime les cookies
            setcookie('session_uid', null, 0, '/');
            setcookie('session_key', null, 0, '/');
        }
    }
}
?>