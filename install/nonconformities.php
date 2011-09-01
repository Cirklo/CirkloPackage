#!/usr/bin/php5
<?php

session_start();
$pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
$_SESSION['path'] = "../".$pathOfIndex[sizeof($pathOfIndex)-1];
require_once("../agendo/commonCode.php");
require_once("../agendo/alertClass.php");
$alert= new alert;
$alert->nonconf();


?>

