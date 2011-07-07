<?php

/**
    * @author João Lagarto
    * @copyright 2010 João Lagarto
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract Class to automatically import csv files to mysql
    */ 

require_once("__dbConnect.php");
class importer{
	public $db;

	public function __construct(){
		$this->db = database(1);
	}
	
	/**
    * @author João Lagarto
    * @copyright 2010 João Lagarto
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract method to delete all rows that are not referenced elsewhere (very specific query) (external products)
    */ 
	
	function delete ($table, $match, $field){ //need to add a supplier condition/clause
		mysql_select_db($this->db);
		$sql = "DELETE FROM $table WHERE ".$table."_id NOT IN (SELECT product_id FROM product, request WHERE product_id=request_number AND request_origin='product') AND ".$table."_id<>0 AND $field IN (SELECT vendor_id FROM vendor WHERE vendor_name='$match') AND product_type=1";
		//echo $sql."<br>";
		$res = mysql_query($sql) or die (mysql_error().$sql);
		echo "<b>Completed</b>!<br><br>";
	}
	
	function checkMatch($value, $field, $table, $match, $column){
		mysql_select_db($this->db);
		$sql = "SELECT 1 FROM $table WHERE ".$table."_".$field."='$value'";// AND ".$table."_".$column." IN (SELECT ".$column."_id FROM $column WHERE ".$column."_name='$match')";
		$res = mysql_query($sql) or die (mysql_error().$sql);
		$nrows = mysql_num_rows($res);
		if($nrows == 0){ // no match found on the database (NEED TO INSERT NEW ROW)
			return TRUE;
		} else { //match found (UPDATE ROW)
			return FALSE;
		}
		
	}
	
	function add($table,$fields,$values){
		mysql_select_db($this->db);
		$sql = "INSERT INTO $table ($fields) VALUES ($values);";
		$res = mysql_query($sql) or die (mysql_error());
		//echo $sql."<br>";
	}
	
	function update($table, $values, $unique, $id){
		mysql_select_db($this->db);
		$sql = "UPDATE $table SET $values WHERE $unique='$id'";
		$res = mysql_query($sql) or die (mysql_error());
		//echo $sql."<br>";
		
	}
	
}

?>