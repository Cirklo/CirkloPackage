<?php
require_once("../session.php");
$user_id = startSession();

require_once "../__dbConnect.php";
require_once "../dispClass.php";
require_once "../queryClass.php";
require_once "../resClass.php";

//http variables
if(isset($_GET['table'])){	$table=$_GET['table'];}
if(isset($_GET['type'])){	$type=$_GET['type'];}
if(isset($_GET['state'])){	$state=$_GET['state'];}

//call classes
$conn = new dbConnection();
$display = new dispClass();
$database = $conn->getDatabase();

//is there any type variable?
if(isset($type)){
	//Grouping BASKET?
	$sql=$conn->prepare("SELECT type_grouping FROM $database.type WHERE type_name='$type'");
	$sql->execute();
	$row=$sql->fetch();
	$json->grouping[]=$row[0];
	
	$query="SELECT listconfig_name, listconfig_fieldname, bool_type, listconfig_datatype FROM $database.listconfig, $database.bool WHERE bool_id=listconfig_editable AND listconfig_type IN (SELECT type_id FROM $database.type WHERE type_name='$type') ORDER BY listconfig_id";
}

if(isset($state)){
	$query="SELECT basketconfig_name, basketconfig_fieldname, bool_type, basketconfig_datatype FROM $database.basketconfig, $database.bool WHERE bool_id=basketconfig_editable ORDER BY basketconfig_id";
}

//Query to get table properties
$sql = $conn->prepare($query);
//echo $sql->queryString;
$sql->execute();

for($i=0;$row=$sql->fetch();$i++){
	if($row[2]=="TRUE") $bool=true;
	else $bool=false;
	if($i==0 and isset($type))$hide=true;
	else{$hide=false;}
	if($i==1 and isset($type)) $width="350px";
	else $width=null;
	$json->colModel[] = array("editable"=>$bool, "name"=>$row[0], "index"=>$row[0], "resizable"=>true, "hidden"=>$hide, "edittype"=>$row[3], "width"=>$width, "align"=>"right");
	$json->colNames[] = $row[1];
	//search options 	
}

echo json_encode($json);
?>

