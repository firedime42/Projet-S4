<?php

global $_session;
$_session = array();
session_start();
foreach ($_SESSION as $k => $v) $_session[$k] = $v;
session_write_close();
    
?>