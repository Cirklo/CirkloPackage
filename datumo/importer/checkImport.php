<script type='text/javascript'>

function startImport(file, table, field, column, match,but){
	var resp = confirm("Sure you want to proceed?");
	if(resp){
		alert("This can take up to a few hours. Do not close the browser while it is processing. Click OK to continue!");
		window.location = "startImport.php?file=" + file + "&table=" + table + "&field=" + field + "&column=" + column + "&match=" + match;
		but.enabled=false;	
	} else {
		alert("Data import canceled");
	}
}


</script>
<?php
require_once ("__dbConnect.php");
require_once ("classcheck.php");
//require_once ("classimport.php");

set_time_limit(0); //sets unlimited timerange (no timeout)

$db = database(1);
$headers = array();
$errors = array();
$isNull = array();
$type = array();
$FKtable = array();
//call classes
$check = new checkImport;
//$import = new importer;

//values from form
$table = $_GET['table'];
$field = $_GET['field'];
$column = $_GET['matchcol'];
$match = $_GET['match'];
$file = $_FILES["file"]["name"];

if ($_FILES["file"]["error"] > 0){ //no file uploaded
	echo "Error: " . $_FILES["file"]["error"] . "<br />";
} else { //check data for errors
	$path = "/var/www/datumo2.0/importer/upload/".basename($_FILES["file"]["name"]);
	
	//file validation
	//if($_FILES["file"]["type"] != "application/vnd.ms-excel"){
	if($_FILES["file"]["type"] != "text/csv" and $_FILES["file"]["type"] != "application/vnd.ms-excel"){
		echo "File cannot be uploaded. Please upload a .csv file!";
		exit();
	}
	if(!move_uploaded_file($_FILES["file"]["tmp_name"],$path)){
		echo "File not uploaded to server! Please try again...";
		exit();
	}
	echo "Checking data for errors... Please wait!<br><br>";
	$row = 0;
	if (($handle = fopen($path, "r")) !== FALSE) {
		//loop for checking
		while(($data = fgetcsv($handle, 1000, ","))!== FALSE){	//(file, line length in chars, delimiter)
			$num = count($data); 	    //number of columns found in .CSV
			$row++;
			if($row == 1){ //headers
				for ($i=0; $i < $num; $i++) {
					$headers[$i] = $data[$i]; //write columns headers
					mysql_select_db("information_schema");
					if($num <= $check->num_columns($table)){ //matching number of columns//	
						if($check->column_name($table,$data[$i])){ 
							//CHECK OK -> write headers
							//echo $data[$i]."\t";
							$headers[$i] = $table."_".$data[$i];
							$isNull[$headers[$i]] = $check->checknull($headers[$i], $table);
							$type[$headers[$i]] = $check->checktype($headers[$i], $table);
							$FKtable[$headers[$i]] = $check->checkFK($headers[$i], $table);
						} else { //column names don't match
							echo "<br><br>Column names don't match!";
							exit(); //CRITICAL ERROR
						}				
					} else { //number of columns don't match
						echo "<br><br>Number of columns don't match!";
						exit(); //CRITICAL ERROR
					}
				}
			} else { //row after headers
				for ($i=0; $i < $num; $i++) {
					if($headers[$i] == $table."_".$column){ //if we are in the matching field
						if($data[$i] != $match){ //garantees that only one supplier is inserted
							echo "<br><br>Error: keys don't match! You must have only one $column!";
							exit(); //CRITICAL ERROR
						}
					}
					if($FKtable[$headers[$i]] == ''){ //IF it is not a foreign key
						//check data for: null values and wrong datatype
						if($check->isNull($isNull[$headers[$i]],$data[$i])){
							if(!$check->checkdata($type[$headers[$i]], $data[$i])){//check datatype
								array_push($errors, $row);
							}
						} else {
							array_push($errors, $row);
							//echo $data[$i];
						}
					} else { //it is a foreign key
						if($data[$i] == '') {
							array_push($errors, $row);
						}
					}		
				}
			}
		}
	}
	if(sizeof($errors) == 0){
		echo "List successfully checked. File is ready to import!<br><br>";
		echo "Click the button to start importing the data: ";
	} else {
		echo "<b>Report</b><br>";
		echo "File has <b>".sizeof($errors)."</b> error(s). ";
		echo "Errors found on:<br>";
		for($i = 0; $i<sizeof($errors); $i++){
			echo "Line $errors[$i]<br>";
		}
		echo "<br>";
		echo "Click the button to start importing the data. <b>Note that the data where the errors were found will not be imported!</b>";
	}
	echo "<br><br>";
	echo "<input type=button id=startimp value='Start Import' onclick=\"javascript:startImport('$file','$table', '$field', '$column', '$match',this);\">";
	
}	


?>