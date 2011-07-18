<?php

/**
 * @author João Lagarto	/ Nuno Moreno
 * @abstract Ajax handler request -> get second attribute from referenced table
 */
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
if(isset($_GET['id'])){ $id = $_GET['id'];}
if(isset($_GET['table'])){ $table = $_GET['table'];}
if(isset($_GET['att'])){ $att = $_GET['att'];}

$display->tableHeaders($table);
$header = $display->getFKeys();

$sql = $conn->prepare("SELECT * FROM ".$database.".$header[$att] WHERE ".$header[$att]."_id=$id");
//echo $sql->queryString;
$sql->execute();
$row = $sql->fetch();
echo $row[1];



?>