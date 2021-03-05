<?php
    global $_CONFIG;
    $_CONFIG = array(
        "sitename" => "Shared",

        # base de données
        "db_host" => "",
        "db_name" => "",
        "db_user" => "",
        "db_password" => "",

        # session
        "session_keepalive" => 365 * 24 * 3600, # seconds
        "max_concurrent_sessions" => 3,         # nombre maximum de sessions simultanées (3 ~> 3 appareils)

        
        # Password strength requirements
        "password_length" => 8,
        "password_nb_uppercase" => 0,
        "password_nb_lowercase" => 0,
        "password_nb_digits" => 0,
        "password_nb_special_char" => 0,


        # la modification de la methode si dessous rend obselete les anciens mots de passe.
        # methode initial : "sha256"
        "hash_method" => "sha256"
    );

?>