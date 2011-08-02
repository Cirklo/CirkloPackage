<?php

/**
 * @author João Lagarto
 * @abstract Ajax handler request -> get referenced table
 * 
 *  */
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
if(isset($_GET['table'])){ $table = $_GET['table'];}

//construct array for input parameters.
$arr = array($database,$table,'','');
//set values
for($i = 0;$i<sizeof($arr);$i++){
	$query->__set($i, $arr[$i]);	
}

//select engine (mysql or pgsql)
$query->engineHandler($engine);

//query number 6 -> necessary in order to select specific query from vault
$sql = $conn->prepare($query->getSQL(5)); 
$sql->execute();
echo "<form name=list>";
echo "<table border=0>";
//FOR postgresql one must use rowCount>1
if($sql->rowCount()>1) echo "<tr><td colspan=2>You may choose another tables to build the query</td></tr>";
for($i=0;$row=$sql->fetch();$i++){
	if($table!=$row[0])	{
		echo "<tr><td width=25px><input type=checkbox name=$row[0] id=$row[0] lang=$row[1]></td><td style='text-align:left'>".$row[0]."</td>";
		//echo "<td>".$row[1]."</td>";
		echo "</tr>";
	}
}
//FOR postgresql one must use rowCount>1
echo "<tr><td colspan=2><input type=button value=Submit onclick=displayFields()></td></tr>";
echo "</table>";
echo "</form>";
?>