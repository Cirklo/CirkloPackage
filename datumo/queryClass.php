<?php

/**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract Class to handle queries from different engines. It supports MYSQL and POSTGRESQL (so far!!!!)
 */

class queryClass{
	private $sql=array();
	private $vars=array();  
    private $data=array();
    private $pdo;
	
	public function __construct(){
		$this->pdo=new dbConnection();
    }

    /**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract __set and __get magic methods to dynamically create queries
 */
    
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
    
    public function prepareQuery($arr, $no){
    	$this->pdo->dbInfo();
		
		for($i = 0;$i<sizeof($arr);$i++){
			$this->__set($i, $arr[$i]);	
		}
		//select engine (mysql or pgsql)
		$this->engineHandler($this->pdo->getEngine());
		$sql = $this->pdo->query($this->getSQL($no)); 
		$row=$sql->fetch();
		//return search path to main database
		$this->pdo->dbConn();
		return $row;
    }
	
	public function getSQL($i) {return $this->sql[$i];}
	
 /**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract method that redirects to two new branches depending on the engine
 */
	
	public function engineHandler($engine){		
		switch($engine){
			case "mysql": //select mysql queries
				$this->mysqlQuery();
				break;
			case "pgsql": //select postgresql queries
				$this->pgsqlQuery();
				break;
		}
	}
	
	/**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract structure that contains all specific queries for both mysql and pgsql. Hopefully in the future (datumo3.0) someone might add another DBMS
 * @param 4 input parameters
 */

	public function mysqlQuery(){
		$this->sql[0] = "";
		$this->sql[1] = "SELECT table_type, table_comment FROM tables WHERE table_name='".$this->vars[0]."' AND table_schema='".$this->vars[1]."'";
		$this->sql[2] = "SELECT column_name, data_type, column_comment, character_maximum_length, column_default FROM columns WHERE table_schema='".$this->vars[1]."' AND table_name='".$this->vars[0]."'";
		$this->sql[3] = "SELECT referenced_table_name FROM key_column_usage WHERE referenced_table_name<>'NULL' AND table_schema='".$this->vars[1]."' AND column_name='".$this->vars[0]."'";
		$this->sql[4] = "SELECT ".$this->vars[0]."_id, ".$this->vars[2]." FROM ".$this->vars[3].".".$this->vars[0]." WHERE LOWER(".$this->vars[2].") regexp LOWER('".$this->vars[1]."')";
		$this->sql[5] = "SELECT table_name, column_name FROM KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = '".$this->vars[0]."' AND REFERENCED_TABLE_NAME = '".$this->vars[1]."'";
		$this->sql[6] = "SELECT referenced_table_name FROM key_column_usage WHERE table_schema='".$this->vars[0]."' AND column_name='".$this->vars[1]."'";
		$this->sql[7] = "SELECT user_login, message_title, message_text, NOW() FROM ".$this->vars[0].".message, ".$this->vars[0].".user WHERE user_id=message_from AND (message_to=".$this->vars[1]." OR message_to=message_from) ORDER BY message_date DESC LIMIT ".$this->vars[2];
		$this->sql[8] = "SELECT column_name FROM key_column_usage WHERE table_schema='".$this->vars[0]."' AND referenced_table_name='".$this->vars[1]."' AND table_name='".$this->vars[2]."'";
	}
	
	public function pgsqlQuery(){
		$this->sql[0] = "";
		$this->sql[1] = "SELECT table_type, (select description from pg_description where objoid='".$this->vars[1].".".$this->vars[0]."'::regclass AND objsubid=0) FROM tables WHERE table_name='".$this->vars[0]."' AND table_schema='".$this->vars[1]."'";
		$this->sql[2] = "SELECT column_name, data_type, (SELECT col_description('".$this->vars[1].".".$this->vars[0]."'::regclass,ordinal_position)),character_maximum_length, column_default FROM columns WHERE table_schema='".$this->vars[1]."' AND table_name='".$this->vars[0]."'";
		$this->sql[3] = "SELECT a.table_name FROM constraint_column_usage as a, table_constraints as b WHERE a.constraint_name=b.constraint_name AND a.table_schema='".$this->vars[1]."' AND b.constraint_name IN (SELECT constraint_name FROM key_column_usage WHERE column_name='".$this->vars[0]."') AND b.constraint_type<>'UNIQUE'";
		$this->sql[4] = "SELECT ".$this->vars[0]."_id, ".$this->vars[2]." FROM ".$this->vars[3].".".$this->vars[0]." WHERE ".$this->vars[2]."~*'".$this->vars[1]."' LIMIT 25";
		$this->sql[5] = "SELECT a.table_name, a.column_name FROM key_column_usage as a, constraint_column_usage as b WHERE b.constraint_name=a.constraint_name AND a.constraint_schema='".$this->vars[0]."' AND b.table_name='".$this->vars[1]."'";
		$this->sql[6] = "SELECT b.table_name FROM key_column_usage as a, constraint_column_usage as b WHERE a.constraint_schema='".$this->vars[0]."' AND a.constraint_name=b.constraint_name AND a.column_name='".$this->vars[1]."'";
		$this->sql[7] = "SELECT user_login, message_title, message_text, date_trunc('second',message_date) FROM ".$this->vars[0].".message, ".$this->vars[0].".user WHERE user_id=message_from AND (message_to=".$this->vars[1]." OR message_to=message_from) ORDER BY message_date DESC LIMIT ".$this->vars[2];
		$this->sql[8] = "SELECT a.column_name FROM key_column_usage as a, constraint_column_usage as b WHERE b.constraint_name=a.constraint_name AND a.constraint_schema='".$this->vars[0]."' AND b.table_name='".$this->vars[1]."' AND a.table_name='".$this->vars[2]."'";
	}
	
	
}

?>