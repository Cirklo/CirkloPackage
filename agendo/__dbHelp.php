<?php
	session_start();
	if(!isset($_SESSION['path']) || $_SESSION['path'] == ''){
		echo "No path defined in session";
		exit;
	}	// include_once($_SESSION['path']."/.htconnect.php");
	
	include_once($_SESSION['path']."/.htconnect.php");

	class dbHelp{ 
	
		private static $connect;
		private static $schema; 
		private static $dateHash;
		private static $instance;

		private function __construct(){
			$connect = new dbConnection();
			$schema = $connect->getSchemaName();
		}
		
		public static function getConnect(){
			if(!isset(dbHelp::$connect)){
				dbHelp::$connect = new dbConnection();
				dbHelp::$schema = dbHelp::$connect->getSchemaName();
				// Beware that w in MySQL is (0..6) and D in PostGre is (1..7)
				dbHelp::$dateHash = array("i" => "MI", "H" => "HH24", "h" => "HH12", "d" => "DD", "w" => "ID", "m" => "MM", "M" => "Month", "Y" => "YYYY");
			}
			return dbHelp::$connect;
		}
		
		public static function changeToDatabase($db){
			$connect = self::getConnect();
			$connect->dbSelect($db);
		}
		
		public static function changeToSchema($schema){
			$connect = self::getConnect();
			$connect->schemaSelect($schema);
			$prepSql = $connect->prepare($connect->getSchema());
			$prepSql->execute();
		}
		
		public static function mysql_query2($sql){
			$connect = dbHelp::getConnect();
			$prepSql = $connect->prepare($sql);
			$prepSql->execute();
			return $prepSql;
		}
		
		public static function mysql_numrows2($prepSql){
			return $prepSql->rowCount();
		}
		
		public static function mysql_fetch_array2($prepSql){
			return $prepSql->fetch(PDO::FETCH_ASSOC);
		}
		
		public static function mysql_fetch_row2($prepSql){
			return $prepSql->fetch(PDO::FETCH_NUM);
		}

		public static function mysql_select_db2($db){
			dbHelp::getConnect()->dbSelect($db);
		}
		
		public static function getDatabase(){
			return dbHelp::getConnect()->getDatabase();
		}
		
		public static function getSchemaName(){
			$connect = dbHelp::getConnect();
			return $connect->getSchemaName();
		}

		public static function getPath(){
			return getcwd();
		}
		
		public static function date_add($datefield, $interval, $timeType){
			$connect = dbHelp::getConnect();

			switch($connect->getEngine()){
				case "mysql": //query to change database in mysql
					return "date_add(".$datefield.", interval ".$interval." ".$timeType.")";
					break;
				case "pgsql"; //query to change database in postgresql
					return $datefield." + INTERVAL '".$interval." ".$timeType."'";
					break;
			}
		}
		
		public static function date_sub($datefield, $interval, $timeType){
			$connect = dbHelp::getConnect();

			switch($connect->getEngine()){
				case "mysql": //query to change database in mysql
					return "date_sub(".$datefield.", interval ".$interval." ".$timeType.")";
					break;
				case "pgsql"; //query to change database in postgresql
					return $datefield." - INTERVAL '".$interval." ".$timeType."'";
					break;
			}
		}

		// Gets the same formatation parameters as MySql date_format (most of them at least) and converts them to PostGre
		// Always returns the date as a string and not as a timestamp (unless $asTimestamp is true)
		public static function getFromDate($datefield, $timeFormat, $asTimestamp=false){
			$connect = dbHelp::getConnect();
			
			switch($connect->getEngine()){
				case "mysql": //query to change database in mysql
					$date = "date_format(".$datefield.", '".$timeFormat."')";
				break;
				case "pgsql"; //query to change database in postgresql
					$dateCommand = dbHelp::convertDateCommands($timeFormat);
					$date = "to_char(".$datefield.", '".$dateCommand."')";
					
					if($asTimestamp){
						$date = "to_timestamp(".$date.",'".$dateCommand."')";
					}
					
				break;
			}
			return $date;
		}
		
		// Converts the MySql time formats to PostGre format
		public static function convertDateCommands($timeFormat){
			$connect = dbHelp::getConnect();
			$dateCommand = "";
			$timeFormat = str_replace("%", "", $timeFormat);

			for($i = 0; $i<strlen($timeFormat); $i++){
				$char = $timeFormat[$i];
				$tempChar = dbHelp::$dateHash[$char];
				if(isset($tempChar))
					$dateCommand = $dateCommand.$tempChar;
				else
					$dateCommand = $dateCommand.$char;
			}
			return $dateCommand;
		}
		
		public static function convertDateStringToTimeStamp($date, $dateCommand){
			$connect = dbHelp::getConnect();
			
			switch($connect->getEngine()){
				case "mysql": //query to change database in mysql
					$sql = "str_to_date('".$date."','".$dateCommand."')";
				break;
				case "pgsql"; //query to change database in postgresql
					$sql = "to_timestamp('".$date."','".dbHelp::convertDateCommands($dateCommand)."')";
				break;
			}
			return $sql;
		}
		
		// Reads a script, expects a big line of sql statements seperated by ';'
		public static function scriptRead($sql){
			$connect = dbHelp::getConnect();
			$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$statements = explode(';',$sql);
			$i = 0;
			while($i < sizeof($statements)){
				$statements[$i] = trim($statements[$i]);
				if(!empty($statements[$i]))
					$connect->exec($statements[$i]);
				$i++;
			}
		}
		
		public static function startTransaction($sql){
			$resultMsg = 'success';
			try{
				$connect = dbHelp::getConnect();
				// $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$connect->beginTransaction();
				$connect->exec($sql);
				$connect->commit();
			}
			catch(PDOException $e){
				$resultMsg = $e->getMessage();
				// $connect->rollBack();
			}
			return $resultMsg;
		}
	}
?>