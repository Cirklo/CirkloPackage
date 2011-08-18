<?php
//PHP includes
require_once "../session.php";
startSession();
require_once "classImport.php";

//classes to use
$conn=new dbConnection();
$importer=new importerClass();

//PHP defaults
$delimiter=",";	//some spreadsheets might use ; instead of ,
$lineLenght="1000";
$row=0;	//set row starting number
$dataArray=array();
$counter=0; //count the number of inserted rows

//Set Posted variables
if(isset($_POST['objName'])){
	$objName=$_POST['objName'];
	$importer->setObjName($objName);
}
if(isset($_POST['unique'])){
	$unique=$_POST['unique'];
	$importer->setUniqueKey($unique);
}
if(isset($_POST['matchingKey'])){
	$matchingKey=$_POST['matchingKey'];
	$importer->setMatchingKey($matchingKey);
} else {
	$matchingKey=null;
}

if(isset($_POST['matchingKeyValue'])){
	$matchingKeyValue=$_POST['matchingKeyValue'];
	$importer->setMatchingKeyValue($matchingKeyValue);
} else {
	$matchingKeyValue=null;
}
if(isset($_POST['delOption']))	$delOption=$_POST['delOption'];
if(isset($_POST['path']))		$path=$_POST['path'];
if(isset($_POST['error'])){
	$error=$_POST['error'];
	$errorArray=explode(",", $error);
}

//check delete option. Retrive sql query if that's the case
try{
	if($query=$importer->delete($delOption)){
		$conn->query($query);
	}
	$del=true;
} catch (Exception $e){
	$del=false;
}

//start sql transaction
$conn->beginTransaction();

//start counting the time
$startTime=date("d/m/y : H:i:s", time());

try{
	if(!$del)	throw new Exception("Unable to delete data from the database");
	//start importing the file	
	if (($handle = fopen($path, "r")) !== FALSE) {
		//check and store table settings
		$importer->headerSettings();
		$fullheader=$importer->getFullheader();
		$header=$importer->getHeader();
		$isForeignKey=$importer->getForeignKeys();
		
		//loop through every file row
		while(($data = fgetcsv($handle, $lineLenght, $delimiter))!== FALSE){	//(file, line length in chars, delimiter)
			$row++;
			$nColumns=count($data); 	    //number of columns found in .CSV file
			if($row==1){
				for($i=0;$i<$nColumns;$i++){
					$fileHeaders[$i]=$objName."_".strtolower($data[$i]);
					$importer->setFileHeaders($fileHeaders);
					$shortHeaders[$i]=$data[$i];
				}
				continue;			
			}
			//check if the row has an error
			if(!in_array($row, $errorArray)){
				$counter++;
				//check the table for the unique key. If this value exists, just update, if it doesn't insert
				for($i=0;$i<$nColumns;$i++){
					if($isForeignKey[$fileHeaders[$i]])//is a foreign key 
						$data[$i]=$importer->getForeignKeyValue($data[$i],$isForeignKey[$fileHeaders[$i]]);
					//write data to array
					$dataArray[]=utf8_encode($data[$i]);
					//check which action to take
					if($fileHeaders[$i]==$unique){
						$action=$importer->checkUnique($data[$i]);
						//store unique value 
						$uniqueValue=$data[$i];
					}
					//do action when it reaches the last row
					if($i==$nColumns-1){
						switch($action){
							case "UPDATE":
								$query=$importer->update($dataArray, $uniqueValue);
								break;
							case "INSERT":
								$query=$importer->insert($dataArray);
								break;
						}
					}
				}
				// query teh database
				$sql=$conn->query($query);
//				echo $query;
			} 			

			//clear array and query string
			unset($dataArray);
			unset($query);
		}
	}
		
		
	//commit SQL changes
	$conn->commit();
	$endTime=date("d/m/y : H:i:s", time());
	echo "Changes commited. $counter rows successfully imported<br>";
	echo "Start time: $startTime<br>";
	echo "End Time: $endTime";
} catch (Exception $e){
	$conn->rollBack();
	echo $e->getMessage();
	
}
?>