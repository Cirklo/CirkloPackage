<?php

/**
 * @author João Lagarto
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract Class to handle reports
 */

require_once "errorClass.php";

class treeClass{
	private $pdo;
	private $query;
	private $name;
	private $description;
	private $perm;
	private $upd;
	private $del;
	private $add;
	
	public function __construct(){
    	$this->pdo = new dbConnection();
    	$this->query = new queryClass();
    	$this->perm = new restrictClass();
    	$this->error = new errorClass();
    }
	
    public function setTreeviewName($arg){	$this->name=$arg;}
	public function setTreeviewDescription($arg){	$this->description=$arg;}
    public function setUpdate($arg){	$this->upd=$arg;}
    public function setDelete($arg){	$this->del=$arg;}
    public function setAdd($arg){	$this->add=$arg;}
    	
	public function getTreeviewName(){	return $this->name;}
    public function getTreeviewDescription(){	return $this->description;}  
	public function getUpdate(){	return $this->upd;}
    public function getDelete(){	return $this->del;}
    public function getAdd(){	return $this->add;}
    
/**
 * @author João Lagarto
 * @abstract Method to dynamically generate trees
 */
    
    public function genTreeView($tree){
    	//set search path to main database
    	$this->pdo->dbConn();
    	$arr = array();
    	//store all tables in array
    	$arr = $this->treeTables($tree);
    	if($arr[0]!=""){
    		$table=$arr[0];
    	} else {
    		$table=$arr[1];
    	}
    	//display first table options
    	$sql = $this->pdo->prepare("SELECT * FROM ".$this->pdo->getDatabase().".".$table." ORDER BY 2");
    	$sql->execute();
    	echo "<ul id='browser' class='filetree treeview-famfamfam'>";
    	for($i=0;$row=$sql->fetch();$i++) {
    		if($arr[0]!=""){
    			$conn = $this->tableConn($arr[0], $arr[1]);
    			$onclick="dispTree('firstTree$i','$arr[1]','$arr[2]','$conn','$row[0]',1,1,false,$tree)";
       		} else {
       			$conn = $this->tableConn($arr[1], $arr[2]);
 		   		$onclick="dispTree('firstTree$i','$arr[2]','$arr[2]','$conn','$row[0]',1,2,false,$tree)";
      		}
    		echo "<li class=expandable><a href=javascript:void(0) onclick=$onclick>$row[1]</a>";
    		echo "<div id=firstTree$i style='display:none'></div>";
  			echo "</li>";
    	}
    	echo "</ul>";
    }
    
/**
 * @author João Lagarto
 * @abstract Method to get all tables from a tree group
 */
    
    public function treeTables($treeview_id){
    	//get all tables that make part of this tree
    	$sql = $this->pdo->prepare("SELECT treeview_table1,treeview_table2,treeview_table3 FROM ".$this->pdo->getDatabase().".treeview WHERE treeview_id=$treeview_id");
    	$sql->execute();
    	$arr = $sql->fetch();
    	return $arr;
    }
	
    
    public function tableConn($table1, $table2){
    	//set search path to information_schema
    	$this->pdo->dbInfo();
    	//construct array for input parameters.
		$arr = array($this->pdo->getDatabase(),$table1,$table2,'');
		for($i = 0;$i<sizeof($arr);$i++){
			$this->query->__set($i, $arr[$i]);	
		}
		//select engine (mysql or pgsql)
		$this->query->engineHandler($this->pdo->getEngine());
		//query number 1 -> necessary in order to select specific query from vault
		$sql = $this->pdo->prepare($this->query->getSQL(8)); 
		try{
			$sql->execute();	
			$row = $sql->fetch();
			//return search path to main database
			$this->pdo->dbConn();
			return $row[0];	
		} catch(Exception $e){
			$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
		}
		
    }
    
/**
 * @author João Lagarto
 * @abstract Method to handle treeview access
 */
    
