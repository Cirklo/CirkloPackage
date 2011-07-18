<?php

require_once("__dbConnect.php");
//database options
$db = database(1);
mysql_select_db("information_schema");

$table = $_GET['table'];

$sql = "SELECT ORDINAL_POSITION, COLUMN_NAME FROM COLUMNS WHERE TABLE_NAME='$table' AND TABLE_SCHEMA='$db'";
$res = mysql_query($sql) or die(mysql_error());
while($row = mysql_fetch_array($res)){
	echo "<name>" . substr($row['COLUMN_NAME'], strlen($table."_"), strlen($row['COLUMN_NAME']));
    echo "<value>" . $row['ORDINAL_POSITION'];
}
?>