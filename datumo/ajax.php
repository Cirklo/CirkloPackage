<?php

/**
 * @author João Lagarto	/ Nuno Moreno
 * @abstract Ajax handler request -> get second attribute from referenced table
 */
require_once "session.php";
$user_id=startSession();
require_once("__dbConnect.php");
require_once("queryClass.php");
require_once("dispClass.php");

/*
require_once("resClass.php");
require_once("searchClass.php");
require_once("reportClass.php");
*/
//variables
$header = array();

//call classes to handle database connection and to build queries for different engines
$conn = new dbConnection();
$query = new queryClass();
$display = new dispClass();
$engine = $conn->getEngine();
$database = $conn->getDatabase();

//change database to information schema
$conn->dbInfo();

//http variables
if(isset($_GET['val'])){ $val = $_GET['val'];}
if(isset($_GET['var'])){ $var = $_GET['var'];}
if(isset($_GET['id'])){  $id = $_GET['id'];}

if($val == '') { exit();}

//construct array for input parameters.
$arr = array($var,$database,'','');
//set values
for($i = 0;$i<sizeof($arr);$i++){
	$query->__set($i, $arr[$i]);	
}

//select engine (mysql or pgsql)
$query->engineHandler($engine);

//query number 3 -> necessary in order to select specific query from vault
$sql = $conn->prepare($query->getSQL(3)); 
$sql->execute();
$row = $sql->fetch();
//get all fields from referenced table
$display->tableHeaders($row[0]);
$header = $display->getFullHeader();

//change to main database
$conn->dbConn();

//get id from referenced table
$sql = $conn->prepare("SELECT ".$row[0]."_id FROM $database.$row[0] WHERE ".$header[1]."='$val'");

$sql->execute();
$row = $sql->fetch();

//ajax response
echo $row[0];
?>