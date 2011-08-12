<?php

class importerClass{
	private $conn;				//database connection
	private $objName=null;		//target table
	private $unique=null;		//unique key
	private $matchingKey=null;	//matching key field
	private $matchingKeyValue=null;
	private $header=array();
	private $fullheader=array();
	private $datatype=array();
	private $nullable=array();
	private $maxlength=array();
	private $default=array();
	private $isForeignKey=array();
	private $columnLink=array();
	private $fileHeaders=array();
	//define error arrays
	private $error_nulls=array();
	private $error_data=array();
	private $warning_fk=array();
	private $warning_length=array();
	private $warning_regexp=array();
	private $rowsAffected=array();
	private $rowsWithErrors=array();
	//allowed characters
	private $regexp="/[^a-zA-Z0-9_ %\.\/\?\&\,\@\.\:\(\)%&-]/";	
	
	
	function __construct(){
		$this->conn=new dbConnection();
	}
	
	public function setObjName($arg){		$this->objName=$arg;}
	public function setUniqueKey($arg){		$this->unique=$arg;}
	public function setMatchingKey($arg){	$this->matchingKey=$arg;}
	public function setMatchingKeyValue($arg){	$this->matchingKeyValue=$arg;}
	public function setErrors($arg){		$this->error=$arg;}
	public function setFileHeaders($arg){	$this->fileHeaders=$arg;}
	
	public function getObjName(){		return $this->objName;}
	public function getUniqueKey(){		return $this->unique;}
	public function getMatchingKey(){	return $this->matchingKey;}
	public function getMatchingKeyValue(){	return $this->matchingKeyValue;}
	public function getHeader(){		return $this->header;}
	public function getFullheader(){	return $this->fullheader;}	
	public function getForeignKeys(){	return $this->isForeignKey;}
	public function getNulls(){			return $this->nullable;}
	public function getDatatype(){		return $this->datatype;}
	public function getMaxLength(){		return $this->maxlength;}
	public function getRegexp(){		return $this->regexp;}
	public function getNullErrors(){	return $this->error_nulls;}
	public function getDataErrors(){	return $this->error_data;}
	public function getForeignKeyWarnings(){	return $this->warning_fk;}
	public function getLengthWarnings(){return $this->warning_length;}
	public function getRegexpWarnings(){return $this->warning_regexp;}
	public function getRowsAffected(){	return $this->rowsAffected;}
	public function getRowsWithErrors(){return $this->rowsWithErrors;}
	
	function headerSettings(){
		//set search path to information schema
		$this->conn->dbInfo();
		$query="SELECT column_name, data_type, is_nullable, character_maximum_length, column_default 
		FROM columns 
		WHERE table_schema='".$this->conn->getDatabase()."' 
		AND table_name='".$this->objName."'";
		try{
			$sql=$this->conn->query($query);
			//loop through all table attributes
			for($i=0;$row=$sql->fetch();$i++){
				$this->header[]=substr($row['column_name'],strlen($this->objName)+1,strlen($row['column_name'])-strlen($this->objName)+1);
				$this->fullheader[]=$row['column_name'];
				$this->datatype[$row['column_name']]=$row['data_type'];
				$this->nullable[$row['column_name']]=$row['is_nullable'];
				$this->maxlength[$row['column_name']]=$row['character_maximum_length'];		
				$this->default[$row['column_name']]=$row['column_default'];		
				$this->isForeignKey[$row['column_name']]=$this->FindForeignKeys($row['column_name']);
			}
		} catch (Exception $e){
			echo $e->getMessage();
		}
//		print_r($this->header);
//		echo "<br>";
//		print_r($this->fullheader);	
//		echo "<br>";
//		print_r($this->datatype);
//		echo "<br>";
//		print_r($this->nullable);
//		echo "<br>";
//		print_r($this->maxlength);
//		echo "<br>";
//		print_r($this->default);
//		echo "<br>";
//		print_r($this->isForeignKey);
	}
	
	function FindForeignKeys($fullheader){
		//check if the field is a foreign key
		$query="SELECT referenced_table_name 
		FROM key_column_usage 
		WHERE referenced_table_name<>'NULL' 
		AND table_schema='".$this->conn->getDatabase()."' 
		AND column_name='$fullheader'";
		$sql=$this->conn->query($query);
		$row=$sql->fetch();
		//if it is foreign key, then we must search for the second attribute of the table
		if($row[0]!="") {
			$query_="SELECT column_name FROM columns 
			WHERE table_schema='".$this->conn->getDatabase()."' 
			AND table_name='$row[0]'
			AND ordinal_position=2";
			//change search path to information schema
			$this->conn->dbInfo();
			$sql_=$this->conn->query($query_);
			$row_=$sql_->fetch();
			$this->columnLink[$row[0]]=$row_[0];
		}
		return $row[0];
	}
	
	public function checkColumnNames($nColumns,$data){
		//initialize error array
		$error=array();
		//loop through all columns in the .csv file and check for a match in the database
		for($i=0;$i<$nColumns;$i++){
			$query="SELECT 1 
			FROM columns 
			WHERE table_schema='".$this->conn->getDatabase()."'
			AND table_name='".$this->objName."' 
			AND (column_name='".$this->objName."_".$data[$i]."'
			OR column_name='".$data[$i]."')";
			$sql=$this->conn->query($query);
			//if no match is found
			if($sql->rowCount()==0){
				$error[]=$data[$i];
			} 
		}
		//no errors were found. The file is good to go
		if(sizeof($error)==0){
			return false;
		} else {
			return $error;
		}
	}
	
