<?php

/**
    * @author João Lagarto
    * @copyright 2010 João Lagarto
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract Class to automatically import csv files to mysql
    */ 

require_once("__dbConnect.php");

class checkImport{
	public $db;

	public function __construct(){
		$this->db = database(1);
	}
	
	/**
    * @author João Lagarto
    * @copyright 2010 João Lagarto
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract method to count the number of columns from the table we which to update
    */ 
	
	function num_columns($table){  
		$sql = "SELECT COLUMN_NAME FROM COLUMNS WHERE TABLE_NAME='$table' AND TABLE_SCHEMA='$this->db'";
		$res = mysql_query($sql) or die (mysql_error());
		return mysql_num_rows($res);
	}
	
	/**
    * @author João Lagarto
    * @copyright 2010 João Lagarto
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract method that returns the column names from the table we which to update
    */ 
	
	function column_name($table,$field){ 
		mysql_select_db("information_schema");
		$sql = "SELECT COLUMN_NAME FROM COLUMNS WHERE TABLE_NAME='$table' AND TABLE_SCHEMA='$this->db' AND COLUMN_NAME='".$table."_".$field."'";
		$res = mysql_query($sql) or die (mysql_error());
		$nrows = mysql_num_rows($res);
		if($nrows == 0){ //no match
			return FALSE;
		} else { //matching columns
			return TRUE;
		}
	}
	
	/**
    * @author João Lagarto
    * @copyright 2010 João Lagarto
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract check if the input field references another table (Foreign key check)
    */
	
	function checkFK($field, $table){
		mysql_select_db("information_schema");
		$sql = "SELECT REFERENCED_TABLE_NAME FROM KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = '$this->db' AND COLUMN_NAME='$field' AND TABLE_NAME='$table' AND REFERENCED_TABLE_NAME<>'NULL'";
		$res = mysql_query($sql) or die (mysql_error());
		$nrows = mysql_num_rows($res);
		$row = mysql_fetch_row($res);
		return $row[0];
		
	}
	
	
	/**
    * @author João Lagarto
    * @copyright 2010 João Lagarto
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract Checks if column is referenced or not
    */
	
	function isFK($column){
		if($column == ''){
			return false;
		} else {
			return true;
		}
	}

	/*
	function checkFK($field, $value, $table, $j, $type){
		mysql_select_db("information_schema");
		$sql = "SELECT REFERENCED_TABLE_NAME FROM KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = '$this->db' AND COLUMN_NAME='$field' AND TABLE_NAME='$table' AND REFERENCED_TABLE_NAME<>'NULL'";
		$res = mysql_query($sql) or die (mysql_error());
		$nrows = mysql_num_rows($res);
		$row = mysql_fetch_row($res);
		if($nrows == 0){ //field is not a foreign key
			if(!$this->checknull($field, $table, $value)){ //check is value can be null
				if(!$this->checkdata($field, $table, $value)){ //check if the type is correct for field
					return "*ERROR*:".$j;
					//exit();
				} else {
					if($type == 'import'){
						return $value;
					}
				}
			} else { 
				return "*ERROR*:".$j;
				//exit();
			}
		} else { //field is a foreign key
			if($value == ''){ //field is a foreign key but value is missing
				return "*ERROR*:".$j;
			} else {
				if($type == 'import'){ //after check
					$FKvalue = $this->FKreturn($row[0], $value, $j);
					return $FKvalue;
				}
			}
		}		
		
	}
	*/

	/**
    * @author João Lagarto
    * @copyright 2010 João Lagarto
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract returns the primary key of the referenced table
    */
	
	function FKreturn($FKtable, $value){
		mysql_select_db($this->db);
		//show fields from referenced table
		$sql = "show fields from $FKtable";
		$res = mysql_query($sql) or die (mysql_error());
		//get first field name
		mysql_data_seek($res,0);
		$field1 = mysql_fetch_row($res);
		//get second field name
		mysql_data_seek($res,1);
		$field2 = mysql_fetch_row($res); 
		//query to check if the written value has an ID
		$sql = "SELECT ".$field1[0]." FROM $FKtable WHERE ".$field2[0]." LIKE '%$value%'";
		//echo $sql."<br>";
		$res = mysql_query($sql) or die (mysql_error());
		$nrows = mysql_num_rows($res);
		if($nrows == 0){ //no primary key for this value-> need to create new entry (CREATE METHOD FOR THIS)
			mysql_select_db($this->db); //assuming that all fk type is correct
			$sql = "INSERT INTO $FKtable (".$field1[0].",".$field2[0].") VALUES ('NULL','$value')";
			$res = mysql_query($sql) or die (mysql_error());
			return mysql_insert_id();
		} else {
			$row = mysql_fetch_row($res);
			return $row[0];
		}
	}
	
