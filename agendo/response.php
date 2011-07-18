<?php

/*
  @author Nuno Moreno/João Lagarto
  @copyright 2009-2010 Nuno Moreno/João Lagarto
  @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
  @version 1.1
  @ ajax request handler
*/

// require_once(".htconnect.php");
// require_once("__dbHelp.php");
require_once("commonCode.php");
require_once("functions.php");
$db = dbHelp::getDatabase();
dbHelp::mysql_select_db2('information_schema');

$id = $_GET['id'];
$value = clean_input($_GET['val']);

$sql = "SELECT REFERENCED_TABLE_NAME FROM KEY_COLUMN_USAGE where REFERENCED_TABLE_NAME <> 'null' AND COLUMN_NAME = '".$id."' AND TABLE_SCHEMA LIKE '".$db."'";
$resf = dbHelp::mysql_query2($sql);
while($row = dbHelp::mysql_fetch_row2($resf))
{
	$table = $row[0];
	
}

dbHelp::mysql_select_db2($db);

$sql = "show fields from ".$table;
$res = dbHelp::mysql_query2($sql) or die ($sql);
// mysql_data_seek($res,0);
$field1 = dbHelp::mysql_fetch_row2($res);
// mysql_data_seek($res,1);
$field2 = dbHelp::mysql_fetch_row2($res); 

$sql="select " . $field2[0] . ",". $field1[0] . " from $table where lower(" . $field2[0] . ") like lower('" . $value . "%')";
$res = dbHelp::mysql_query2($sql) or die ($sql);
$arr = dbHelp::mysql_fetch_row2($res);
echo $arr[1];

?>
