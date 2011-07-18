<?php
// This file was altered by Pedro Pires (The chosen two)

session_start();
error_reporting(0);

$user_id = $_SESSION['user_id'];

if(!isset($user_id) || $user_id == '' && !isset($_SESSION['logOff']))
{
    echo "<meta HTTP-EQUIV='REFRESH' content='0; url=./'>";
	$_SESSION['logOff'] = true;
}
 


?>