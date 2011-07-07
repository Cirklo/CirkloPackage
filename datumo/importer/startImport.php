<?php
require_once ("__dbConnect.php");
require_once ("classcheck.php");
require_once ("classimport.php");

set_time_limit(0); //sets unlimited timerange (no timeout)

$db = database(1);
$headers = array();
$errors = array();
//call classes
$check = new checkImport;
$import = new importer;

//values from checking
$table = $_GET['table'];
$field = $_GET['field'];
$column = $_GET['column'];
$match = $_GET['match'];
$file = $_GET['file'];	
$file = "/var/www/datumo2.0/importer/upload/".$file;

echo date("d/m/y : H:i:s", time());
echo "<br>";
echo "Deleting non referenced data... "; 
$import->delete($table, $match, $table."_".$column);
//initialize counters
$row = 0;
$counter = 0; //number of inserted rows
$error=0;
$fields="";
$add="";
$upd="";
if (($handle = fopen($file, "r")) !== FALSE) {
	//loop for checking
	while(($data = fgetcsv($handle, 1000, ","))!== FALSE){	//(file, line length in chars, delimiter)
		$num = count($data); 	    //number of columns found in .CSV
		$row++;
		if($row == 1){ //headers
			for ($i=0; $i < $num; $i++) {
				$headers[$i] = $table."_".$data[$i];
				$isNull[$headers[$i]] = $check->checknull($headers[$i], $table);
				$type[$headers[$i]] = $check->checktype($headers[$i], $table);
				$FKtable[$headers[$i]] = $check->checkFK($headers[$i], $table);
			}
		} else { //rows after headers
			for ($i=0; $i < $num; $i++) {
				//check data for: null values and wrong datatype
				if($FKtable[$headers[$i]] == ''){ //IF it is not a foreign key
					//check data for: null values and wrong datatype
					if($check->isNull($isNull[$headers[$i]],$data[$i])){
						if(!$check->checkdata($type[$headers[$i]], $data[$i])){//check datatype
							$error = 1;
						}
					} else {
						$error = 1;
						//echo $data[$i];
					}
				} else { //it is a foreign key
					if($data[$i] == '') {
						$error = 1;
					} else {
						$data[$i] = $check->FKreturn($FKtable[$headers[$i]], $data[$i]); //write referenced value
						//echo $data[$i]."<br>"; //DEBUG print
						//echo $headers[$i];
					}
				}
				if(strtolower($headers[$i]) == strtolower($table."_".$field)){ //unique column can't be a foreign key
					$action = $import->checkMatch($data[$i], $field, $table, $match, $column);
					$ref = $data[$i]; //unique value for this row 
				}
			}
			
			if($error == 1){ //error found, skip to the next iteration of the loop
				$error=0;
				continue;
			}
			
			if($action){ //INSERT ACTION
				for ($i=0; $i < $num; $i++) { 
					$fields .= $headers[$i].",";
					$value = $data[$i];
					$value = str_replace("'"," ", $value);
					$value = str_replace("\""," ", $value);
					$add .= "'".$value."',";
				}
				$fields = substr($fields, 0, strlen($fields)-1);
				$add = substr($add, 0, strlen($add)-1);
				$import->add($table, $fields, $add);
				$fields = '';
				$add = '';
			} else { //UPDATE
				for ($i=0; $i < $num; $i++) {
					$value = $data[$i];
					$value = str_replace("'"," ", $value);
					$value = str_replace("\""," ", $value);
					$upd .= $headers[$i]."='".$value."',";
				}
				$upd = substr($upd, 0, strlen($upd)-1);
				$import->update($table, $upd, $table."_".$field, $ref);
				$upd = '';
			}
			$counter++;
		}
	}
}

fclose($handle);

echo "$counter lines successfully imported. Thank you for using <b>datumo<sup>&reg;</sup> Importer tool</b>.<br>";
echo date("d/m/y : H:i:s", time()) ;
?>