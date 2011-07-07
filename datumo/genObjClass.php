<?php

/**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract Class to table queries (insert, delete and update)
 */

class genObjClass{
	private $pdo;
	private $display;
	private $vars=array();
	private $error;
	
	public function __construct(){
		$this->pdo = new dbConnection();
		$this->display = new dispClass();
		$this->error = new errorClass();
	}
	
	public function __set($var, $val){
        $this->vars[$var] = $val;
    } 
    public function __get($var){
        if(isset($this->vars[$var])){
            return $this->vars[$var];
        } else {
            throw new Exception("Property ‘$var’ does not exist");
        }
    }
    
    /**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract method to delete single or multiple rows
 */
    
    public function delete($objName){
    	//set search path to main database
    	$this->pdo->dbConn();
    	$pkey = $objName."_id";
    	$sql=$this->pdo->prepare("DELETE FROM ".$this->pdo->getDatabase().".$objName WHERE $pkey=".$this->vars[$pkey]);
    	try{
    		$sql->execute();
    	} catch(Exception $e){
    		$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
    	}
    }    
    
   /**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract method to update single or multiple rows 
 * @example Password is updated if: column comment is "pwd"; column name finishes with "_passwd"
 */
    
    public function update($objName){
    	//set search path to main database
    	$this->pdo->dbConn();
    	//initialize variables
    	$set="";
    	$pkey = $objName."_id";
    	$this->display->tableHeaders($objName);
		$arr=array();
		$arr=$this->display->getComment();
		
		foreach($this->vars as $key=>$value){
			if($arr[$key] == "pwd" and substr($key,strlen($objName),strlen($key))=="_passwd" and strlen($value)<64){
    	    	$value = $this->cryptPass($value);
    	    }
			$set .= $key."='".nl2br($value)."',";
		}
		$set=substr($set,0,strlen($set)-1);
		$sql = $this->pdo->prepare("UPDATE ".$this->pdo->getDatabase().".$objName SET $set WHERE $pkey='".$this->vars[$pkey]."'");
		//echo $sql->queryString;   
    	try{
    		$sql->execute();
    	} catch(Exception $e){
    		$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
    	}
    }
    
   /**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract method to insert single or multiple rows
 */    
    
    public function insert($objName){
    	//set search path to main database
    	$this->pdo->dbConn();
    	//initialize variables
    	$att="";
    	$val="";
		$this->display->tableHeaders($objName);
		$arr=array();
		$arr=$this->display->getComment();
		
		foreach($this->vars as $key=>$value){
			if($value=="")continue; //proceed if it is a null value. No need to say it to the database
    	    if($arr[$key] == "pwd" and substr($key,strlen($objName),strlen($key))=="_passwd"){
    	    	$value = $this->cryptPass($value);
    	    }
			$att.=$key.",";
    		$val.="'".nl2br($value)."',";
    	}
    	$att=substr($att,0,strlen($att)-1);
    	$val=substr($val,0,strlen($val)-1);
 		$sql=$this->pdo->prepare("INSERT INTO ".$this->pdo->getDatabase().".$objName ($att) VALUES ($val)");
		//echo $sql->queryString;
		try{
    		$sql->execute();
    	} catch(Exception $e){
    		$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
       	}
    }
   
   /**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract method to Crypt password: using sha256
 */
    
    public function cryptPass($value){
    	return hash("sha256",$value);
    }

}


?>