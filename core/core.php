<?php
    require_once dirname(__FILE__).'/config.php';

    # tentative de connection à la base de donnée

    try {
        $_DB = new PDO("mysql:host={$_CONFIG['db_host']};dbname={$_CONFIG['db_name']}", $_CONFIG['db_user'], $_CONFIG['db_password']);
    } catch (PDOException $e) {
        echo "error: database connection";
        die();
    }
?>