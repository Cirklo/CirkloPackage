<?php

//PHP includes
require_once "../session.php";
startSession();

if(isset($_GET['type'])){
	$type=$_GET['type'];
	switch ($type){
		case 0:	//get table attributes
			fill();
			break;
	}
}

function fill(){
	require_once "../__dbConnect.php";
	//call classes 
	$conn=new dbConnection();
	//get database
	$database=$conn->getDatabase();
	//http variables
	if(isset($_GET['table'])){ $table = $_GET['table'];}
	
	try{
		//change search path to information schema
		$conn->dbInfo();
		$sql = $conn->query("SELECT ordinal_position, column_name FROM columns WHERE table_name='$table' AND table_schema='$database'");
		for($i=0;$row=$sql->fetch();$i++){
			echo "<name>" . substr($row[1], strlen($table."_"), strlen($row[1]));
	    	echo "<value>" . $row[1];
		}
	} catch (Exception $e){
		echo $e->getMessage();
	}
}

?>