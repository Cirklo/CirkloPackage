<?php

//PHP includes
require_once "../session.php";
startSession();
require_once "classImport.php";

?>
<link href="css/importer.css" rel="stylesheet" type="text/css">
<link href="../css/jquery.jnotify.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="../js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="../js/jquery.jnotify.js"></script>
<script type="text/javascript" src="js/auxJS.js"></script>
<?php

//call classes 
$importer=new importerClass();

/** ***********************************************************************************************************************
 * TO DO LIST

5. Check datatypes, nullable fields, field size and foreign keys.

6. Create validations for these properties

7. First validation for errors. Loop through all file rows and validate all fields. Foreign keys must also be validated

Concerning FK, check if it is possible to insert new values in the referenced table. If not, throw error.

In the end of the validation, the script must show all errors found in the csv file.

8. After the validation, each row must be inserted along with the foreign key values
 
 ****************************************************************************************************************************/
echo "<div class=validation>";
//PHP defaults
$limitError=100000;	//maximum number of errors allowed
$uniqueValidation=array();
$delimiter=",";	//some spreadsheets might use ; instead of ,
$lineLenght="1000";
$row=0;	//set row starting number
$fileHeaders=array();
$shortHeaders=array();

//get posted variables
if(isset($_POST['targetTable'])){	//target table
	$objName=$_POST['targetTable'];
	$importer->setObjName($objName);
} else {
	$objName=null;
}

if(isset($_POST['targetUnique'])){	//unique field
	$unique=$_POST['targetUnique'];
	$importer->setUniqueKey($unique);
} else {
	$unique=null;
}

if(isset($_POST['cbMatching'])){	//matching key
	$matchingKey=$_POST['targetMatching'];
	$importer->setMatchingKey($matchingKey);
} else {
	$matchingKey=null;
}

if(isset($_POST['dataErase'])){		//delete option
	/*
	 * 0 - do not delete
	 * 1 - delete all data
	 * 2 - delete data related with the matching key
	 */
	$delOption=$_POST['dataErase'];
} else {
	$delOption=0;	//by default do not erase any data
}

if(isset($_FILES['file']['name'])){	//get file name
	$filename=$_FILES['file']['name'];
}


