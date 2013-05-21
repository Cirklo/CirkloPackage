<?php 

require_once "session.php";
$user_id=startSession();

require_once "__dbConnect.php";

if(isset($_POST['action'])){
	$genObj = new genObjClass();
	$action=$_POST['action'];
	switch($action){
		case "delete":
			$genObj->delete();
			break;
		case "update":
			$genObj->update();
			break;
		case "insert":
			$genObj->insert();
			break;
	}
}

class genObjClass{
	private $conn;
	private $regexp="/[^a-zA-Z0-9_ %\[/]\.\?\&\,\@\.\:\(\)%&-]/";
	
	
	public function __construct(){
		$this->conn=new dbConnection();
	}
	
	function delete(){
		//get posted ids
		if(isset($_POST['id']))			$id=$_POST['id'];	//rows to delete
		if(isset($_POST['objName']))	$objName=$_POST['objName'];	//target table

		try{
			//loop through all ids
			foreach($id as $row){
				$query="DELETE FROM $objName WHERE ".$objName."_id=$row";
				$this->conn->query($query);
			}
			//echo false;
		} catch (Exception $e){
			$error="Value is currently being used by another table";
			echo $error;
		}
	}
	
	function update(){
		require_once "dispClass.php";
	
		//get posted ids
		if(isset($_POST['arr']))		$arr=$_POST['arr'];	//rows to update
		if(isset($_POST['objName']))	$objName=$_POST['objName'];	//target table
	
		$display=new dispClass();
		
		//get tables fields
		//change search path to information schema
		$this->conn->dbInfo();
		$table=$this->tableHeaders($objName);
		
		//change search path to the main database
		$this->conn->dbConn();
		$this->conn->beginTransaction();
		try{
			//loop through all rows to update
			foreach ($arr as $row){
				//initialize counter
				$i=0;
				
				//initialize update query
				$query="UPDATE $objName SET ";
				//loop through all table attributes
				foreach ($table->header as $key){
					//set primary key
					$pkey=$row["update"][0];
					
					//nulls validation
					if($table->nullable[$key]=="NO" and trim($row["update"][$i]," ")==null and $i!=0)
						throw new Exception("$key cannot be null");	
					
					if(preg_match($this->regexp, $row["update"][$i]))
						throw new Exception("Invalid characters found at: ".$row["update"][$i]);	
						
					//password encryption
					if($table->comment[$key]=="pwd" and strlen($row["update"][$i])<64){
						$row["update"][$i]=$this->cryptPass($row["update"][$i]);
					}
					
					//is this a foreign key field?			
					if(!$table->fk[$key]){
						$query.=$key."='".$row["update"][$i]."',";
					} else {
						//get fields from fk table
						$display->tableHeaders($table->fk[$key]);
						$header = $display->getFullHeader();
						//build query to get primary key
						$fk_query="SELECT * FROM ".$table->fk[$key]." WHERE $header[1]='".$row["update"][$i]."'";
						$sql=$this->conn->query($fk_query);
						$res=$sql->fetch();
						// if($res[0]=="")	  throw new Exception("Unable to find foreign key value");
						// $query.=$key."='$res[0]',";
						if($res[0]=="")	  $query.=$key.'=NULL,';
						else $query.=$key."='$res[0]',";
					}
					$i++;
				}
				//remove the last comma (last character of the string)
				$query=substr($query,0,strlen($query)-1);
				//identify the query
				$query=$query." WHERE ".$objName."_id=$pkey";
				//finally update the database
				$this->conn->query($query);
			}
			$this->conn->commit();
			//echo false;
		} catch (Exception $e){
			$this->conn->rollBack();
			echo $e->getMessage();
		}
		
	}
	
	function insert(){
		require_once "dispClass.php";
		
		//get posted ids
		if(isset($_POST['arr']))		$arr=$_POST['arr'];	//rows to update
		if(isset($_POST['objName']))	$objName=$_POST['objName'];	//target table
	
		$display=new dispClass();
		
		//get tables fields
		//change search path to information schema
		$this->conn->dbInfo();
		$table=$this->tableHeaders($objName);
		
		//change search path to the main database
		$this->conn->dbConn();
		$this->conn->beginTransaction();
		try{
			//loop through all rows to update
			foreach ($arr as $row){
				//initialize counter
				$i=0;
				
				//initialize update query
				$query="INSERT INTO $objName VALUES (";
				//loop through all table attributes
				foreach ($table->header as $key){

					//nulls validation
					if($table->nullable[$key]=="NO" and trim($row["insert"][$i]," ")==null and $i!=0)
						throw new Exception("$key cannot be null");	
						
					//characters validation	
					if(preg_match($this->regexp, $row["insert"][$i]))
						throw new Exception("Invalid characters found at: ".$row["insert"][$i]);	
						
					//password encryption
					if($table->comment[$key]=="pwd"){
						$row["insert"][$i]=$this->cryptPass($row["insert"][$i]);
					}
					
					//is this a foreign key field?			
					if(!$table->fk[$key]){
						$query.= "'".$row["insert"][$i]."',";
					} else {
						//get fields from fk table
						$display->tableHeaders($table->fk[$key]);
						$header = $display->getFullHeader();
						//build query to get primary key
						$fk_query="SELECT * FROM ".$table->fk[$key]." WHERE $header[1]='".$row["insert"][$i]."'";
						$sql=$this->conn->query($fk_query);
						$res=$sql->fetch();
						// if($res[0]=="")	  throw new Exception("Unable to find foreign key value");
						// $query.= "'".$res[0]."',";
						if($res[0]=="")	  $query.= 'NULL,';
						else $query.= "'".$res[0]."',";
					}
					$i++;
				}
				//remove the last comma (last character of the string)
				$query=substr($query,0,strlen($query)-1);
				$query.=");";
				//finally insert new records into the database
				$this->conn->query($query);
			}
			$this->conn->commit();
			//echo false;
		} catch (Exception $e){
			$this->conn->rollBack();
			echo $e->getMessage();
		}
	
	
	}
	
	function tableHeaders($objName){
		$query="SELECT column_name, is_nullable, data_type, column_comment FROM columns WHERE table_schema='".$this->conn->getDatabase()."' AND table_name='$objName'";
		$sql=$this->conn->query($query);
		//loop through all table headers
		for($i=0;$row=$sql->fetch();$i++){
			$table->header[]=$row[0];
			$table->nullable[$row[0]]=$row[1];
			$table->datatype[$row[0]]=$row[2];
			$table->comment[$row[0]]=$row[3];
			//check for referenced tables
			$query2="SELECT referenced_table_name FROM key_column_usage WHERE table_schema='".$this->conn->getDatabase()."' AND table_name='$objName' AND column_name='$row[0]' AND referenced_table_name<>'NULL'";
			$sql2=$this->conn->query($query2);
			$row2=$sql2->fetch();
			$table->fk[$row[0]]=$row2[0];
		}
		return $table;
	}

	function cryptPass($value){
		return hash("sha256",$value);
	}
}








?>