<?php
require_once "../session.php";
require_once "../__dbConnect.php";

//get posted variables so I can understand what is the operation
if(isset($_POST['oper'])) $oper=$_POST['oper'];
if(isset($_POST['id'])) $id=$_POST['id'];
if(isset($_GET['table']))	$table=$_GET['table'];

//call database class and open connection
$conn = new dbConnection();

//initialize variables to construct the query
$attr=""; //table attributes
$vals=""; //values to be inserted
switch($oper){
	case 'edit':
		foreach ($_POST as $key=>$value){
			if($key=="oper") break;
			$attr.= $key."='$value',";
		}
		$attr = substr($attr,0,strlen($attr)-1);
		$query = "UPDATE $table SET $attr WHERE ".$table."_id=$id";
		break;
	case 'del':
		$query = "DELETE FROM $table WHERE ".$table."_id=$id";
		break;
}

$sql = $conn->prepare($query);
try{
	$sql->execute();
} catch(Exception $e){
	//do nothing
}

	
?>