//start data validation
try{
	//define destination folder
	//path for windows servers
	$path="/xampp/htdocs/datumo/newImporter/files/".basename($filename);
	//path for linux servers
	//$path="/var/www/datumo/newImporter/files/".basename($filename);
	
	//check if the file has the right extension
	if($_FILES["file"]["type"] != "text/csv" and $_FILES["file"]["type"] != "application/vnd.ms-excel")
		throw new Exception ("File cannot be uploaded. Please upload a .csv file!");
	
	//check if the file can was uploaded or not
	if ($_FILES["file"]["error"] > 0) //no file uploaded
		throw new Exception($_FILES["file"]["error"]);
	
	//upload file to server
	if(!move_uploaded_file($_FILES["file"]["tmp_name"],$path))
		throw new Exception ("File not uploaded to server! Please try again...");

	//start Checking data for errors
	if (($handle = fopen($path, "r")) !== FALSE) {	//open the file
		//check and store table settings
		$importer->headerSettings();
		$fullheader=$importer->getFullheader();
		$header=$importer->getHeader();
		$isForeignKey=$importer->getForeignKeys();
		$nullable=$importer->getNulls();
		$datatype=$importer->getDatatype();
		$length=$importer->getMaxLength();

		//go through each row of the file
		while(($data = fgetcsv($handle, $lineLenght, $delimiter))!== FALSE){	//(file, line length in chars, delimiter)
			$row++;	//increment row counter
			$nColumns=count($data); 	    //number of columns found in .CSV file
			
			//check if the number of columns in the csv is greater than the number of table attributes
			if($nColumns > sizeof($fullheader))
				throw new Exception("Imported file has more columns than the target table");
			if($row==1){	//First row -> Column headers
				//check if the column names match
				if($arr=$importer->checkColumnNames($nColumns,$data))
					throw new Exception("The following column names do not match table headers: <br><br>".implode("<br>", $arr));
				for($i=0;$i<$nColumns;$i++){
					$fileHeaders[$i]=$objName."_".strtolower($data[$i]);
					$shortHeaders[$i]=$data[$i];
				}	
			} else {		//Remaining rows
				//loop through all columns of the file
				for($i=0;$i<$nColumns;$i++){
					//check if the matching key option was chosen
					if($matchingKey){
						//target the matching key header
						if($fileHeaders[$i]==$matchingKey){
							if($row==2){	//get matching key value from the row right after the table headers
								$matchingKeyValue=$data[$i];	//setting the matching key value for comparison
							} else {
								//throw exception if the values along this column do not match
								if($data[$i]!=$matchingKeyValue){	
									throw new Exception("Matching keys along the file do not match. Please verify $matchingKey column (Row $row)");
								}
							}
						}
					}
					//check if the unique key is really unique
					if($fileHeaders[$i]==$matchingKey){
						$uniqueValidation[]=$data[$i];
					}
					
					//is this field a foreign key or not??
					if($isForeignKey[$fileHeaders[$i]]){ //is a foreign key 
						//check if foreign key value exists or not
						if($data[$i]==""){
							$importer->importerErrors("null", $row, $shortHeaders[$i]);
						}
						
						//need to check data (if it is valid or not)
						//check if foreign key exists, if not throw warning
						//check if it matches the second attribute of the column
//						if(!$importer->fkExists($data[$i], $isForeignKey[$fullheader[$i]]))
//							$importer->importerErrors("fk", $row, $fullheader[$i]);
					} else { //its not a foreign key
						//Nulls validation
						if($nullable[$fileHeaders[$i]]=="NO" and $data[$i]=="")
							$importer->importerErrors("null", $row, $shortHeaders[$i]);
						//datatype validation
						if(!$importer->checkDatatype($datatype[$fileHeaders[$i]], $data[$i]))
							$importer->importerErrors("datatype", $row, $shortHeaders[$i]);
						
						//character validation
						if($datatype[$fileHeaders[$i]]=="varchar"){	//set for MYSQL tables only
							//check if the value char length is greater than the allowed
							if(strlen($data[$i])>$length[$fileHeaders[$i]]){	
								$importer->importerErrors("length", $row, $shortHeaders[$i]);
							}	
							//check for bad characters
							if(preg_match($importer->getRegexp(), $data[$i])){
								$importer->importerErrors("regexp", $row, $shortHeaders[$i]);
//								echo $data[$i]."<br>";
							}
						}
					}
				}
			}
		}
	}
	
	//check unique key array for validation
	$uniqueValidation=array_unique($uniqueValidation);
	if(sizeof($uniqueValidation)>1){
		throw new Exception("Values repeated in the unique key field");
	}
	
	//get rows that will not be imported
	$errorArray=array_unique($importer->getRowsWithErrors());
	//get rows affected by errors and warnings
	$arr=array_count_values($importer->getRowsAffected());

	//display success Message
	if($matchingKey){
		if(!$importer->fkExists($matchingKeyValue, $isForeignKey[$matchingKey]))
			throw new Exception ("Matching key not found in the database: $matchingKey -> $matchingKeyValue");
		else{
			echo "<div class=success>";
			echo "File successfully checked. Ready to start importing the file.";
			if($matchingKey){
				echo "<br><br>";
				echo "Your matching key is <input type=text name=matchingKey id=matchingKey value='$matchingKeyValue' size=50 readonly>";
			}
			echo "</div>";
		}
	}
	//are there any errors at all?
	if(sizeof($arr)>0){
		if(sizeof($arr)<$limitError){
			echo "<h2>Errors were found in the following rows:</h2>";
			foreach($arr as $key=>$value){
				echo "<b>$key</b>; ";
			}
		} else {
			throw new Exception("Too many errors were found in the imported file (more than $limitError)");
		}
	
		//color schema
		$warning="#FFCC11";
		$error="#FF0000";
		
		//Display errors and warnings
		$arr=array("<font color='$warning'>Invalid characters</font>"=>$importer->getRegexpWarnings(),	//bad characters warnings
				   "<font color='$warning'>Too many characters. String is too long</font>"=>$importer->getLengthWarnings(),	//too many characters per field 
				   "<font color='$warning'>Foreign key errors</font>"=>$importer->getForeignKeyWarnings(),
				   "<font color='$error'>Null fields</font>"=>$importer->getNullErrors(),
				   "<font color='$error'>Wrong datatype</font>"=>$importer->getDataErrors());		

		//display errors
		echo "<h2>Errors and Warnings</h2>";
		
		echo "<div class=errorDetails>";
		//loop through all error types
		foreach ($arr as $key=>$value){
			echo "<div class=smallDetails>";
			if(sizeof($value)){
				echo "<h3>$key</h3>";
				foreach ($value as $row){
					echo "$row<br>";
				}
			}
			echo "</div>";
		}
		echo "</div>";
		
		//Display legend
		echo "<hr>";
		echo "<div class=legend>";
		echo "<h3>Observation</h3>";
		echo "<font color='$warning'><b>WARNINGS</b></font> will be imported. The imported data may not be accurate<br>";
		echo "<font color='$error'><b>ERRORS</b></font> will not be imported<br>";
		echo "</div>";
		echo "<hr>";
	}
	
	//Display 3 links
	//go back link
	echo "<div class=links>";
	echo "<a href=options.php>back</a>&nbsp;&nbsp;";	
	//refresh link
	echo "<a href=javascript:window.location.reload()>refresh</a>&nbsp;&nbsp;";
	//start import
	echo "<a href=javascript:void(0) onclick=startImport('$objName','$unique','$matchingKey','$delOption','$path','".implode(",",$errorArray)."')>start import</a>";
	echo "</div>";
} catch (Exception $e){
	echo "<div class=error>";
	echo "<font color=#FF0000>Error:</font> ".$e->getMessage().$query;
	echo "</div>";
	echo "<div class=links>";
	//go back link
	echo "<a href=options.php>back</a>&nbsp;&nbsp;";	
	//refresh link
	echo "<a href=javascript:window.location.reload()>refresh</a>&nbsp;&nbsp;";
	echo "</div>";
}
echo "</div>";
?>