	function checkDatatype($columnType,$value){
		//check field data type
		switch($columnType){	//integers for MYSQL
			case "int":
				return is_numeric($value);
				break;
			case "varchar":		//strings for MYSQL
				if(gettype($value)=="string"){
					return true;
				} else {
					return false;
				}
				break;
			case "double":		//double types
				return is_numeric($value);
				break;
			case "datetime":
				return $this->isValidDateTime($value);
				break;
			case "date";
				return $this->isValidDateTime($value." 00:00:00"); //hack to allow date fields to be checked as datetimes:)
				break;	
		}
		
	}
		
	function importerErrors($type, $row, $attribute){
		//store every row to display affected rows
		$this->rowsAffected[]=$row;
		//construct string
		$str="<span class=rowIdentifier>$row</span>: $attribute";
		switch($type){
			case "null":
				$this->error_nulls[]=$str;
				$this->rowsWithErrors[]=$row;
				break;
			case "datatype":
				$this->error_data[]=$str;
				$this->rowsWithErrors[]=$row;
				break;
			case "fk":
				$this->warning_fk[]=$str;
				break;
			case "regexp":
				$this->warning_regexp[]=$str;
				break;
			case "length":
				$this->warning_length[]=$str;
				break;
		}
	}
	
	function isValidDateTime($dateTime){
	    if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dateTime, $matches)) {
	        if (checkdate($matches[2], $matches[3], $matches[1])) {
	            return true;
	        }
	    }
	
	    return false;
	}
	
	function fkExists($value, $referenced_table){
		//set search path to main database
		$this->conn->dbConn();
		//get the second attribute from the referenced table
		$query="SELECT 1 FROM $referenced_table WHERE ".$this->columnLink[$referenced_table]."='$value'";
		//echo $query."<br>";
		$sql=$this->conn->query($query);
		if(!$sql->rowCount()){
			return false;
		} else {
			return true;
		}
	}
	
	function getForeignKeyValue($value,$referenced_table){
		//set search path to main database
		$this->conn->dbConn();
		//get the second attribute from the referenced table
//		print_r($this->columnLink);
		$query="SELECT * FROM $referenced_table WHERE ".$this->columnLink[$referenced_table]."='$value'";
		$sql=$this->conn->query($query);
		$row=$sql->fetch();
		if($sql->rowCount()){ //if this foreign key exists
			return $row[0];
		} else { //it there's no foreign key
			//search path to information schema
			$this->conn->dbInfo();
			$query="SELECT column_name FROM columns WHERE table_schema='".$this->conn->getDatabase()."' AND table_name='$referenced_table'";
			$sql=$this->conn->query($query);
			if($sql->rowCount()>1)
				$noColumns=$sql->rowCount()-2;	//remove the first two attributes
			else 
				$noColumns=$sql->rowCount();
			//change back to target db
			$this->conn->dbConn();
			//initialize query to insert a new value
			$query="INSERT INTO $referenced_table VALUES ('','$value',";
			//loop through the remaining attributes
			for($i=0;$i<$noColumns;$i++){
				$query.="'NULL',";	//set this as NULL
			}
			$query=substr($query,0,strlen($query)-1);	//remove the last comma (,)
			$query.=")";	//finish query
//			echo $query;
			$this->conn->query($query);
			return $this->conn->lastInsertId();
		}
	}
	
	function checkUnique($value){
		//set search path to main database
		$this->conn->dbConn();
		$query="SELECT 1 FROM ".$this->objName." WHERE ".$this->unique." LIKE '$value'";
		$sql=$this->conn->query($query);
		//check if there is any related data
		if($sql->rowCount()){
			$action="UPDATE";
		} else {
			$action="INSERT";
		}
		return $action;
	}
	
	
	function delete($option){
		$query=false;
		switch($option){
			case "0":
				//don't do anything
				break;
			case "1":
				//delete all entries from this table
				$query="DELETE FROM ".$this->getObjName();
				break;
			case "2":
				//delete entries only related with matching key (if it exists)
				$query="DELETE FROM ".$this->objName." WHERE ".$this->matchingKey."='".$this->matchingKeyValue."'";
				break;
		}
		return $query;
	}

	function insert($arr){
		//initialize query to insert data 
		$query="INSERT INTO ".$this->objName." (";
		foreach ($this->fileHeaders as $title){
			$query.="$title,";
		}
		$query=substr($query,0,strlen($query)-1);
		$query.=") VALUES (";
		//loop through all values of the array
		foreach ($arr as $value){
			$query.="'$value',";
		}
		//remove the last comma from th string
		$query=substr($query,0,strlen($query)-1);
		//finish the query
		$query.=")";
		//echo $query;
		return $query;
	}
	
	function update($arr, $uniqueValue){
		//initialize query to insert data
		$query="UPDATE ".$this->objName." SET ";
		$i=0;
		foreach ($arr as $value){
			$query.=$this->fileHeaders[$i]."='$value',";
			$i++;
		}
		//remove the last comma from th string
		$query=substr($query,0,strlen($query)-1);
		//finish the query
		$query.=" WHERE ".$this->unique."='$uniqueValue'";
		return $query;
	}
	

}
	



?>