    public function treeview_access($user_id){
    	//set search path to main database
    	$this->pdo->dbConn();
    	$sql = $this->pdo->prepare("SELECT treeview_id, treeview_name, treeview_description FROM ".$this->pdo->getDatabase().".treeview WHERE treeview_id IN (SELECT restree_name FROM ".$this->pdo->getDatabase().".restree WHERE restree_user=$user_id)");
    	try{
	    	$sql->execute();
	    	echo "<table>";
	    	if($sql->rowCount()==0){
	    		echo "<tr><td>No treeview reports available!</td></tr>";
	    	}else{
	    		for($i=0;$row=$sql->fetch();$i++){
	    			echo "<tr><td>".($i+1).".</td><td><a href='".$this->pdo->getFolder()."/treeview.php?tree=$row[0]' title='$row[2]'>$row[1]</a> - $row[2]</td></tr>";
	    		}	
	    	}
	    	echo "</table>";
    	} catch (Exception $e){
    		$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
    	}
    	
    	
    }
    
/**
 * @author João Lagarto
 * @abstract Method get treeview information (name and description)
 */
    
    public function treeDesc($treeview_id){
    	//set search path to main database
    	$this->pdo->dbConn();
    	$sql = $this->pdo->prepare("SELECT treeview_id, treeview_name, treeview_description FROM ".$this->pdo->getDatabase().".treeview WHERE treeview_id=$treeview_id");
    	try{
    		$sql->execute();
	    	$row = $sql->fetch();
	    	$this->setTreeviewName($row[1]);
	    	$this->setTreeviewDescription($row[2]);
    	} catch (Exception $e){
    		$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
    	}
    }
    
/**
 * @author João Lagarto
 * @abstract Method to handle treeview restrictions
 */
    
    public function treeRestrictions($treeview_id, $user_id){
    	//set search path to main database
    	$this->pdo->dbConn();
    	$sql = $this->pdo->prepare("SELECT restree_access FROM ".$this->pdo->getDatabase().".restree WHERE restree_name=$treeview_id AND restree_user=$user_id");
    	try{
    		$sql->execute();
			$row=$sql->fetch();
	    	//get this user permissions
	    	$this->perm->genRestrictions($row[0]);
	    	$this->setAdd($this->perm->getInsert());
	    	$this->setUpdate($this->perm->getUpdate());
	    	$this->setDelete($this->perm->getDelete());
       	} catch (Exception $e){
    		$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
    	}
    	
    	    	
    }
    
    public function update($treeview_id){
    	//retrieve variables FROM GET
    	if(isset($_GET['val']))	$fkv=$_GET['val']; //foreign key from which values will be updated
    	if(isset($_GET['arr']))	$arr=$_GET['arr']; //list of values to be updated
    	//tables that are used to create this treeview
    	$table=$this->treeTables($treeview_id);
    	//need to figure out which is that connects the last two tables (INDEX)
    	$index=$this->tableConn($table[1], $table[2]);
    	//initialize variable to build the query
    	$query="";
    	//loop through all posted variables
    	foreach($_POST as $key=>$value){
    		if($value=="") continue;
    		$query.=$key."='$value',";
    	}
    	$query=substr($query,0,strlen($query)-1);
    	//set search path to main database
    	$this->pdo->dbConn();
    	$sql=$this->pdo->prepare("UPDATE $table[2] SET $query WHERE $index=$fkv AND ".$table[2]."_id IN ($arr)");
    	//echo $sql->queryString;
    	try {
    		$sql->execute();
    	} catch (Exception $e){
    		$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
    	}
    }
    
    public function delete($treeview_id){
    	//retrieve variables FROM GET
    	if(isset($_GET['val']))	$fkv=$_GET['val']; //foreign key from which values will be updated
    	if(isset($_GET['arr']))	$arr=$_GET['arr']; //list of values to be updated
    	//tables that are used to create this treeview
    	$table=$this->treeTables($treeview_id);
    	//need to figure out which is that connects the last two tables (INDEX)
    	$index=$this->tableConn($table[1], $table[2]);
    	//initialize variable to build the query
    	$query="";    
    	//set search path to main database
    	$this->pdo->dbConn();
    	$sql=$this->pdo->prepare("DELETE FROM $table[2] WHERE $table[2]_id IN ($arr)");
    	echo $sql->queryString;
    //	$sql->execute();
    
    
    }
    
}



?>