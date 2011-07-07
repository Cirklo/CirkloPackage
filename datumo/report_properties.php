<?php
require_once("session.php");
$user_id = startSession();

require_once "__dbConnect.php";
require_once "dispClass.php";
require_once "queryClass.php";
require_once "resClass.php";

//http variables
if(isset($_GET['report_id'])){	$report_id=$_GET['report_id'];}

//call classes
$conn = new dbConnection();
$display = new dispClass();
$database = $conn->getDatabase();

//get report properties
$query="SELECT reprop_attribute, reprop_mask FROM reprop WHERE reprop_report=$report_id";
//Query to get table properties
$sql = $conn->prepare($query);
//echo $sql->queryString;
$sql->execute();

for($i=0;$row=$sql->fetch();$i++){
	$json->colModel[] = array("editable"=>false, "name"=>$row[0], "index"=>$row[0], "resizable"=>true);
	$json->colNames[] = $row[1];
}

echo json_encode($json);
?>

