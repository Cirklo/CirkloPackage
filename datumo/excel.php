<?php
header("Content-type: application/vnd.ms-excel;");
header("Content-Disposition: attachment; filename='export-to-excel.xls'; ");
require_once "session.php";
$user_id=startSession();
require_once "errorClass.php";
//call class to handle errors
$error=new errorClass();

if(isset($_GET['oper'])){
	$buffer = $_POST['csvBuffer'];
	try{
	    echo $buffer;
	} catch (Exception $e){
		//error report
		$error->errorDisplay("Excel export through jqgrid",$objName,$e->getMessage());
	}
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
	$db = new dbConnection();
	
	//get session variable
	//echo $_SESSION['sql'];
	$query=$_SESSION['sql'];
	//SESSION['sql']=null;
	//echo $query;
	
	//remove page limitation
	$query=substr($query,0,strpos($query, "LIMIT"));
	$sql=$db->prepare($query);
	try{
		$sql->execute();
	} catch (Exception $e){
		echo $e->getMessage();
	}
	
	//loop through all results and write each one of them to a spreadsheet
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

?>