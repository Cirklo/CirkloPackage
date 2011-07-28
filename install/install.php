<?php
	session_start();
	error_reporting (E_ERROR | E_WARNING | E_PARSE);

	if(isset($_POST['functionName'])){
		call_user_func($_POST['functionName']);
		exit;
	}

	// These methods seem quite useless for this test: echo "?????"; and as such they are commented as a reminder of the dark side of programming
	// header('Content-Type:text/html; charset=UTF-8');
	// echo " <html><head><meta http-equiv='Content-type' value='text/html; charset=utf-8'></head>";
	// echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Frameset//EN' 'http://www.w3.org/TR/html4/frameset.dtd'>";
	echo "<script type='text/javascript' src='../agendo/js/jquery-1.5.2.min.js'></script>";
	echo "<link type='text/css' href='../agendo/css/jquery.jnotify.css' rel='stylesheet' media='all' />";
	echo "<script type='text/javascript' src='../agendo/js/jquery.jnotify.js'></script>";
	echo "<script type='text/javascript' src='install.js'></script>";
	echo "<link href='install.css' rel='stylesheet' type='text/css'>";
	echo "<link href='../agendo/css/tipTip.css' rel='stylesheet' type='text/css'>";
	echo "<script type='text/javascript' src='../agendo/js/jquery.tipTip.js'></script>";

	echo "<center><img src=pics/cirklo.png></center>";
	
	echo "<table id='firstScreen' style='padding:10px'>";
	echo "<tr>";
	echo "<td>";
	
	echo "<table id='software'>";
		echo "<tr>";
			echo "<td align='center' valign='bottom'>";
			echo "<label>";
				echo "<img width=200 src='agendo.png' />";
				echo "<br><text style='color:white'>Check to install Agendo </text><input type='checkbox' name='package' id='agendo' checked/>";
			echo "</label>";
			echo "</td>";
		echo "</tr>";
	echo "</table>";

	echo "</td>";
	echo "<td>";

	// Eventually we will have postgresql and maybe others here
	$engineArray[] = 'MySQL';
	// htconnect data	
	echo "<table id='htConnectTable'>";
		echo "<tr>";
			echo "<td>";
			echo "<label id='dbEngineLabel'>Database engine</label>";
			echo "</td>";
			
			echo "<td>";
			echo "<select id='dbEngine' style='width:100%'>";
			foreach($engineArray as $engineItem){
				echo "<option value='".strtolower($engineItem)."'>".$engineItem."</option>";
			}
			echo "</select>";
			echo "</td>";
		echo "</tr>";
		
		echo "<tr>";
			labelInputText('dbName', 'Database name', 'databasename', 'Choose a proper database name (needs to be a non existing database)');
		echo "</tr>";
		
		echo "<tr>";
			labelInputText('dbHost', 'Database host', 'localhost', '');
		echo "</tr>";
		
		echo "<tr>";
			labelInputText('dbUser', 'Database username', 'root', 'Database user with permissions to create databases and tables');
		echo "</tr>";
		
		echo "<tr>";
			echo "<td>";
			echo "<label id='dbPassLabel'>Database password</label>";
			echo "</td>";
			
			echo "<td>";
			echo "<input id='dbPass' type='password' style='width:100%'></input>";
			echo "</td>";

			// echo "<td>";
			// echo "<img id='dbPassImg' class='helpClass' src='pics/interrogation.png' title='".$helpText."'></input>";
			// echo "</td>";
		echo "</tr>";
		
		echo "<tr>";
			labelInputText('path', 'Destination path', 'Cirklo', 'Relative path where the software will be installed within the root server');
		echo "</tr>";
		
		// Uncomment later when its possible to isolate all the tables to remove in case user goes back
		// echo "<tr>";
			// echo "<td align='center' colspan=2>";
				// echo "<input id='makeDB' type='checkbox' checked >Check to create database<P>";
			// echo "</td>";
		// echo "</tr>";
	echo "</table>";


	echo "</td>";
	echo "</tr>";

	echo "<tr>";
			echo "<td align='center' style='padding:10px' colspan=2>";
			echo "<input id='makeHtConnect' type='button' 	value='Check DataBase Connection' onclick=postMe(this.id)></input>";
			echo "</td>";
		echo "</tr>";
		
	echo "</table>";


	$height = 290;
	// data to put in the database
	echo "<table id='databaseData' style='display:none;'>";
		echo "<tr>";
			echo "<td valign='top'>";
				echo "<fieldset style='height:".$height."'>";
				echo "<legend><b>Email Settings</b></legend>";
					echo "<table id='mailSettings' >";
						echo "<tr>";
							echo "<td>";
							echo "<label id='instituteMailLabel'>Email </label>";
							echo "</td>";

							echo "<td>";
							echo "<input id='instituteMail' type='text' value='' onkeyup='checkMail(this.value,true)' style='width:100%'></input>";
							echo "</td>";

							echo "<td>";
							echo "<img id='instituteMailImg' class='helpClass' src='pics/interrogation.png' title='Webmail to send alerts and notifications (Gmail ex: yourGmailAccount@gmail.com)'></input>";
							echo "</td>";
						echo "</tr>";
				
						echo "<tr>";
							echo "<td>";
							echo "<label id='institutePassLabel'>Email password</label>";
							echo "</td>";
							
							echo "<td>";
							echo "<input id='institutePass' type='password' style='width:100%' value='' ></input>";
							echo "</td>";

							// echo "<td>";
							// echo "<img id='dbPassImg' class='helpClass' src='pics/interrogation.png' title='".$helpText."'></input>";
							// echo "</td>";
						echo "</tr>";
				
						echo "<tr>";
							labelInputText('instituteHost', 'Email Host ', '', "Email server host name (Gmail ex: ssl://smtp.gmail.com)");
						echo "</tr>";
				
						echo "<tr>";
							labelInputText('institutePort', 'Email Port ', '25', "Email smtp port (Gmail ex: 465)");
						echo "</tr>";
				
						echo "<tr>";
							labelInputText('instituteSecure', 'Smtp Secure ', 'none', "(Gmail ex: none)");
						echo "</tr>";
				
						echo "<tr>";
							labelInputText('instituteAuth', 'Smtp Auth ', '0', "(Gmail ex: 1)");
						echo "</tr>";

						echo "<tr><td><br></td></tr>";
						
						echo "<tr>";
							echo "<td align='right' colspan=2>";
							echo "<label>";
								// echo "<text>Send email </text><input type='checkbox' name='sendEmailChecked' id='sendEmailChecked' checked/>";
							echo "<text>An email will be sent to Cirklo for registration</text>";
							echo "</label>";
							echo "</td>";						
							
							echo "<td>";
							echo "<img id='mailSendImg' class='helpClass' src='pics/interrogation.png' title='If you do not want an email to be sent to Cirklo you will have to do a manual install. In any case, Cirklo does not keep any information other than the administrator name and email and the institute`s name.'></input>";
							echo "</td>";
						echo "</tr>";
					echo "</table>";
				echo "</fieldset>";
			echo "</td>";
		
		
			echo "<td valign='top'>";
				echo "<fieldset style='height:".$height."'>";
				echo "<legend><b>Institute</b></legend>";
					echo "<table id='institute'>";
						echo "<tr>";
							labelInputText('instituteName', 'Name ', '', "The full name of your institute");
						echo "</tr>";

						echo "<tr>";
							labelInputText('instituteShort', 'Short name ', '', "Institute abreviation");
						echo "</tr>";
				
						echo "<tr>";
							labelInputText('instituteUrl', 'Url ', '', "The url of your institute");
						echo "</tr>";
				
						echo "<tr>";
							echo "<td>";
							echo "<label>Country </label>";
							echo "</td>";
							
							echo "<td>";
							echo "<select id='countries' onclick='getCountry(this.value)'>";
							echo "</select>";
							echo "</td>";
						echo "</tr>";

						echo "<tr>";
							labelInputText('instituteAddress', 'Address ', '');
						echo "</tr>";
						
						echo "<tr>";
							labelInputText('institutePhone', 'Phone number ', '', "The phone number of your institute");
						echo "</tr>";

					echo "</table>";
				echo "</fieldset>";
			echo "</td>";
			
			echo "<td valign='top' style='height:100%'>";
				echo "<fieldset style='height:".$height."px'>";
				echo "<legend><b>Administrator</b></legend>";
					echo "<table id='institute' height=100% style='padding-bottom:10px'>";
						echo "<tr>";
							echo labelInputText('adminId', 'Login ', '', "Your username (ex: jlagarto)");
						echo "</tr>";

						echo "<tr>";
							echo "<td>";
							echo "<label id='adminPassPassLabel'>Password</label>";
							echo "</td>";
							
							echo "<td>";
							echo "<input id='adminPass' type='password' style='width:100%'></input>";
							echo "</td>";

							echo "<td>";
							echo "<img id='dbPassImg' class='helpClass' src='pics/interrogation.png' title='Your password (will be encrypted in SHA256)'></input>";
							echo "</td>";
						echo "</tr>";

						echo "<tr>";
							labelInputText('adminFirst', 'First name ', '', 'Your first name(ex: Joao)');
						echo "</tr>";

						echo "<tr>";
							labelInputText('adminLast', 'Last name ', '', 'Your last name(ex: Lagarto)');
						echo "</tr>";

						echo "<tr>";
							labelInputText('adminPhone', 'Phone number ', '', 'Your phone number');
						echo "</tr>";

						echo "<tr>";
							labelInputText('adminExt', 'Phone extension ', '', 'Your phone extension number(ex: 789)');
						echo "</tr>";

						echo "<tr>";
							labelInputText('adminMobile', 'Mobile phone ', '', 'Your mobile phone number, relevant if SMS warning is available');
						echo "</tr>";

						echo "<tr>";
							echo labelInputText('adminMail', 'Email ', '', 'Your personal email(ex:jlagarto@cirklo.org) ');
						echo "</tr>";

						echo "<tr>";
							labelInputText('department', 'Department ', '', 'Your department (ex: Equipment management unit)');
						echo "</tr>";
					echo "</table>";
				echo "</fieldset>";
			echo "</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td align='center' colspan=3>";
				echo "<input id='back'				type='button'	value='Undo Changes' 		onclick=back()></input>";
				echo "<input id='applySql'			type='button'	value='Finish'				onclick=postMe(this.id) disabled></input>";
			echo "</td>";
		echo "</tr>";
	echo "</table>";

	echo "<div>";
	echo "<fieldset style='background:#f7c439;color:black;text-align:center' >";
	echo "<label id='successError'>Welcome to Cirklo Software Installer.</label>";
	echo "</fieldset>";
	echo "</div>";
	
	
	// Gets current path and adds the previous path to create the htconnect file
	function makeHtConnect(){
		$successMsg = "Database connection successfully established.";
		$msg = $successMsg;
		$separateArray = getSeparator();
		$systemSeparator = $separateArray[0];
		$echoSeparator = $separateArray[1];
		
		try{
			if(!is_readable(getcwd()) || !is_writable(getcwd())) {
				throw new Exception("You don't have the proper read/write file permissions.\nIf using Linux, go to the command line, find the folder/directory where the 'Install', 'Agendo' and 'Datumo' folders are located and type 'sudo chmod -R 777 .' .");
			}
		
			$phpProperVersion = '5';
			if(!version_compare(phpversion(), $phpProperVersion, '>=')){
				throw new Exception("PHP version '".phpversion()."' detected, you need, at least, version '".$phpProperVersion."' to proceed.");
			}
			
			$dataArray = $_POST['data'];
			if(!isset($dataArray)){
				throw new Exception("Did not get any information fom client.");
			}
			
			$dbEngine =		$dataArray[0];
			$dbName = 		$dataArray[1];
			$dbHost = 		$dataArray[2];
			$dbUser = 		$dataArray[3];
			$dbPass =		$dataArray[4];
			$path = 		'../'.$dataArray[5];
			// $makeDB = 		(boolean)$dataArray[6];
			
			if(is_dir($path)){
				throw new Exception("You can not install this software in an existing folder.");
			}

			if(!mkdir($path)){
				throw new Exception("Wasn't able to create the '".$dataArray[5]."' folder.");
			}
			
			if(($fileData = file_get_contents('.htconnect.php')) == false){
				throw new Exception("Wasn't able to create/find the '.htconnect.php'");
			}
			
			$fileData = str_replace('engineMarker',$dbEngine,$fileData);
			wtlog('Engine marker replaced successfully');
			
			$informationSchemaName = "information_schema";
			$fileData = str_replace('databaseMarker',$dbName,$fileData);
			wtlog('Database marker replaced successfully','a');
			
			$fileData = str_replace('usernameMarker',$dbUser,$fileData);
			wtlog('Username marker replaced successfully','a');
			
			$fileData = str_replace('passwordMarker',$dbPass,$fileData);
			wtlog('Password marker replaced successfully','a');
			
			// change if postgre
			$fileData = str_replace('schemaMarker',$dbName,$fileData);
			wtlog('Schema marker replaced successfully','a');
	
			// create the .htconnect file and use it to connect
			$filename = '.htconnect.php';
			if(!file_put_contents($path."/".$filename, $fileData) || !copy(($filename = 'indexDatumo.php'), $path."/index.php") || !copy(($filename = 'nonconformities.php'), $path."/nonconformities.php")){
				throw new Exception("Couldn't create the ".$filename." file in '".$path."'.");
			}
			
			$arrVersion = noHtconnectFetchRows("select version()", $dbEngine, $informationSchemaName, $dbHost, $dbUser, $dbPass);
			$arrDB = noHtconnectFetchRows("CREATE DATABASE ".$dbName, $dbEngine, $informationSchemaName, $dbHost, $dbUser, $dbPass);
			$mysqlVersion = "5";
			if(version_compare($arrVersion[0], $mysqlVersion, '<')){ // Checks if the current mysql version is a minimum of $mysqlVersion
				throw new Exception("You need at least Mysql ".$mysqlVersion." to proceed.");
			}
			
			$_SESSION['path'] = $path;
			require_once("../agendo/commonCode.php");

			if(($sql = file_get_contents('DatumoBase.sql')) == false){
				throw new Exception("Wasn't able to open 'DatumoBase.sql'.");
			}
			
			$sql = str_replace('pathMarker',$dataArray[5],$sql);
			wtlog('Path marker replaced successfully','a');

			// throws exception if error
			tablesAlreadyExist($sql);

			// throws exception if error
			// imports part of the database
			dbHelp::scriptRead($sql);
			
			// gets the data in countries table and sends it to javascript via msg
			$countries = array();
			$sql = "select country_id, country_name from country";
			$res = dbHelp::mysql_query2($sql);
			while($arr = dbHelp::mysql_fetch_row2($res)){
				// $countries = $countries.$echoSeparator.$arr[0].$echoSeparator.$arr[1];
				$countries[$arr[0]] = $arr[1];
			}
			$json->countries = $countries;
		}
		catch(Exception $e){
			$msg = $e->getMessage();
			$extraMsg = back();
			if($extraMsg != ""){
				$msg = $msg."\n".$extraMsg;
			}
		}
		
		wtlog($msg,'a');
		$json->message = $msg;
		$json->success = ($msg == $successMsg);
		echo json_encode($json);
	}
	
	// apply the sql script
	function applySql(){
		$successMsg = "Successfully imported all database settings.";
		$msg = $successMsg;
		$separateArray = getSeparator();
		$systemSeparator = $separateArray[0];
		$echoSeparator = $separateArray[1];
			
		try{
			require_once("../agendo/commonCode.php");
			$dataArray = $_POST['data'];
			if(!isset($dataArray)){
				throw new Exception("Did not get any information from client.");
			}
			
			$adminId = 		$dataArray[0];
			$adminPass = 	$dataArray[1];
			$adminFirst = 	$dataArray[2];
			$adminLast = 	$dataArray[3];
			$adminPhone = 	$dataArray[4];
			$adminExt = 	$dataArray[5];
			$adminMobile = 	$dataArray[6];
			$adminMail = 	$dataArray[7];
			
			$institute = 		$dataArray[8];
			$instituteShort = 	$dataArray[9];
			$instituteUrl = 	$dataArray[10];
			$instituteMail = 	$dataArray[11];
			$institutePass = 	$dataArray[12];
			$instituteHost = 	$dataArray[13];
			$institutePort = 	$dataArray[14];
			$instituteSecure = 	$dataArray[15];
			$instituteAuth = 	$dataArray[16];
			$instituteAddress = $dataArray[17];
			$institutePhone = 	$dataArray[18];
			$instituteCountry = $dataArray[19];
			$department = 		$dataArray[20];
			$software =			$dataArray[21];
			// $sendEmail =		$dataArray[22];
			// $makeDB = 		(boolean)$dataArray[16];
			// $dbName = 		$dataArray[17];
			
			$address = "info@cirklo.org";
			$subject = "A new institute is installing Cirklo software.";
			$message = "Administrator: ".$adminFirst." ".$adminLast."\nEmail: ".$adminMail."\nInstitute: ".$institute."\nDate: ".date("F j, Y, g:i a");
			// if($sendEmail){
			if(!checkMailAux($instituteMail)){
				throw new Exception("Invalid email, please input a valid email address.");
			}
			// }
			
			sendMail($subject, $address, $message, ($adminFirst." ".$adminLast), false, $instituteAuth, $instituteSecure, $institutePort, $instituteHost, $instituteMail, $institutePass);
			
			$datumoConstraintsFile = 'DatumoConstraints.sql';
			$datumoTriggersFile = 'DatumoTriggers.sql';
			$currentFile = $datumoConstraintsFile;
			
			if(($sqlDatumoConstraints = file_get_contents($currentFile)) == false && ($sqlDatumoTriggers = file_get_contents(($currentFile = $datumoTriggersFile))) == false){
				throw new Exception("Failed to open ".$currentFile.".");
			}
			
			$sql = "INSERT INTO `user` (`user_id`, `user_login`, `user_passwd`, `user_firstname`, `user_lastname`, `user_dep`, `user_phone`, `user_phonext`, `user_mobile`, `user_email`, `user_alert`, `user_level`) VALUES
					(1, '".$adminId."', '".cryptPassword($adminPass)."', '".$adminFirst."', '".$adminLast."', 1, '".$adminPhone."', '".$adminExt."', '".$adminMobile."', '".$adminMail."', 1, 0)";
			dbHelp::mysql_query2($sql);	
			
			$sql = "INSERT INTO `institute` (`institute_id`, `institute_name`, `institute_address`, `institute_phone`, `institute_country`, `institute_vat`) VALUES
					(1, '".$institute."', '".$instituteAddress."', '".$institutePhone."', ".$instituteCountry.", 0)";
			dbHelp::mysql_query2($sql);	
			
			$sql = "INSERT INTO `department` (`department_id`, `department_name`, `department_inst`, `department_manager`) VALUES
					(1, '".$department."', 1, 1)";
			dbHelp::mysql_query2($sql);	
			
			$sql = "INSERT INTO `configParams` (`configParams_id`, `configParams_name`, `configParams_value`) VALUES
					(0, 'institute', '".$institute."'),
					(1, 'shortname', '".$instituteShort."'),
					(2, 'url', '".$instituteUrl."'),
					(3, 'secureresources', '0'),
					(4, 'host', '".$instituteHost."'),
					(5, 'port', '".$institutePort."'),
					(6, 'password', '".$institutePass."'),
					(7, 'email', '".$instituteMail."'),
					(8, 'smtpsecure', '".$instituteSecure."'),
					(9, 'smtpauth', '".$instituteAuth."'),
					(10,'publicity', '0')";
			dbHelp::mysql_query2($sql);	
					
			dbHelp::scriptRead($sqlDatumoConstraints);
			copyFolderTo('pics', $_SESSION['path']."/pics");
			copyFolderTo('admin', $_SESSION['path']."/admin");
			
//******* .htaccess ***********
			if(($fileData = file_get_contents($_SESSION['path']."/admin/.htaccess")) == false){
				throw new Exception("Wasn't able to open '.htaccess'.");
			}
			
			$absPath = getcwd();
			$absPath = str_replace('\\', '/', $absPath);
			$absPath = str_ireplace('install', substr($_SESSION['path'], 3)."/admin/", $absPath);
			$fileData = str_replace('pathMarker', $absPath, $fileData);
			wtlog('.htaccess path marker replaced successfully','a');

			if(!file_put_contents($_SESSION['path']."/admin/.htaccess", $fileData)){
				throw new Exception("Couldn't create the .htaccess file in '".$_SESSION['path']."/admin/");
			}
//*****************************

			// Eventually change this if there are other softwares to install
			if($software != "agendo"){
				loadTriggers($sqlDatumoTriggers, $echoSeparator);
			}
			// Eventually change this if there are other softwares to install
			else{
				if(
				!copy(($filename = 'indexAgendo.php'), $_SESSION['path']."/index.php") || 
				!copy(($filename = 'weekview.php'), $_SESSION['path']."/weekview.php")
				){
					throw new Exception("Couldn't create the '".$filename."' file in '".$_SESSION['path']."'.");
				}

				$agendoBaseFile = 'AgendoBase.sql';
				$agendoConstraintsFile = 'AgendoConstraints.sql';
				$agendoTriggersFile = 'AgendoTriggers.sql';
				$currentFile = $agendoBaseFile;
				if(
				!($sqlAgendo = file_get_contents($currentFile)) || 
				!($sqlAgendoContraints = file_get_contents(($currentFile = $agendoConstraintsFile))) ||
				!($sqlAgendoTriggers = file_get_contents(($currentFile = $agendoTriggersFile)))
				){
					throw new Exception("Failed to open '".$currentFile."'.");
				}
				
				tablesAlreadyExist($sqlAgendo);

				dbHelp::scriptRead($sqlAgendo);
				dbHelp::scriptRead($sqlAgendoContraints);
				loadTriggers($sqlAgendoTriggers, $echoSeparator);
			}
		}
		catch(Exception $e){
			$msg = $e->getMessage();
		}
		
		wtlog($msg,'a');
		$json->message = $msg;
		$json->success = ($msg == $successMsg);
		echo json_encode($json);
	}
	
	function back(){
		$message = "";
		
		try{
			require_once("../agendo/commonCode.php");
		}
		catch(Exception $e){
			$message = $e.getMessage()."\n";
		}

		try{
			$sql = "drop database ".dbHelp::getSchemaName();
			dbHelp::mysql_query2($sql);
		}
		catch(Exception $e){
			$message = $message.$e.getMessage()."\n";
		}

		try{
			rrmdir($_SESSION['path']);
		}
		catch(Exception $e){
			$message = $message.$e.getMessage()."\n";
		}

		session_destroy();
		return $message;
	}
	
	function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	} 
 
	function labelInputText($id, $label, $defaultText, $helpText = ''){
			echo "<td>";
			echo "<label id='".$id."Label'>".$label."</label>";
			echo "</td>";
			
			echo "<td>";
			echo "<input id='".$id."' type='text' value='".$defaultText."' style='width:100%'></input>";
			echo "</td>";

			if(!empty($helpText)){
				echo "<td>";
				echo "<img id='".$id."Img' class='helpClass' src='pics/interrogation.png' title='".$helpText."'></input>";
				echo "</td>";
			}
	}
	
	function wtlog($string, $mode = "w"){
		$fh = fopen('log.txt', $mode);
		fwrite($fh, $string."\n");
		fclose($fh);
	}
	
	function tablesAlreadyExist($sql){
		$msg = $success;
		$tables = getBetweenArray($sql, 'CREATE TABLE IF NOT EXISTS', '(');
		$sqlExisting = "select table_name from information_schema.tables where table_schema = '".dbHelp::getSchemaName()."'";
		$foundTable = "";
		$res = dbHelp::mysql_query2($sqlExisting);
		$arr = dbHelp::mysql_fetch_row2($res);
		if(!empty($arr)){
			for($i = 0; $i<sizeOf($tables); $i++){
					if(in_array(str_replace('`', '', $tables[$i]), $arr, true)){
						$foundTable = $tables[$i];
						throw new Exception("Table ".$tables[$i]." already exists.");
					}
			}
		}
	}
	
	function getInBetweenText($all, $begin, $end = ''){
		if(($positionBegin = stripos($all, $begin)) !== false){
			$positionBegin = $positionBegin + strlen($begin);
			if($end != ''){
				if(($positionEnd = stripos($all, $end, $positionBegin)) !== false){
					return trim(substr($all, $positionBegin, $positionEnd-$positionBegin));
				}
			}
			else
				return trim(substr($all, $positionBegin));
		}
		return false;
	}

	// drops and inserts triggers from the text of a script
	// doesnt need to have the drop trigger command in that script
	function loadTriggers($sql, $separator){
		$triggers = getBetweenArray($sql, "DELIMITER //", "//", $separator);
		for($i=0;$i<sizeOf($triggers);$i++){
			// inserts current trigger
			dbHelp::mysql_query2($triggers[$i]);
		}
	}
	
	function copyFolderTo($sourceFolder, $destinationFolder){
		if(!is_dir($sourceFolder))
			throw new Exception("Source directory doesn't exist.");
		else if(!is_dir($destinationFolder) && !mkdir($destinationFolder))
			throw new Exception("Could not copy the ".$sourceFolder." to ". $destinationFolder.".");
		else{
			$dir = dir($sourceFolder);
			//List files in images directory
			while (($file = $dir->read()) !== false)
			{
				if($file == '.' || $file == '..')
					continue;
				copy($sourceFolder."/".$file, $destinationFolder."/".$file );
			}
			$dir->close();
		}
	}
	
	function checkMail(){
		if(!isset($_POST['address'])){
			echo "Didn't get an email address.";
		}
		else{
			$address=$_POST['address'];
			if(checkMailAux($address)){
				echo "Valid email address.";
			}
			else{
				echo "Invalid email, please input a valid email address.";
			}
		}
		exit;
	}
	
	function checkMailAux($mailAddress){
		require_once("../agendo/alert/class.phpmailer.php");
		$mail = new PHPMailer();
		if($mail->ValidateAddress($mailAddress)){
			return true;
		}
		else{
			return false;
		}
	}
	
	function getSeparator(){
		return array(0 => "IRSYSTEMSEPARATOR", 1 => "IRSEPARATOR");
	}
	
	function noHtconnectFetchRows($sql, $engine, $db, $host, $user, $pass){
		$pdo = new PDO($engine.":".$db.";".$host, $user, $pass);
		$prepSql = $pdo->prepare($sql);
		if(!$prepSql->execute())
			throw new Exception("Error using the 'noHtconnectFetchRows' function when executing the sql query '".$sql."'.");
		return $prepSql->fetch(PDO::FETCH_NUM);
	}
	
?>