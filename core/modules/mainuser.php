<?php

require_once dirname(__FILE__).'/../core.php';
require_once dirname(__FILE__).'/../models/user.m.php';

class MainUser {
    public static $SUCCESS = 'ok';
    public static $ERROR_INVALIDE_PASSWORD = 'IPSW invalide password';
    public static $ERROR_INVALIDE_PSEUDO = 'IP invalide pseudo';
    public static $ERROR_NO_ACCOUNT = 'NA no account on habbocity';
    public static $ERROR_NOT_IN_GROUPE = 'NIG not in group';
    public static $ERROR_INVALIDE_MOTTO = 'IM invalide motto';
    public static $ERROR_PASS_STRENGTH_NEC = 'PSWNEC password too weak : not enough char';           // Not Enough Char
    public static $ERROR_PASS_STRENGTH_NEU = 'PSWNEU password too weak : not enough uppercase char'; // Not Enought Upper Char
    public static $ERROR_PASS_STRENGTH_NEL = 'PSWNEL password too weak : not enough lowercase char'; // Not Enought Lower Char
    public static $ERROR_PASS_STRENGTH_NED = 'PSWNED password too weak : not enough digits';         // Not Enought Digits
    public static $ERROR_PASS_STRENGTH_NES = 'PSWNES password too weak : not enough special char';   // Not Enought Special Char
    public static $ERROR_ACCOUNT_ALREADY_EXIST = 'AAE account already exist';

    private $user = null;

    private $session_open = false;
    private $session_uid = null;
    private $session_key = null;
    private $session_hash = null;

    public function __construct() {
        # check if session is open
        $this->retrieveSession();
    }


    ## FONCTIONS DE SESSION


    ## FONCTIONS DE CONNECTION ET D'IDENTIFICATION

    public static function password_strength($password) {
        global $_CONFIG;

        # resultat
        $res = MainUser::$SUCCESS;

        # counters
        $pass_length = strlen($password);
        $pass_nb_uppercase = 0;
        $pass_nb_lowercase = 0;
        $pass_nb_digits = 0;
        $pass_nb_special = 0;

        for ($i = 0; $i < $pass_length; $i++) {
            $char = $password[$i];

            if ($char >= 'A' && $char <= 'Z') $pass_nb_uppercase++;
            elseif ($char >= 'a' && $char <= 'z') $pass_nb_lowercase++;
            elseif ($char >= '0' && $char <= '9') $pass_nb_digits++;
            else $pass_nb_special++; 
        }

        if ($pass_length < $_CONFIG["password_length"])
            $res = MainUser::$ERROR_PASS_STRENGTH_NEC;

        if ($pass_nb_uppercase < $_CONFIG["password_nb_uppercase"])
            $res = MainUser::$ERROR_PASS_STRENGTH_NEU;

        if ($pass_nb_lowercase < $_CONFIG["password_nb_lowercase"])
            $res = MainUser::$ERROR_PASS_STRENGTH_NEL;

        if ($pass_nb_digits < $_CONFIG["password_nb_digits"])
            $res = MainUser::$ERROR_PASS_STRENGTH_NED;

        if ($pass_nb_special < $_CONFIG["password_nb_special_char"])
            $res = MainUser::$ERROR_PASS_STRENGTH_NES;

        return $res;
    }
    
    /**
     * try to login
     */
    public function login($pseudo, $password) {
    }

    public function register($pseudo, $password, $motto_key) {
    }

    public function logout() {
        # remove session
        $this->user = null;
        $this->endSession();
    }

    public function isLoggedIn() {
        return $this->session_open;
    }

    ## FONCTIONS DE PARAMETRAGES

    ## GET
    public function getUser() {
        global $_DB;

        if ($this->session_open && $this->user == null) {
            $req = $_DB->prepare('SELECT * FROM `users` WHERE `id` = :id');
            $req->execute(array('id' => $this->session_uid));

            $req->setFetchMode(PDO::FETCH_CLASS, "User");
            $this->user = $req->fetch();
        }

        return $this->user;
    }
} 
?>