	/*
	function FKreturn($FKtable, $value, $j){
		mysql_select_db($this->db);
		//show fields from referenced table
		$sql = "show fields from $FKtable";
		$res = mysql_query($sql) or die (mysql_error());
		//get first field name
		mysql_data_seek($res,0);
		$field1 = mysql_fetch_row($res);
		//get second field name
		mysql_data_seek($res,1);
		$field2 = mysql_fetch_row($res); 
		//query to check if the written value has an ID
		$sql = "SELECT ".$field1[0]." FROM $FKtable WHERE ".$field2[0]."='$value'";
		$res = mysql_query($sql) or die (mysql_error());
		$nrows = mysql_num_rows($res);
		if($nrows == 0){ //no primary key for this value-> need to create new entry (CREATE METHOD FOR THIS)
			if($this->checkdata($field2[0], $FKtable, $value)){ //check field datatype
				mysql_select_db($this->db);
				$sql = "INSERT INTO $FKtable (".$field1[0].",".$field2[0].") VALUES ('NULL','$value')";
				$res = mysql_query($sql) or die (mysql_error());
			} else { //WRONG datatype
				return "*ERROR*:".$j;
				//exit();
			}
		}
		//new query to check the written value ID
		$sql = "SELECT ".$field1[0]." FROM $FKtable WHERE ".$field2[0]."='$value'";
		$res = mysql_query($sql) or die (mysql_error());
		$nrows = mysql_num_rows($res); 
		$row = mysql_fetch_row($res);
		return $row[0];	
	}
	*/
	/**
    * @author João Lagarto
    * @copyright 2010 João Lagarto
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract checks if value is valid
    */
	
	function checkdata($field, $value){
		switch ($field){
			case "int":
				$regexp = "/[^0-9]/"; //all characters but numbers
				if(!preg_match($regexp, $value)) return TRUE;
				else return FALSE;
				break;
			case "double":
				$regexp = "/[^0-9\.]/"; //all characters but numbers and dot
				if(!preg_match($regexp, $value)) return TRUE;
				else return FALSE;
				break;
			case "varchar": //all characters
				return TRUE;
				/*$regexp = "/[^A-Za-z0-9\-\.\,\(\)\&\%\*\®\'\"]/"; // all characters with the exception of special symbols
				if(!preg_match($regexp, $value)) return TRUE;
				else return FALSE;*/
				break;
			case "date": //don't know yet how to check date (maybe a hack is needed)
				return TRUE;
				break;
		}	
	}
	
	/**
    * @author João Lagarto
    * @copyright 2010 João Lagarto
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract Checks field datatype
    */
	
	function checktype($field, $table){
		mysql_select_db("information_schema");
		$sql = "SELECT DATA_TYPE FROM COLUMNS WHERE TABLE_SCHEMA='$this->db' AND COLUMN_NAME='$field' AND TABLE_NAME='$table'";
		$res = mysql_query($sql) or die (mysql_error());
		$row = mysql_fetch_row($res);
		return $row[0];
	}

	/**
    * @author João Lagarto
    * @copyright 2010 João Lagarto
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract checks if a certain field is null or not
    */
	
	function checknull($field, $table){
		mysql_select_db("information_schema");
		$sql = "SELECT IS_NULLABLE FROM COLUMNS WHERE TABLE_SCHEMA='$this->db' AND COLUMN_NAME='$field' AND TABLE_NAME='$table'";
		$res = mysql_query($sql) or die (mysql_error().$sql);
		$row = mysql_fetch_row($res);
		return $row[0];
	}
	
	/**
    * @author João Lagarto
    * @copyright 2010 João Lagarto
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract Is this a null value on a non nullable field?
    */
	
	function isNull($field, $value){
		if($field == "NO" and $value == ""){
			return FALSE;			
		} else {
			return TRUE;
		}
	}
	
}

?>