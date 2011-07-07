
<?php
error_reporting(1);
/**
 * @author João Lagarto	/ Nuno Moreno
 * @abstract Ajax handler request -> get second attribute from referenced table
 */
require_once "session.php";

require_once("__dbConnect.php");
require_once("dispClass.php");
/*
require_once("queryClass.php");
require_once("resClass.php");
require_once("searchClass.php");
require_once ("reportClass.php");
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
if(isset($_GET['value'])){ $val = $_GET['value'];}
if(isset($_GET['table'])){ $table = $_GET['table'];}
if(isset($_GET['id'])){ $id = $_GET['id'];}
if(isset($_GET['bool'])){ $bool = $_GET['bool'];}


//other variables
$arr=array();
$fk=array();
$display->tableHeaders($table);
$arr=$display->getHeader(); //table headers
$fk=$display->getFKtable(); //referenced table

$sql = $conn->prepare("SELECT * FROM $database.$table WHERE ".$table."_id=$val");
$sql->execute();
$row = $sql->fetch();
echo "<table style='line-height:10px'>";
for($i=0;$i<sizeof($arr);$i++){	
	if($fk[$i]!="" and $fk[$i]!=$table){ //is it a foreign key?
		//get second attribute from referenced table
		$display->getFKvalue($row[$i], $i);
		$row[$i] = $display->getFKatt();
	} 
	echo "<tr><td><b>".$arr[$i]."</b></td><td>".$row[$i]."</td></tr>";	
	
}
if(!isset($bool))echo "<tr><td colspan=2 style='text-align:right'><a href=javascript:void(0) onclick=showhide('$id')>Close</a></td></tr>";
echo "</table>";


?>