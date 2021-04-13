<?php
    global $_CONFIG;
    $_CONFIG = array(
        "sitename" => "Shared",

        # base de données
        "db_host" => "51.254.223.143:3306", //"localhost",//"51.178.54.52",
        "db_name" => "lcmc_116714",//"share",//"id16419041_wazap",
        "db_user" => "lcmc_116714",//"root",//"id16419041_info406",
        "db_password" => "2bde19f5",//"",//"^YE#>RNuaIGe_1@(",
        
        # session
        "session_keepalive" => 365 * 24 * 3600, # seconds
        "max_concurrent_sessions" => 3,         # nombre maximum de sessions simultanées (3 ~> 3 appareils)

        
        # Password strength requirements
        "password_length" => 8,
        "password_nb_uppercase" => 0,
        "password_nb_lowercase" => 0,
        "password_nb_digits" => 0,
        "password_nb_special_char" => 0,


        # la modification de la methode si dessous rend obsolete les anciens mots de passe.
        # methode initial : "sha256"
        "hash_method" => "sha256"
    );

?>