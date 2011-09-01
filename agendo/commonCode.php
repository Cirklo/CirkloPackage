<?php
/*
  @author Pedro Pires or the Chosen Two
  @copyright 2010-2011 Pedro Pires
  @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
  @version 1.0
  @Code used in lots of places and all joined in an artistic way to avoid copy pasting same methods in different places
*/
	error_reporting (E_ERROR | E_WARNING | E_PARSE);
	session_start();
	// session_destroy();
	require_once("__dbHelp.php");
	
	if(isset($_GET['autocomplete'])){
		autocompleteAgendo();
		exit;
	}
	
	if(isset($_GET['usersList'])){
		getUsersList();
		exit;
	}

	function autocompleteAgendo(){
		$value = $_GET['term'];
		$sql = "select resource_id, resource_name from resource where lower(resource_name) like '%".strtolower($value)."%' and resource_status not in (0,2)";
		$res = dbHelp::query($sql);
		while($arr = dbHelp::fetchRowByIndex($res)){
			$row_array['id'] = $arr[0];
			$row_array['value'] = $arr[1];
			$json[] = $row_array;
		}
		echo json_encode($json);
	}

function getUsersList(){
	// $value = $_GET['term'];
	// $sql = "select user_id, resource_name from resource where lower(resource_name) like '%".strtolower($value)."%' and resource_status not in (0,2)";
	$value = explode(' ', $_GET['term']);
	if(sizeOf($value) > 1){
		$sql = "select user_id, user_firstname, user_lastname, user_login from user where lower(user_firstname) like '%".strtolower($value[0])."%' and lower(user_lastname) like '%".strtolower($value[1])."%' or lower(user_login) like '%".strtolower($value[0])."%'";
	}
	else{
		$sql = "select user_id, user_firstname, user_lastname, user_login from user where lower(user_firstname) like '%".strtolower($value[0])."%' or lower(user_lastname) like '%".strtolower($value[0])."%' or lower(user_login) like '%".strtolower($value[0])."%'";
	}
	$res = dbHelp::query($sql);
	while($arr = dbHelp::fetchRowByIndex($res)){
		$row_array['id'] = $arr[0];
		$row_array['value'] = $arr[1]." ".$arr[2]." (".$arr[3].")";
		$json[] = $row_array;
	}
	echo json_encode($json);
}

	function logIn(){
		$userLogin=$_POST['login'];
		$pass=$_POST['pass'];
		// $resource=$_GET['resource'];
		$passCrypted=$_POST['passCrypted'];

		try{
			if(
				!isset($userLogin) ||
				!isset($pass) ||
				!isset($passCrypted)
				// !isset($resource)
			){
				// $json->success = false;
				// $json->msg = "Did not get all the data needed to login.";
				throw new Exception("Did not get all the data needed to login.");
			}
			else{
				if($passCrypted == 'false'){
					$pass = cryptPassword($pass);
				}
				
				//****** check for the imap login ******
				$externalLogin = false;
				$sql = "select configParams_name, configParams_value from configParams where configParams_name = 'imapCheck' or configParams_name = 'imapHost' or configParams_name = 'imapMailServer'";
				$res = dbHelp::query($sql) or die ($sql);
				$configArray = array();
				while($arr=dbHelp::fetchRowByIndex($res)){
					$configArray[$arr[0]] = $arr[1];
				}
				$message = "Wrong Login!";
				if(sizeof($configArray) == 3 && $configArray['imapCheck'] == 1 && $configArray['imapHost'] != '' && $configArray['imapMailServer'] != ''){
					$email = $userLogin."@".$configArray['imapMailServer'];
					// {imap.gmail.com:993/imap/ssl}INBOX
					$inbox = @imap_open("{".$configArray['imapHost']."}INBOX", $email, $_POST['pass']);
					// if login to imap is successfull then $externalLogin = true;
					if(!$inbox){
						$message = imap_last_error();
					}
					else{
						imap_close($inbox);
						$externalLogin = true;
					}
				}
				//*****************************************
				
				$sql= "select user_firstname, user_lastname, user_passwd, user_id from ".dbHelp::getSchemaName().".user where user_login = '".$userLogin."' and user_passwd = '".$pass."'";
				$res=dbHelp::query($sql) or die ($sql);
				if (dbHelp::numberOfRows($res) == 0){
					// ********** Imap section *********
					if($externalLogin){
						// send user to the make new user(aplication.php) screen (with the login, pass and email already filled, from session?)
						$json->email = $email;
						$json->makeUser = true;
					}
					// *********************************
					else{
						// $json->success = false;
						// $json->msg = "Wrong Login!";
						throw new Exception($message);
					}
				}
				else{
					$arr=dbHelp::fetchRowByIndex($res);
					$_SESSION['user_name'] = $arr[0];
					$_SESSION['user_lastName'] = $arr[1];
					$_SESSION['user_pass'] = $arr[2];
					$_SESSION['user_id'] = $arr[3];
					$_SESSION['database'] = dbHelp::getSchemaName();

					// $json->success = true;
					$json->firstName = $arr[0];
					$json->lastName = $arr[1];
					// $json->resourceId = $resource;
				}
			}
			$json->success = true;
		}
		catch(Exception $e){
			$json->success = false;
			$json->msg = $e->getMessage();
		}
		echo json_encode($json);
	}

	function importJs(){
		echo "<script type='text/javascript' src='../agendo/js/jquery-1.5.2.min.js'></script>";
		echo "<script type='text/javascript' src='../agendo/js/commonCode.js'></script>";
		echo "<link type='text/css' href='../agendo/css/jquery.jnotify.css' rel='stylesheet' media='all' />";
		echo "<script type='text/javascript' src='../agendo/js/jquery.jnotify.js'></script>";
		echo "<link href='../agendo/css/tipTip.css' rel='stylesheet' type='text/css'>";
		echo "<script type='text/javascript' src='../agendo/js/jquery.tipTip.js'></script>";
		echo "<link rel='stylesheet' type='text/css' href='../agendo/css/autocomplete.css'>";
		echo "<script type='text/javascript' src='../agendo/js/jquery-ui-1.8.14.custom.min.js'></script>";
	}

	function showMsg($message, $isError = false, $import = false){
		if($import){
			importJs();
		}
		$isError = (string)$isError;// needs this because javascript is as old as Elvis(well, almost) and cant handle booleans from PHP
		echo "<script type='text/javascript'>";
		echo "showMessage('".$message."', '".$isError."');";
		// echo "alert('".$message."');";
		echo "</script>";
	}

	// if(isset($_GET['checkUserAndPass'])){
		// validUserAndPass($_GET['user'], $_GET['pass']);
		// exit;
	// }
		
	// Initializes the session, checks if it timesOut and if needsToBeLogged it doesnt allow the page where 
	// this function is to be entered without a user being logged in
	function initSession($needsToBeLogged=false){
		// error_reporting(0);
		// require_once("permClass.php");
		
        // $maxNoActivity = 10*60; // Seconds of session duration of no activity
		// $difference = (time() - $_SESSION['activeTime']);
		// if(isset($_SESSION['user_id']) && $difference > $maxNoActivity && isset($_SESSION['activeTime'])){
			// logOff();
		// }
		// else
			// $_SESSION['activeTime'] = time();
			
		if(isset($_SESSION['database']) && $_SESSION['database'] != dbHelp::getSchemaName()){
			logOff();
		}
		
		if($needsToBeLogged && (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == ''))
			echo "<meta HTTP-EQUIV='REFRESH' content='0; url=./'>";
	}

	function logOff(){
		session_start();
		session_unset();
		session_destroy();
		// echo "<meta HTTP-EQUIV='REFRESH' content='0; url=./'>";
	}
	
	function loggedInAs($phpFile, $resource){
		if(isset($_SESSION['user_name']) && $_SESSION['user_name']!=''){
			$textColor = 'white';
			$textColorHover = '#F7C439';
			$textSize = '10px';
			echo "<div id='loggedAsDiv' align='right' valign='bottom'>";
				echo "<label style='font-size:".$textSize.";color:".$textColor."'>Logged as ".$_SESSION['user_name']." ".$_SESSION['user_lastName']." | </label>";
				echo "<a style='cursor:pointer;font-size:".$textSize.";color:".$textColor."' onmouseover=\"this.style.color='".$textColorHover."'\" onmouseout=\"this.style.color='".$textColor."'\" title='Click here to logoff' onclick=logOff('".$phpFile.".php',".$resource.")> Logoff</a>";
				// echo "<a style='cursor:pointer;font-size:".$textSize.";color:".$textColor."' onmouseover=\"this.style.color='".$textColorHover."'\" onmouseout=\"this.style.color='".$textColor."'\" title='Click here to go to the admin area' onclick=\"window.location='../datumo/index.php'\"> AdminArea</a>";
			echo "</div>";
		}
	}
	
	function validUserAndPass($user, $pass, $passCrypted = false){
		if(!$passCrypted)
			$pass = cryptPassword($pass);

		$sql= "select user_id from ".dbHelp::getSchemaName().".user where user_login = '".$user."' and user_passwd = '".$pass."'";
		$res=dbHelp::query($sql) or die ($sql);
		if(dbHelp::numberOfRows($res) <= 0)
			echo "Wrong Login";
		else{
			$arr=dbHelp::fetchRowByIndex($res);
			$_SESSION['user_id'] = $arr[0];
			$_SESSION['user_pass'] = $pass;
			$_SESSION['database'] = dbHelp::getSchemaName();
			echo "";
		}
	}

	function cryptPassword($uncryptedPass){
		return hash('sha256', $uncryptedPass);
	}
	
	// Buttons for help, videos, resources and user/management
	function echoUserVideosResourceHelpLinks(){
		echo "<div id='linksImages'>";
			echo "<img style='cursor:pointer' width=30px id=help title='help' src=pics/ask.png onclick=\"javascript:window.open('http://www.cirklo.org/agendo_help.php','_blank','directories=no,status=no,menubar=yes,location=yes,resizable=yes,scrollbars=yes,width=1000,height=600')\" align='right' />";
			echo "<img style='cursor:pointer' width=30px id=video title='feature videos' src=pics/video.png onclick=go(this) align='right' />";
			echo "<img style='cursor:pointer' width=30px id=resources title='resource type' src=pics/resource.png onclick=go(this) align='right' />";
			echo "<img style='cursor:pointer' width=30px id=user title='user area' src=pics/user.png onclick=go(this) align='right' />";
			
			// echo "<label style='font-size:12px;color:#F7C439;' title='Type the name of the resource you wish to find'>search";
			// echo "&nbsp;";
			// echo "<input style='font-size:12px;width:120px;height:18px' type='text' id='resourceSearch' /></label>";
			// echo "&nbsp;";
		echo "</div>";
	}
	
	// Videos div
	function echoVideosDiv(){
		echo "<div id=videodiv align='center' style='cursor:pointer;padding:5px;display:none;position:absolute;width:150px;color:#444444;background-color:#FFFFFF;opacity:0.9;'>";
			$sql= "select media_name, media_link, media_description from media order by media_name";
			$res=dbHelp::query($sql) or die ($sql);
			$vidWidth = 640;
			$vidHeight = 480;
			for ($i=0;$i<dbHelp::numberOfRows($res);$i++) {
				$arr=dbHelp::fetchRowByIndex($res);
				echo "<a title='".$arr[2]."' onclick=\"javascript:window.open('".$arr[1]."','_blank','directories=no,status=no,menubar=yes,location=yes,resizable=yes,scrollbars=no,width=".$vidWidth.",height=".$vidHeight."')\">".$arr[0]."</a><br>";
			}
		echo "</div>";		
	}
	
	// Resources div
	function echoResourcesDiv(){
		echo "<div id=resourcesdiv align='center' style='padding:10px;display:none;position:absolute;left:540px;color:#444444;background-color:#FFFFFF;opacity:0.9'>\n";
			echo "<table>";
			// echo "<div style='width:162px;overflow:auto;'>";
				echo "<tr>";
					echo "<td>";
					echo "<div style='color:#789095;text-align:left;width:100px;'>";
						echo "<label style='color:#789095'>search</label>";
						echo "<br>";
						echo "<input type='text' id='resourceSearch' style='width:100px;font-size:11px;' title='Type the name of the resource you wish to find'/>";
					echo "</div>";
					echo "</td>";

					echo "<td>";
					echo "<div style='text-align:left;width:50px;margin-left:10px;margin-top:2px;'>";
						echo "<a href='index.php?class=0'>All</a>";
						echo "<br>";
						echo "<a href='index.php'>Most used</a>";
					echo "</div>";
					echo "</td>";
				echo "<tr>";
				
				// echo "&nbsp;";
				// echo "<div style='clear: both;'>";
				// echo "</div>";
			// echo "</div>";
			echo "</table>";
			$sql= "select * from resourcetype where resourcetype_id in (select distinct resource_type from resource) order by resourcetype_name";
			$res=dbHelp::query($sql) or die ($sql);
			$numRows = dbHelp::numberOfRows($res);
			// echo "<div style='position:relative;'>";
			if($numRows > 0){
				echo "<hr>";
				for ($i=0;$i<$numRows;$i++) {
					$arr=dbHelp::fetchRowByIndex($res);
					echo "<a href=index.php?class=" .$arr[0] . ">" . $arr[1] . "</a><br>";
				}
			}
			// echo "</div>";
			// echo "<input type='button' id='resourceSearchButton' />";
		echo "</div>";
	}
	
	// User/management div
	function echoUserDiv($phpFile, $resource){
		// Used only for the horrible patch/hack of the checkfields function in the weekview.js, more details on that file
		if($phpFile=='weekview')
			echo "<script type='text/javascript'> setUsingSession(false) </script>";
		// end
		
		$display = "table";
		echo "<div id=userdiv align='center' style='display:none;width:auto;position:absolute;color:#444444;background-color:#FFFFFF;opacity:0.9;padding:5px'>";
			echo "<form name=edituser id=edituser method=post>";
				if(isset($_SESSION['user_id'])){
					$display = "none";
					
					// Used only for the horrible patch/hack of the checkfields function in the weekview.js, more details on that file
					if($phpFile=='weekview'){
						echo "<script type='text/javascript'> setUsingSession(true) </script>";
					}
					// end
					
					echo "<table>";
						echo "<tr>";
							echo "<td style='text-align:center'>";
								echo "<input type=button style='font-size:11px' onclick=\"window.location='../datumo/index.php'\" value='AdminArea' />";
							echo "</td>";
						echo "</tr>";
					echo "</table>";
				}
				
				$sql = "select 1 from configParams where configParams_name = 'imapCheck' and configParams_value = 1";
				$res = dbHelp::query($sql);
				$arr = dbHelp::fetchRowByIndex($res);
				$action = "onblur='ajaxUser(this)'";
				if(dbHelp::numberOfRows($res) > 0){
					$action = "";
				}
				echo "<table style='display:".$display.";padding:6px;'>";
					echo "<tr>";
						echo "<td><label>User Name</label></td>";
						echo "<td><input style='font-size:11px;' name='user_idm' id='user_idm' value='' ".$action."/></td>";
					echo "</tr>";
					
					echo "<tr>";
						echo "<td><label>Password</label></td>";
						echo "<td><input type=password style='font-size:11px' id=user_passwd name=user_passwd value='' /></td>";
					echo "</tr>";
					
					echo "<tr>";
						echo "<td colspan=2 style='text-align:center'>";
						echo "<input type=button style='font-size:11px' onclick=submitUser('".$phpFile.".php',".$resource.",null,null,0) value='Login' />";
						echo "<input type=button style='font-size:11px' onclick=submitUser('../datumo/session.php',".$resource.",null,null,1) value='AdminArea' />";
						echo "</td>";
					echo "</tr>";

					echo "<tr>";
						echo "<td align=center colspan=2>";
						echo "<input type=button style='font-size:11px' onclick=ajaxRecoverPWD() value='Recover Password' />";
						echo "</td>";
					echo "</tr>";
				echo "</table>";

				echo "<table>";
					echo "<tr>";
						echo "<td align=center colspan=2>";
						echo "<hr />";
						echo "</td>";
					echo "</tr>";
				
					echo "<tr>";
							echo "<td colspan=2 style='text-align:center'>";
								echo "<input type=button style='font-size:11px' onclick=\"window.location='admin/cookie.php'\" value='Resource Settings' />";
							echo "</td>";
					echo "<tr>";

					echo "<tr>";
						echo "<td align=center>";
						echo "<input type=button style='display:".$display.";font-size:11px' value='New User' onclick=\"javascript:window.open('../agendo/application.php','_blank','directories=no,status=no,menubar=yes,location=yes,resizable=no,scrollbars=no,width=600,height=475')\" />";
						echo "</td>";

						echo "<td align=center>";
						echo "<input type=button style='font-size:11px' value='New Permission' onclick=\"javascript:window.open('../agendo/newperm.php','_blank','directories=no,status=no,menubar=yes,location=yes,resizable=no,scrollbars=no,width=400,height=275')\" />";
						echo "</td>";
					echo "<tr>";
				echo "</table>";
			echo "</form>";
		echo "</div>";
	}	
