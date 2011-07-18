<?php

/**
 * 
 * @author João Lagarto
 * @license EUPL
 * @copyright João Lagarto 2011
 * @abstract Requisition system 2.0 
 * 
 */

//require_once "../__dbConnect.php";
require_once "../resClass.php";

class reqClass{
	private $pdo;
	private $state=array();
	private $type=array();
	private $query=array();
	
	public function __construct(){
		$this->pdo = new dbConnection();
		$this->perm = new restrictClass();
	}
	
	//sets and gets
	public function setStates($arg){	$this->state=$arg;}
	public function setQuery($arg){	$this->query=$arg;}
	public function getQuery(){	return $this->query;}

	/**
	 * João Lagarto
	 * 
	 * Method to create a basket per group/user (need configuration). Only 1 basket per basket type can be created.
	 */
	
	public function getType(){ return $this->type;}
	
	public function createBasket($user_id, $department_id){
		//set search path to main database
		$this->pdo->dbConn();
		//How many basket types are there?
		$this->basketType($user_id);
		for($i=0;$i<sizeof($this->type);$i++){
			if($department_id=="") //Am I submitting a basket from my department?
				$sql = $this->pdo->prepare("SELECT 1 FROM basket WHERE basket_type IN (SELECT type_id FROM ".$this->pdo->getDatabase().".type WHERE type_name='".$this->type[$i]."') AND basket_state IN (SELECT state_id FROM ".$this->pdo->getDatabase().".state WHERE state_name='Active') AND basket_user IN (SELECT user_dep FROM ".$this->pdo->getDatabase().".user WHERE user_id=$user_id)");
			else //is this basket from another department?
				$sql = $this->pdo->prepare("SELECT 1 FROM basket WHERE basket_type IN (SELECT type_id FROM ".$this->pdo->getDatabase().".type WHERE type_name='".$this->type[$i]."') AND basket_state IN (SELECT state_id FROM ".$this->pdo->getDatabase().".state WHERE state_name='Active') AND basket_user=$department_id");
			$sql->execute();
			if($sql->rowCount()==0){
				if($department_id=="") //Am I submitting a basket from my department?
					$sql = $this->pdo->prepare("INSERT INTO basket (basket_user, basket_state, basket_type) SELECT user_dep, 0, (SELECT type_id FROM ".$this->pdo->getDatabase().".type WHERE type_name='".$this->type[$i]."') FROM ".$this->pdo->getDatabase().".user WHERE user_id=$user_id");
				else { //is this basket from another department?
					$sql = $this->pdo->prepare("INSERT INTO basket (basket_user, basket_state, basket_type) SELECT $department_id, 0, (SELECT type_id FROM ".$this->pdo->getDatabase().".type WHERE type_name='".$this->type[$i]."') FROM ".$this->pdo->getDatabase().".user WHERE user_id=$user_id");
				}
				try{		
					$sql->execute();
				}catch(Exception $e){
					echo $e->getMessage();
				}
			}
		}
		
	}
	
	/**
	 * Query the database for basket types
	 */
	
	public function basketType($user_id){
		//initialize variable to store restrictions
		$having="";
		//set search path to main database
		$this->pdo->dbConn();
		$having=$this->perm->restrictAttribute($user_id, "type");
		if($having!="")$having=" AND $having";
		$sql = $this->pdo->prepare("SELECT * FROM ".$this->pdo->getDatabase().".type WHERE type_id<>0 $having ORDER BY type_id"); //Excluding undefined
		$sql->execute();
		for($i=0;$row=$sql->fetch();$i++){
			$this->type[]=$row[1];
		}
	}
	
	/**
	 * Query the database for the current active basket
	 */
	
	
	public function actBasket($type, $department_id){
		//set search path to main database
		$this->pdo->dbConn();
		$sql=$this->pdo->prepare("SELECT basket_id FROM ".$this->pdo->getDatabase().".basket WHERE basket_state=0 AND basket_type IN (SELECT type_id FROM ".$this->pdo->getDatabase().".type WHERE type_name='$type') AND basket_user=$department_id");
		$sql->execute();
		$row=$sql->fetch();
		return $row[0];
		
	}
	
/**
 * Method to query the database for active basket states.
 * We are only looking for baskets that were already submitted
 */
	
	public function activeStates(){
		//set search path to main database
		$this->pdo->dbConn();
		//query for all active states
		$sql=$this->pdo->prepare("SELECT state_id, state_name FROM ".$this->pdo->getDatabase().".state WHERE state_bool=1 AND state_name<>'Active'");
		$sql->execute();
		for($i=0;$row=$sql->fetch();$i++){
			$states[$row[0]]=$row[1];
		}
		$this->setStates($states);
		return $this->state;
		
	}
	
	
}