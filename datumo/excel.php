<?php
header("Content-type: application/vnd.ms-excel;");
header("Content-Disposition: attachment; filename='export-to-excel.xls'; ");
require_once "session.php";
$user_id=startSession();


if(isset($_GET['oper'])){
	$buffer = $_POST['csvBuffer'];
	echo $buffer;
	exit();
}

//http variables
if(isset($_GET['table'])){	
	//get url variable
	$table = $_GET['table'];
	//redirect to specific method
	fromTable($table,$user_id);
}

if(isset($_GET['report'])){	
	//get url variable
	$report = $_GET['report'];
	//redirect to specific method
	fromReport($report,$user_id);
}



/**
*Method to export data directly from a table to excel
*
**/

function fromTable($table){
	//sets unlimited timerange to execute script (no timeout)
	set_time_limit(0); 
	
	//includes
	require_once("__dbConnect.php");
	require_once ("dispClass.php");
	
	//call database class
	$db = new dbConnection();
	//call other classes
	$display = new dispClass();
	
	//local variables
	$fullarr=array();
	$arr=array();
	$fk=array();
	
	//session variables
	$query=$_SESSION['sql'];
	$_SESSION['sql']=null;
	
	//call method to get table headers
	$display->tableHeaders($table);
	
	$arr=$display->getHeader();
	$fk=$display->getFKtable();
	
	for($i=0;$i<sizeof($arr);$i++){
		echo $arr[$i]."\t";
	}
	echo "\n";
	//execute query to retrieve data from the database
	$sql = $db->prepare($query);
	$sql->execute();
	for($i=0;$row=$sql->fetch();$i++){
		for($j=0;$j<$sql->columnCount();$j++){
			if($fk[$j]!=$table and $fk[$j]!=""){
				$display->getFKvalue($row[$j],$j);
				echo $display->getFKatt()."\t";	
			} else {
				echo $row[$j]."\t";
			}
		}
		echo "\n";
	}
}

/**
*Method to export data from a report which query is stored in the database
*
**/

function fromReport($report) {
	//sets unlimited timerange to execute script (no timeout)
	set_time_limit(0); 
	
	//includes
	require_once "__dbConnect.php";
	require_once "dispClass.php";
	
	//call database class
	$conn = new dbConnection();
	
	//report id number
	if(isset($_GET['report_id']))	$report_id=$_GET['report_id'];
	//extra parameters operators
	if(isset($_GET['extra_op'])){
		$extra_op=$_GET['extra_op'];
	} else {
		$extra_op=null;
	}
	//extra parameters fields
	if(isset($_GET['extra_fields'])){
		$extra_fields=$_GET['extra_fields'];
	} else {
		$extra_fields=null;
	}
	//column names
	if(isset($_GET['columns']))	{
		$column_names=$_GET['columns'];
		$column_names=explode(",", $column_names);
	}
	
	//get columns names
	$cols=array();
	foreach ($column_names as $column){
		$query="SELECT reprop_mask FROM reprop WHERE reprop_report=$report_id AND reprop_attribute='$column'";
		$sql=$conn->query($query);
		$row=$sql->fetch();
		$cols[]=$row[0];	//set columns names into an array
	}
	
	
	//query the database for this report
	$query="SELECT report_query FROM report WHERE report_id=$report_id";
	$sql=$conn->query($query);
	$row=$sql->fetch();
	$query=$row[0];	//set query
	
	//write column headers to the excel sheet
	foreach ($cols as $header){
		echo $header."\t";
	}
	//start a new line
	echo "\n";
	
	//remove page limitation
	$sql=$conn->query($query);
	//loop through all results and write each one of them to a spreadsheet
	for($i=0;$row=$sql->fetch();$i++){
		for($j=0;$j<$sql->columnCount();$j++){
			echo $row[$j]."\t";
		}
		echo "\n";
	}	
}

?>