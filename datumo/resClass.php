<?php

class restrictClass{
	private $pdo;
	private $query;
	private $restriction;
	private $update=false;
	private $delete=false;
	private $insert=false;
	private $request=false;
	private $login;
	private $email;
	private $level;
	
	public function __construct(){
		$this->pdo = new dbConnection();
		$this->query = new queryClass();	
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
    
    //sets
    public function setUserLogin($arg){	$this->login=$arg;}
    public function setUserEmail($arg){	$this->email=$arg;}
    public function setUserLevel($arg){	$this->level=$arg;}
	//gets
	public function getUpdate(){ return $this->update;}
	public function getDelete(){ return $this->delete;}
	public function getInsert(){ return $this->insert;}
    public function getRequest(){return $this->request;}
    public function getUserLogin(){ return $this->login;}
    public function getUserEmail(){ return $this->email;}
    public function getUserLevel(){	return $this->level;}
    
    public function userInfo($user_id){
    	//set search path to main database
    	$this->pdo->dbConn();
    	$sql = $this->pdo->prepare("SELECT user_login, user_email, user_level FROM ".$this->pdo->getDatabase().".user WHERE user_id=$user_id");
    	$sql->execute();
    	$row = $sql->fetch();
    	$this->setUserLogin($row[0]);
    	$this->setUserEmail($row[1]);
    	$this->setUserLevel($row[2]);
    	
    	
    }
	
	public function tableAccess($user_id){
		//initialize arrays
		$tables = array();
		$masks=array();
		//unrestricted table
		$sql = $this->pdo->prepare("SELECT DISTINCT admin_table, mask_name FROM admin, mask WHERE admin_table=mask_table AND admin_user=$user_id ORDER BY admin_table");
		$sql->execute();
		for($i=0; $row = $sql->fetch(); $i++){
        	$tables[]=$row[0];
        	$masks[]=$row[1];
     	}
		return array($tables,$masks);
		
	}	
	
	public function tablePermissions($objName, $user_id){
		//set search path to main database
		$this->pdo->dbConn();
		//get restrictions for this table and user
		$sql = $this->pdo->prepare("SELECT admin_permission FROM ".$this->pdo->getDatabase().".admin WHERE admin_user=$user_id AND admin_table='$objName'");
		$sql->execute();
		$row=$sql->fetch();
		$this->genRestrictions($row[0]);
	}
	
	public function genRestrictions($data){
		switch ($data){
			case 1:
				$this->update=true;
				break;
			case 2:
				$this->delete=true;
				break;
			case 3:
				$this->update=true;
				$this->delete=true;
				break;
			case 4:
				$this->insert=true;
				break;
			case 5:
				$this->insert=true;
				$this->update=true;
				break;
			case 6:
				$this->insert=true;
				$this->delete=true;
				break;
			case 7:
				$this->insert=true;
				$this->update=true;
				$this->delete=true;
				break;
			case 8:
				$this->request=true;
				break;
			case 9:
				$this->request=true;
				$this->update=true;
				break;
			case 10:
				$this->request=true;
				$this->delete=true;
				break;
			case 11:
				$this->request=true;
				$this->update=true;
				$this->delete=true;
				break;
			case 12:
				$this->request=true;
				$this->insert=true;
				break;
			case 13:
				$this->request=true;
				$this->insert=true;
				$this->update=true;
				break;
			case 14:
				$this->request=true;
				$this->insert=true;
				$this->delete=true;
				break;
			case 15:
				$this->request=true;
				$this->insert=true;
				$this->update=true;
				$this->delete=true;
				break;
			default:
				$this->request=false;
				$this->insert=false;
				$this->update=false;
				$this->delete=false;
		}
	}
	
	public function restrictAttribute($user_id, $objName){
		//initialize variable
		$having = "";
		//set database path to main database
		$this->pdo->dbConn();
		//query the database for any entry in the tabke resaccess
		$sql = $this->pdo->prepare("SELECT resaccess_column, resaccess_value, resaccess_table FROM resaccess WHERE resaccess_user=$user_id");
		$sql->execute();
		if($sql->rowCount() != 0){
			for($i=0;$row=$sql->fetch();$i++){
				if($row[2] == $objName){
					$row[1] = str_replace(",","','",$row[1]);
					$having .= $row[0]." IN ('".$row[1]."') AND ";
				} else {
					//change search path to information schema
					$this->pdo->dbInfo();
					//construct array for input parameters.
					$arr = array($this->pdo->getDatabase(),$row[2],'',''); //table and database
					for($i = 0;$i<sizeof($arr);$i++){
						$this->query->__set($i, $arr[$i]);	
					}
					//select engine (mysql or pgsql)
					$this->query->engineHandler($this->pdo->getEngine());
					//query number 5 -> necessary in order to select specific query from vault
					$sql2 = $this->pdo->prepare($this->query->getSQL(5)); 
					$sql2->execute();
					for($j=0;$fkrow=$sql2->fetch();$j++){
						if($objName == $fkrow[0]){
							$row[1] = str_replace(",","','",$row[1]);
					   		$having .= $fkrow[1]." IN ('".$row[1]."') AND ";
						}
					}
				}
			}
			$having = substr($having, 0, strlen($having)-4);
		}
		return $having;
	}
	
}


?>