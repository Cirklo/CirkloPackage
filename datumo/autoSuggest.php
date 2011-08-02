<?php

header('Content-type: text/html; charset=UTF-8');
require_once "session.php";
$user_id=startSession();
require_once "__dbConnect.php";
require_once "queryClass.php";

$conn = new dbConnection(); $conn->dbConn();
$query = new queryClass();
$engine = $conn->getEngine();
$database = $conn->getDatabase();
//change search path to information schema
$conn->dbInfo();

	
if(isset($_GET['term'])) $q=$_GET['term'];
if(isset($_GET['field'])) $field=$_GET['field'];

//is it a filter?
if(substr($field,strlen($field)-3,strlen($field))=="_f_")
	$field=substr($field,0, strlen($field)-3);

//construct array for input parameters.
$arr = array($field,$database,'','');
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
$table=$row[0];

//hack for mysql (Dynamic reports)
if($table==""){
	$sql = $conn->prepare("SELECT table_name FROM columns WHERE column_name='$field' AND table_schema='$database'");
	$sql->execute();
	$row=$sql->fetch();
	$table=$row[0];	
}

//get second attribute from table
$sql = $conn->prepare("SELECT column_name FROM columns WHERE table_name='$table' AND table_schema='$database' LIMIT 1 OFFSET 1" );
$sql->execute();
$row = $sql->fetch();

//construct array for input parameters.
$arr = array($table,$q,$row['column_name'],$database);
	
//set values
for($i = 0;$i<sizeof($arr);$i++){
	$query->__set($i, $arr[$i]);	
}
//select engine (mysql or pgsql)
$query->engineHandler($engine);
	
//change main database
$conn->dbConn();

//query number 4 -> necessary in order to select specific query from vault
$sql = $conn->prepare($query->getSQL(4)); 
try{
	$sql->execute();
} catch (Exception $e){
	//do nothing
}
//echo '<ul>'."\n";
//if ($sql->rowCount()>0)	{
//    for($i=0;$row=$sql->fetch();$i++){
//		$p = $row[1];
//		//highlight matching characters
//		//$p = preg_replace('/(' . $q . ')/i', '<span style="font-weight:bold;">$1</span>', $p);
//		echo "\t<li id=autocomplete_.$row[0]. rel=$row[0] title='($row[0]) $row[1]' onclick=\$('#$field').attr('alt','$row[0]')>".utf8_encode($row[1])."</li>\n";
//    }   
//} else {
//	echo "No results!";
//}
// echo '</ul>';
for($i=0;$row=$sql->fetch();$i++){
	$row_array['id']=$row[0];
	$row_array['value']=$row[1];
	$json[]=$row_array;
}

echo json_encode($json);
?>