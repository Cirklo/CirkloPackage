<?php
require_once "session.php";
$user_id=startSession();
require_once("__dbConnect.php");
require_once("dispClass.php");
/*
require_once("queryClass.php");
require_once("resClass.php");
require_once("searchClass.php");
require_once("reportClass.php");
*/
//variables
$header = array();

//call classes to handle database connection and to build queries for different engines
$conn = new dbConnection();
$display = new dispClass();
$engine = $conn->getEngine();
$database = $conn->getDatabase();

//change database to information schema
$conn->dbInfo();

//http variables
if(isset($_GET['val'])){ $val = $_GET['val'];}
if(isset($_GET['table'])){ $table = $_GET['table'];}
if(isset($_GET['att'])){ $att = $_GET['att'];}

//value ->is it a foreign key? if so get id from referenced table
//get table headers' details
$display->tableHeaders($table);
$arr = array();
$arr = $display->getFKtable();

//ordinal_position-1 to match the array indexes
$sql = $conn->prepare("SELECT data_type, ordinal_position-1 FROM columns WHERE table_schema='".$database."' AND table_name='$table' AND column_name='$att'");
try{
	$sql->execute();
} catch (Exception $e){
	echo $sql;
}
$row = $sql->fetch();		
$fktable = $arr[$row[1]];

if($fktable != ""){
	//get second attribute from table
	$sql = $conn->prepare("SELECT column_name FROM columns WHERE table_name='".$fktable."' AND table_schema='$database' LIMIT 1 OFFSET 1" );
	try{
		$sql->execute();
	} catch (Exception $e){
		echo $sql;
	}	$row = $sql->fetch();
	
	//get id from referenced table
	$sql = $conn->prepare("SELECT ".$fktable."_id FROM $database.$fktable WHERE ".$row[0]."='$val'");
	try{
		$sql->execute();
	} catch (Exception $e){
		echo $sql;
	}
	$row = $sql->fetch();
	//ajax response
	echo $row[0];
} else {
	echo $val;
}





?>