<?php
	// Checks if session has started already
	if(session_id() == "") {
		session_start();
	}

	if(!isset($_SESSION['path']) || $_SESSION['path'] == ''){
		echo "No path defined in session";
		exit;
	}	// include_once($_SESSION['path']."/.htconnect.php");
	
	include_once($_SESSION['path']."/.htconnect.php");

	class dbHelp{ 
	
		private static $connect;
		private static $dateHash;

		// this isnt read
		// private function __construct(){
			// $connect = new dbConnection();
			// $schema = $connect->getSchemaName();
			// self::getConnect();
		// }
		
		public static function getConnect(){
			if(!isset(self::$connect)){
				self::$connect = new dbConnection();
				// Beware that w in MySQL is (0..6) and D in PostGre is (1..7)
				self::$dateHash = array("i" => "MI", "H" => "HH24", "h" => "HH12", "d" => "DD", "w" => "ID", "m" => "MM", "M" => "Month", "Y" => "YYYY");
				self::setTimezone();
			}
			return self::$connect;
		}
		
		private static function setTimezone(){
			// try{
				$sql = "SELECT table_schema FROM information_schema.tables WHERE table_schema = '".self::getSchemaName()."' and table_name = 'configParams'";
				$prep = self::query($sql);
				if(dbHelp::numberOfRows($prep) > 0){
					$sql = "select configParams_value from configParams where configParams_name = 'timezone'";
					$res = self::query($sql);
					$arr = self::fetchRowByIndex($res);
					if(isset($arr[0])){
						// if the timezone isnt valid it wont throw an exception just a notice that will probably be ignored
						date_default_timezone_set($arr[0]);
					}
				}
			// }
			// catch(Exception $e){
				// not handled
				// return false;
			// }
			// return true;
		}

		public static function now(){
			return "'".date("Y-m-d H:i:s")."'";
		}
		
		public static function convertToDate($dateOrTime, $isTime = false){
			if($isTime){
				return date("Y-m-d H:i:s", $dateOrTime);
			}
			return date("Y-m-d H:i:s", strtotime($dateOrTime));
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
		
		public static function query($sql, $argsArray = null){
			$connect = self::getConnect();
			try{
				$prepSql = $connect->prepare($sql);
				// wtf($sql."--".$argsArray[0]);
				if(isset($argsArray)){
					foreach($argsArray as $key => &$value){
						$prepSql->bindParam(':'.(string)$key, $value);
						// $prepSql->bindValue(':'.(string)$key, $value);
					}
				}
				$prepSql->execute();
			}
			catch(Exception $e){
				$argsText = "";
				if($argsArray != null){
					$argsText = "\nUsing the data array [".implode("; ", $argsArray)."]";
				}
				self::errorLog("Full sql query is: '".trim(preg_replace("/\s\s+/", " ", $sql))."'".$argsText."\nError is: '".$e->getMessage()."'.\nError happened on: ".date("d/m/Y H:i:s")."\n");
				throw new Exception("Database error, error logged.");
			}
			return $prepSql;
		}
		
		public static function numberOfRows($prepSql){
			return $prepSql->rowCount();
		}
		
		public static function fetchRowByName($prepSql){
			return $prepSql->fetch(PDO::FETCH_ASSOC);
		}
		
		public static function fetchRowByIndex($prepSql){
			return $prepSql->fetch(PDO::FETCH_NUM);
		}

		public static function selectDb($db){
			self::getConnect()->dbSelect($db);
		}
		
		public static function getDatabase(){
			return self::getConnect()->getDatabase();
		}
		
		public static function getSchemaName(){
			$connect = self::getConnect();
			return $connect->getSchemaName();
		}

		public static function getPath(){
			return getcwd();
		}
		
		public static function date_add($datefield, $interval, $timeType){
			$connect = self::getConnect();

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
			$connect = self::getConnect();

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
			$connect = self::getConnect();
			
			switch($connect->getEngine()){
				case "mysql": //query to change database in mysql
					$date = "date_format(".$datefield.", '".$timeFormat."')";
				break;
				case "pgsql"; //query to change database in postgresql
					$dateCommand = self::convertDateCommands($timeFormat);
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
			$connect = self::getConnect();
			$dateCommand = "";
			$timeFormat = str_replace("%", "", $timeFormat);

			for($i = 0; $i<strlen($timeFormat); $i++){
				$char = $timeFormat[$i];
				$tempChar = self::$dateHash[$char];
				if(isset($tempChar))
					$dateCommand = $dateCommand.$tempChar;
				else
					$dateCommand = $dateCommand.$char;
			}
			return $dateCommand;
		}
		
		public static function convertDateStringToTimeStamp($date, $dateCommand){
			$connect = self::getConnect();
			
			switch($connect->getEngine()){
				case "mysql": //query to change database in mysql
					$sql = "str_to_date('".$date."','".$dateCommand."')";
				break;
				case "pgsql"; //query to change database in postgresql
					$sql = "to_timestamp('".$date."','".self::convertDateCommands($dateCommand)."')";
				break;
			}
			return $sql;
		}
		
		// Returns the sql string for the corresponding engine of between dates
		public static function dateBetween($dateFieldName, $date1, $date2){
			// $connect = self::getConnect();
		
			// switch($connect->getEngine()){
				// case "mysql": //query to change database in mysql
					$sql = $dateFieldName." between ".$date1." and ".$date2;
				// break;
				// case "pgsql"; //probably needs to be changed
					// $sql = $dateFieldName." between '".$date1."'and '".$date2."'";
				// break;
			// }
			return $sql;
		}
		
		// Reads a script, expects a big line of sql statements seperated by ';'
		public static function scriptRead($sql){
			$connect = self::getConnect();
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
			// $resultMsg = 'success';
			// try{
				$connect = self::getConnect();
				// $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$connect->beginTransaction();
				$connect->exec($sql);
				$connect->commit();
			// }
			// catch(PDOException $e){
				// $resultMsg = $e->getMessage();
				// $connect->rollBack();
			// }
			// return $resultMsg;
		}
		
		// Creates a log for the db errors, deletes the file if its bigger then the $maxSize then appends the new error
		private static function errorLog($error){
			$maxSize = 5000; // bytes
			$path = $_SESSION['path']."/dbErrorLog.txt";
			if(file_exists($path) && filesize($path)+strlen($error) > $maxSize){
				unlink($path);
			}
			// $fh = fopen($path, "a") or die("Can't open/create db log file on path '".$path."'");
			$fh = fopen($path, "a");
			fwrite($fh, $error."\n");
			fclose($fh);
		}
	
		// Returns the in parentesis data (ex: '(:0, :1, :2, ....)') and an int with the size of the array or false in case the array is either null or empty
		public static function inDataFromArray($array){
			if(!isset($array) || !current($array)){
				return false;
			}
			
			$results = array();
			$results['inData'] = "(:0";
			$results['size'] = 1;
			while(next($array)){
				$results['inData'] .= ", :".$results['size'];
				$results['size']++;
			}
			$results['inData'] .= ")";
			
			return $results;
		}
	}
	// Fix for the lack of static contructors
	dbHelp::getConnect();
?>