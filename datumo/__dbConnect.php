<?php

//hide any PHP warnings that may be displayed
error_reporting(0);
if(isset($_SESSION['path']) or $_SESSION['path']!=""){
	require_once $_SESSION['path']."/.htconnect.php";	
} else {
	echo "Session was lost! Waiting for automatic redirection...";
	echo "<meta HTTP-EQUIV='REFRESH' content='3; url=../'>";
	exit();
}
	
class dbConn{
	public function __construct(){
		$conn=new dbConnection();
	}
}



?>