// End of buttons for help, videos, resources and user/management
	
	// wtf = "write to file", not "what the fu..dge" 
	function wtf($string, $mode = "w", $path = "c:/a.txt"){
		$fh = fopen($path, $mode) or die("Can't open file!");
		fwrite($fh, $string."\n");
		fclose($fh);
	}
	
	// Gets all the data after a certain string($afterString) and before a string ($beforeString)
	//	$array = getTablesFromScript($sql, 'CREATE TABLE IF NOT EXISTS', '('); returns an array of table names
	function getBetweenArray($all, $afterString, $beforeString, $separator = ';'){
		$results = '';
		while(($pos1 = stripos($all, $afterString)) !== false){
			$pos1 = $pos1 + strlen($afterString);
			$all = substr($all, $pos1, strlen($all)-$pos1);
			if(($pos2 = stripos($all, $beforeString)) === false)
				break;
			$results = trim(substr($all, 0, $pos2)).$separator.$results;
			$all = substr($all, $pos2, strlen($all));
		}
		$results = substr($results,0,strlen($results)-strlen($separator));
		return explode($separator, $results);
	}
	
	
	function getBetweenText($all, $begin, $end = ''){
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

	// returns true if the user is either logged or has the right ip.
	function secureIpSessionLogin(){
		$sqlFlag = "select configParams_value from configParams where configParams_name ='secureresources'";
		$resFlag=dbHelp::query($sqlFlag);
		$arrFlag=dbHelp::fetchRowByIndex($resFlag);
		if($arrFlag[0] == '1'){
			if(isset($_SESSION['user_name']) && $_SESSION['user_name'] != ''){
				return true;
			}
			
			$sqlFlag = "select allowedips_iprange from allowedips";
			$resFlag=dbHelp::query($sqlFlag);
			$ip = $_SERVER['REMOTE_ADDR'];
			$ip = substr($ip, 0, strripos($ip, '.'));
			while($arrFlag=dbHelp::fetchRowByIndex($resFlag)){
				if($arrFlag[0] == $ip){
					return true;
				}
			}
			return false;
		}
		return true;
	}
	
	function echoThisToJS(){
		return array(0 => "IRSYSTEMSEPARATOR", 1 => "IRSEPARATOR");
	}
	
	function sendMail($subject, $address, $message, $replyToPerson, $userDbSettings, $auth = null, $secure = null, $port = null, $host = null, $username = null, $password = null){
		require_once("../agendo/alert/class.phpmailer.php");
		$mail = new PHPMailer();
		
		$mail->IsSMTP();
		$mail->SMTPDebug  = 1;
		if($userDbSettings){
			$sql = "SELECT configParams_name, configParams_value from configParams where configParams_name='host' or configParams_name='port' or configParams_name='password' or configParams_name='email' or configParams_name='smtpsecure' or configParams_name='smtpauth'";
			$res = dbHelp::query($sql);
			$configArray = array();
			for($i=0;$arr=dbHelp::fetchRowByIndex($res);$i++){
				$configArray[$arr[0]] = $arr[1];
			}
			$mail->SMTPAuth   = $configArray['smtpauth'];
			$mail->SMTPSecure = $configArray['smtpsecure'];
			$mail->Port       = $configArray['port'];
			$mail->Host       = $configArray['host'];
			$mail->Username   = $configArray['email'];
			$mail->Password   = $configArray['password'];
		}
		else{
			$mail->SMTPAuth   = $auth;
			$mail->SMTPSecure = $secure;
			$mail->Port       = $port;
			$mail->Host       = $host;
			$mail->Username   = $username;
			$mail->Password   = $password;
		}	
		
		$mail->SetFrom($mail->Username, $replyToPerson);
		$mail->Subject = $subject;
		$mail->AddReplyTo($mail->Username, $replyToPerson);

		$mail->Body = $message;
		$mail->AddAddress($address, "");

		if($mail->Send() === false){
			throw new Exception("Unable to send the email, please check the mail settings.");
		}
	}
	
	// Returns all the number of lots used by a user in the week of the date given for a given resource, its resolution and the max ammount of hours a user can use the resource, and if
	function getSlotsResolutionMaxHours($day, $month, $year, $user_id, $resource){
		// convert datetime to time and get day of the week
		$timeDate = mktime(0, 0, 0, $month, $day, $year);
		$dayOfTheWeek = date('N', $timeDate);
		$firstDayWeekSubtractor = $dayOfTheWeek - 1;
		$lastDayWeekAdder = 7 - $dayOfTheWeek;
		
		// get the date of the first day of the week
		$firstDayWeek = mktime(0, 0, 0, $month  , (int)$day - $firstDayWeekSubtractor, $year);
		$firstDayDate = date('Ymd',$firstDayWeek);
		
		// get the date of the last day of the week
		$lastDayWeek = mktime(0, 0, 0, $month  , (int)$day + $lastDayWeekAdder, $year);
		$lastDayDate = date('Ymd',$lastDayWeek);
		
		// use the dateBetween method to get the number of slots
		$sql = "select sum(entry_slots), resource_resolution, resource_maxhoursweek, resource_resp from resource, entry where resource_id = ".$resource." and entry_user = ".$user_id." and entry_status in (1,2) and entry_resource = ".$resource." and ".dbHelp::dateBetween("entry_datetime", dbHelp::convertDateStringToTimeStamp($firstDayDate,'%Y%m%d'), dbHelp::convertDateStringToTimeStamp($lastDayDate,'%Y%m%d'));
		$res = dbHelp::query($sql);
		$arr = dbHelp::fetchRowByIndex($res);
		if(!isset($arr[0])){
			$arr[0] = 0;
		}

		return $arr;
	}
?>