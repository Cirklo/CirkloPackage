<?php

/**
 * @author João Lagarto / Nuno Moreno
 * @version Datumo 2.0
 * @copyright EUPL
 * @abstract Class to handle DB connections
 */

class dbConnection extends PDO{
	
	private $engine;
	private $host;
	private $database;
	private $description;
	private $username;
	private $password;
	private $dsn;
	private $schema;
	private $schemaQuery;
	private $admin;
	
	public function __construct(){
		$this->databaseSettings();
		$this->dsn = $this->engine.":dbname=".$this->database.";host=".$this->host;

		// try {
			//database connection
			parent::__construct($this->dsn, $this->username, $this->password);

			//PDO error handling
			parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			// Sets to the right schema
			$this->dbConn();
		// } catch (PDOException $e) {
				// echo $e->getMessage();
				// exit();
		// }
	}	
	
	public function databaseSettings(){
		$this->engine = 'mysql'; //"mysql" OR "pgsql"
		$this->database = 'databasename';//"dbtest" or "postgres"
		$this->username = 'root'; //"root" OR "postgres"
		$this->password = ''; // "" OR "nasaki"
		$this->host = "localhost";
		$this->schema = 'databasename';// Name of the schema (same as database for mysql)
		$this->description = "IGC requisitions system";
		$this->admin = "info@cirklo.org";
		$this->folder = "/datumo"; //folder where the main files of datumo are located
	}
	

	public function getFolder(){		return $this->folder;} 
	public function getEngine(){ 		return $this->engine;}
	public function getDatabase(){		return $this->database;}
	public function getSchema(){ 		return $this->schemaQuery;}
	public function getDescription(){ 	return $this->description;}
	public function getAdmin(){ 		return $this->admin;}
	public function getSchemaName(){ 	return $this->schema;}
	
/**
 * @author João Lagarto / Nuno Moreno
 * @version Datumo 2.0
 * @copyright EUPL
 * @abstract method to return original database
 */
	
	public function dbConn(){
		$this->schemaSelect($this->schema);
		$sql = parent::prepare($this->schemaQuery);
		$sql->execute();
	}
	
/**
 * @author João Lagarto / Nuno Moreno
 * @version Datumo 2.0
 * @copyright EUPL
 * @abstract method to select information schema
 */
	
	public function dbInfo(){
		$this->schemaSelect("information_schema");
		$sql = parent::prepare($this->schemaQuery);
		$sql->execute();
	}
	
/**
 * @author João Lagarto / Nuno Moreno
 * @version Datumo 2.0
 * @copyright EUPL
 * @abstract method to handle different database engines
 */
	
	public function schemaSelect($db){ 
		switch($this->engine){
			case "mysql": //query to change database in mysql
				$this->schemaQuery = "use ".$db;
				break;
			case "pgsql"; //query to change database in postgresql
				$this->schemaQuery = "set search_path to ".$db.",public";
				break;
		}
	}
	
/**
 * @author Pedro Pires
 * @version Datumo 2.0
 * @copyright EUPL
 * @abstract method to change to different databases
 */
	public function dbSelect($db){ 
		$this->database = $db;
		$this->dbConn();
	}
		
}

?>