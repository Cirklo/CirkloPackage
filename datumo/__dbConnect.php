<?php

//hide any PHP warnings that may be displayed
error_reporting(1);

if(isset($_SESSION['path']) or $_SESSION['path']!=""){
	//use $_SERVER['DOCUMENT_ROOT'] with requistitions and animalhouse
	//echo $_SERVER['DOCUMENT_ROOT'].$_SESSION['path']."/.htconnect.php";
	require_once $_SESSION['path']."/.htconnect.php";
} else {
	echo "Session was lost! Waiting for automatic redirection...";
	echo "<meta HTTP-EQUIV='REFRESH' content='3; url=../'>";
	exit();
}

//require_once ".htconnect.php";
class dbConn{
	public function __construct(){
		$conn=new dbConnection();
	}
}
?>