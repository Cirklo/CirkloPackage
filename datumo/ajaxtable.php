<?php

/**
 * @author João Lagarto	/ Nuno Moreno
 * @abstract Ajax handler request -> get column names through ajax
 */
require_once "session.php";
$user_id=startSession();
require_once("__dbConnect.php");

//call classes to handle database connection and to build queries for different engines
$conn = new dbConnection();
$engine = $conn->getEngine();
$database = $conn->getDatabase();

//change database to information schema
$conn->dbInfo();

//distinguish branches
if(isset($_GET['type'])){ $type = $_GET['type'];}

switch ($type){
	case 0:
		//http variables
		if(isset($_GET['table'])){ $table = $_GET['table'];}
			
		$sql = $conn->prepare("SELECT ordinal_position, column_name FROM columns WHERE table_name='$table' AND table_schema='$database'");
		$sql->execute();
		for($i=0;$row=$sql->fetch();$i++){
			echo "<name>" . substr($row[1], strlen($table."_"), strlen($row[1]));
		    echo "<value>" . $row[1];
		}
		break;
	case 1:
		//call specific classes to this branch
		require_once ("dispClass.php");
		/*
		require_once ("queryClass.php");	
		require_once ("resClass.php");
		require_once ("searchClass.php");
		require_once ("reportClass.php");
		require_once ("msgClass.php");
		*/
		
		$display = new dispClass();
		
		//http variables
		if(isset($_GET['table'])){ $table = $_GET['table'];}
		if(isset($_GET['field'])){ $field = $_GET['field'];}
		
		//get table headers' details
		$display->tableHeaders($table);
		$arr = array();
		$arr = $display->getFKtable();
		
		//ordinal_position-1 to match the array indexes
		$sql = $conn->prepare("SELECT data_type, ordinal_position-1 FROM columns WHERE table_schema='".$database."' AND table_name='$table' AND column_name='$field'");
		$sql->execute();
		$row = $sql->fetch();
		//is it a foreign key?
		if($arr[$row[1]]!="" and $arr[$row[1]]!=$table){ 
			echo $arr[$row[1]];
		} else { //if not then set datatype as is
			echo $row[0];
		}
		
}

?>