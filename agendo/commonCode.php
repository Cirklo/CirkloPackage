<?php
/*
  @author Pedro Pires or the Chosen Two
  @copyright 2010-2011 Pedro Pires
  @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
  @version 1.0
  @Code used in lots of places and all joined to avoid copy pasting same methods in different places
*/
	// the type of error that will be "caught"
	error_reporting (E_ERROR | E_WARNING | E_PARSE);
	
	// Checks if session has started already
	if(session_id() == "") {
		session_start();
	}
	
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
		$json = new stdClass();
		$value = strtolower($_GET['term']);
		// $sql = "select resource_id, resource_name from resource where lower(resource_name) like '%".strtolower($value)."%' and resource_status not in (0,2)";
		// $res = dbHelp::query($sql);
		$sql = "select resource_id, resource_name from resource where lower(resource_name) like :0 and resource_status not in (0,2)";
		$res = dbHelp::query($sql, array("%".$value."%"));
		while($arr = dbHelp::fetchRowByIndex($res)){
			$row_array['id'] = $arr[0];
			$row_array['value'] = $arr[1];
			$json[] = $row_array;
		}
		echo json_encode($json);
	}

	function getUsersList(){
		// has to be an array for the jquery autocomplete plugin
		// $json = new stdClass();
		$json = array();
		$value = explode(' ', $_GET['term']);
		$operand = "or";
		
		$values = array();
		$values[0] = "%".strtolower($value[0])."%";
		$values[1] = "%".strtolower($value[0])."%";
		$values[2] = "%".strtolower($value[0])."%";
		if(sizeOf($value) > 1){
			$operand = "or";
			$values[2] = "%".strtolower($value[1])."%";
			// $sql = "select user_id, user_firstname, user_lastname, user_login from user where lower(user_firstname) like '%".strtolower($value[0])."%' and lower(user_lastname) like '%".strtolower($value[1])."%' or lower(user_login) like '%".strtolower($value[0])."%'";
		}
		
		// else{
			// $sql = "select user_id, user_firstname, user_lastname, user_login from user where lower(user_firstname) like '%".strtolower($value[0])."%' or lower(user_lastname) like '%".strtolower($value[0])."%' or lower(user_login) like '%".strtolower($value[0])."%'";
		// }
		// $res = dbHelp::query($sql);
		
		$sql = "select 
					user_id, 
					user_firstname, 
					user_lastname, 
					user_login 
				from 
					user 
				where 
					lower(user_login) like :0 ".$operand."
					lower(user_firstname) like :1 ".$operand." 
					lower(user_lastname) like :2 
		";

		$res = dbHelp::query($sql, $values);
		while($arr = dbHelp::fetchRowByIndex($res)){
			$row_array['id'] = $arr[0];
			$row_array['value'] = $arr[1]." ".$arr[2]." (".$arr[3].")";
			$json[] = $row_array;
		}
		echo json_encode($json);
	}

	function logIn(){
		//initialize JSON object
		$json = new stdClass();
		
		$userLogin=cleanValue($_POST['login']);
		$pass=cleanValue($_POST['pass']);
		// $resource=$_GET['resource'];
		$passCrypted=$_POST['passCrypted'];
		if($passCrypted == 'false'){
			$pass = cryptPassword($pass);
		}
		$externalLogin = false;
		$message = "Wrong Login!";

		try{
			// if(
				// !isset($userLogin) ||
				// !isset($pass) ||
				// !isset($passCrypted)
			// ){
				// throw new Exception("Did not get all the data needed to login.");
			// }
			// else{
				//****** check for the imap login ******
			$sql = "select configParams_name, configParams_value from configParams where configParams_name = 'imapCheck' or configParams_name = 'imapHost' or configParams_name = 'imapMailServer'";
			$res = dbHelp::query($sql) or die ($sql);
			$configArray = array();
			while($arr=dbHelp::fetchRowByIndex($res)){
				$configArray[$arr[0]] = $arr[1];
			}
			if(sizeof($configArray) == 3 && $configArray['imapCheck'] == 1 && $configArray['imapHost'] != '' && $configArray['imapMailServer'] != ''){
				$email = $userLogin."@".$configArray['imapMailServer'];
				// {imap.gmail.com:993/imap/ssl}INBOX
				// $inbox = @imap_open("{".$configArray['imapHost']."}", $email, $_POST['pass']);
				$inbox = @imap_open("{".$configArray['imapHost']."}", $userLogin, $pass);
				// if login to imap is successfull then $externalLogin = true;
				if(!$inbox){
					// $message = imap_last_error();
					$message = '';
					foreach(imap_errors() as $error){
						$message = $error."<br>".$message;
					}
					throw new Exception($message);
				}
				else{
					imap_close($inbox);
					$externalLogin = true;
				}
			}
		}
		catch(Exception $e){
			$json->success = false;
			$json->msg = $e->getMessage();
		}
		//*****************************************
		try{
			$sql= "select user_firstname, user_lastname, user_passwd, user_id, user_level from ".dbHelp::getSchemaName().".user where user_login = :0 and user_passwd = :1";
			$res=dbHelp::query($sql, array($userLogin, $pass)) or die ($sql);
			if (dbHelp::numberOfRows($res) == 0){
				// ********** Imap section *********
				if($externalLogin){
					// send user to the make new user(aplication.php) screen (with the login, pass and email already filled, from session?)
					$json->email = $email;
					$json->makeUser = true;
				}
				// *********************************
				else{
					throw new Exception($message);
				}
			}
			else{
				$arr=dbHelp::fetchRowByIndex($res);
				// if($arr[4] == '3'){ // Inactive account
					// throw new Exception("This account is inactive.");
				// }

				isBlacklisted($arr[3]);
			
				// $_SESSION['user_name'] = $arr[0];
				// $_SESSION['user_lastName'] = $arr[1];
				$_SESSION['user_pass'] = $arr[2];
				$_SESSION['user_id'] = $arr[3];
				$_SESSION['database'] = dbHelp::getSchemaName();

				// $json->success = true;
				$json->firstName = $arr[0];
				$json->lastName = $arr[1];
				// $json->resourceId = $resource;
			}
			$json->success = true;
		}
		catch(Exception $e){
			$json->success = false;
			$json->msg = $e->getMessage();
		}
		echo json_encode($json);
	}

	$jsWereImported = false;
	function importJs($path = "../agendo"){
		// echo "<script type='text/javascript' src='".$path."/js/jquery-1.5.2.min.js'></script>";
		echo "<script type='text/javascript' src='".$path."/js/jquery-1.8.2.min.js'></script>";
		echo "<script type='text/javascript' src='".$path."/js/commonCode.js'></script>";
		// echo "<link type='text/css' href='".$path."/css/jquery.jnotify.css' rel='stylesheet' media='all' />";
		// echo "<script type='text/javascript' src='".$path."/js/jquery.jnotify.js'></script>";
		echo "<link type='text/css' href='".$path."/css/jNotify.jquery.css' rel='stylesheet' media='all' />";
		echo "<script type='text/javascript' src='".$path."/js/jNotify.jquery.min.js'></script>";
		echo "<link href='".$path."/css/tipTip.css' rel='stylesheet' type='text/css'>";
		echo "<script type='text/javascript' src='".$path."/js/jquery.tipTip.js'></script>";
		echo "<link rel='stylesheet' type='text/css' href='".$path."/css/autocomplete.css'>";
		// echo "<script type='text/javascript' src='".$path."/js/jquery-ui-1.8.14.custom.min.js'></script>";
		echo "<script type='text/javascript' src='".$path."/js/jquery-ui-1.9.1.custom.js'></script>";
		echo "<script type='text/javascript' src='".$path."/js/browserData.js'></script>";
		global $jsWereImported;
		$jsWereImported = true;
	}

	function showMsg($message, $isError = false, $import = false){
		if($import && !isAjax()){
			importJs();
		}
		$isError = (string)$isError;// needs this because javascript is as old as Elvis(well, almost) and cant handle booleans from PHP
		echo "<script type='text/javascript'>";
		echo "showMessage('".$message."', '".$isError."');";
		// echo "alert('".$message."');";
		echo "</script>";
	}

	// Initializes the session, checks if it timesOut and if needsToBeLogged it doesnt allow the page where 
	// this function is to be entered without a user being logged in
	function initSession($needsToBeLogged=false){
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
	}
	
	function loggedInAs($phpFile, $resource){
		// if(isset($_SESSION['user_name']) && $_SESSION['user_name']!=''){
		if(isset($_SESSION['user_id']) && $_SESSION['user_id']!=''){
			$textColor = 'white';
			$textColorHover = '#F7C439';
			$textSize = '10px';
			$sql = "select user_firstname, user_lastname from ".dbHelp::getSchemaName().".user where user_id = :0";
			$prep = dbHelp::query($sql, array($_SESSION['user_id']));
			$row = dbHelp::fetchRowByIndex($prep);
			echo "<div id='loggedAsDiv' align='right' valign='bottom'>";
				// echo "<label style='font-size:".$textSize.";color:".$textColor."'>Logged as ".$_SESSION['user_name']." ".$_SESSION['user_lastName']." | </label>";
				echo "<label style='font-size:".$textSize.";color:".$textColor."'>Logged as ".$row[0]." ".$row[1]." | </label>";
				echo "<a style='cursor:pointer;font-size:".$textSize.";color:".$textColor."' onmouseover=\"this.style.color='".$textColorHover."'\" onmouseout=\"this.style.color='".$textColor."'\" title='Click here to logoff' onclick=logOff('".$phpFile.".php',".$resource.")> Logoff</a>";
			echo "</div>";
		}
	}
	
	function isValidUserAndPass($user, $pass, $passCrypted = false, $errorMessage = "Invalid username or password"){
		if(!$passCrypted)
			$pass = cryptPassword($pass);

		$sql = "select user_id from ".dbHelp::getSchemaName().".user where user_login = :0 and user_passwd = :1";
		$res = dbHelp::query($sql, array($user, $pass));
		if(dbHelp::numberOfRows($res) <= 0){
			throw new Exception($errorMessage);
		}
	}

	function cryptPassword($uncryptedPass){
		return hash('sha256', $uncryptedPass);
	}
	
	// Buttons for help, videos, resources and user/management
	function echoUserVideosResourceHelpLinks(){
		echo "<div id='linksImages'>";
			// echo "<img style='cursor:pointer' width=30px id=help title='help' src=pics/ask.png onclick=\"javascript:window.open('http://www.cirklo.org/agendo_help.php','_blank','directories=no,status=no,menubar=yes,location=yes,resizable=yes,scrollbars=yes,width=1000,height=600')\" align='right' />";
			echo "<img style='cursor:pointer' width=30px id=help title='help' src=pics/ask.png onclick=\"javascript:window.location = 'http://wiki.cirklo.org';\" align='right' />";
			// echo "<img style='cursor:pointer' width=30px id=video title='feature videos' src=pics/video.png onclick=go(this) align='right' />";
			echo "<img style='cursor:pointer' width=30px id=resources title='resource type' src=pics/resource.png onclick=go(this) align='right' />";
			$extraGet = '';
			// $action = "onclick=\"showMessage('Resource needs to be specified or user has to be logged on.');\"";
			// if(isset($_GET['resource'])){
				// $extraGet = '&simEquip';
				// if(isset($_GET['date'])){
					// $date = date('Ymd', strtotime('+1 day', strtotime($_GET['date'])));
					// $extraGet .= "&date=".$date;
				// }
				// $action = "onclick=\"javascript:window.open('../agendo/monitoring.php?resource=".$_GET['resource'].$extraGet."','_blank','toolbars=no,directories=no,status=no,menubar=no,location=no,resizable=yes,scrollbars=no,width=1024,height=300')\"";
			// }
			// else if(isset($_SESSION['user_id'])){
				// $action = "onclick=\"javascript:window.open('../agendo/monitoring.php?userLogged','_blank','toolbars=no,directories=no,status=no,menubar=no,location=no,resizable=yes,scrollbars=no,width=1024,height=300')\"";
			// }
			// echo "<img style='cursor:pointer' width=30px id=group title='group view' src=pics/group.png ".$action." align='right' />";
			echo "<img style='cursor:pointer' width=30px id=group title='group view' src=pics/group.png onclick=go(this) align='right' />";
			echo "<img style='cursor:pointer' width=30px id=user title='user area' src=pics/user.png onclick=go(this) align='right' />";
		echo "</div>";
	}
	
	// Videos div
	function echoVideosDiv(){
		// echo "<div id=videodiv align='center' style='cursor:pointer;padding:5px;display:none;position:absolute;width:150px;color:#444444;background-color:#FFFFFF;opacity:0.9;'>";
		echo "<div id=videodiv align='center' class='dropMenu'>";
			$sql= "select media_name, media_link, media_description from media order by media_name";
			$res=dbHelp::query($sql) or die ($sql);
			$vidWidth = 640;
			$vidHeight = 480;
			for ($i=0;$i<dbHelp::numberOfRows($res);$i++) {
				$arr=dbHelp::fetchRowByIndex($res);
				echo "<div style='padding:2px;'>";
					echo "<a title='".$arr[2]."' onclick=\"javascript:window.open('".$arr[1]."','_blank','directories=no,status=no,menubar=yes,location=yes,resizable=yes,scrollbars=no,width=".$vidWidth.",height=".$vidHeight."')\">".$arr[0]."</a><br>";
				echo "</div>";
			}
		echo "</div>";		
	}
	
	// Resources div
	function echoResourcesDiv(){
		// echo "<div id=resourcesdiv align='center' style='padding:10px;display:none;position:absolute;left:540px;color:#444444;background-color:#FFFFFF;opacity:0.9'>\n";
		echo "<div id=resourcesdiv align='center'  class='dropMenu'>\n";
			echo "<table>";
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
			echo "</table>";
			
			$sql= "select * from resourcetype where resourcetype_id in (select distinct resource_type from resource where resource_status in (1, 3, 4, 5)) order by resourcetype_name";
			$res=dbHelp::query($sql) or die ($sql);
			$numRows = dbHelp::numberOfRows($res);
			if($numRows > 0){
				echo "<hr>";
				for ($i=0;$i<$numRows;$i++) {
					$arr=dbHelp::fetchRowByIndex($res);
					echo "<div style='padding:2px;'>";
						echo "<a href=index.php?class=" .$arr[0] . ">" . $arr[1] . "</a><br>";
					echo "</div>";
				}
			}
		echo "</div>";
	}
	
	// User/management div
	function echoUserDiv($phpFile, $resource){
		// Used only for the horrible patch/hack of the checkfields function in the weekview.js, more details on that file
		if($phpFile=='weekview')
			echo "<script type='text/javascript'> setUsingSession(false) </script>";
		// end
		
		$display = "table";
		// echo "<div id=userdiv align='center' style='display:none;width:auto;position:absolute;color:#444444;background-color:#FFFFFF;opacity:0.9;padding:5px'>";
		echo "<div id=userdiv align='center' class='dropMenu'>";
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

						// $sql = "select resource_id, resource_name from resource where resource_status = 3 and resource_resp = :0"; // user confirmation
						// $prep = dbHelp::query($sql, array($_SESSION['user_id']));
						// if(isAdmin($_SESSION['user_id']) || dbHelp::numberOfRows($prep) > 0){
							// echo "<tr>";
								// echo "<td style='text-align:center'>";
									// echo "<input type=button style='font-size:11px' onclick=\"window.location='../agendo/makeConfirmRes.php'\" value='Resource Settings' />";
								// echo "</td>";
							// echo "</tr>";
						// }
						
						// echo "<tr>";
							// echo "<td colspan='2' style='text-align:center'>";
								// echo "<input type=button style='font-size:11px' onclick=\"window.location='../agendo/hoursUsage.php'\" value='Usage Report' />";
							// echo "</td>";
						// echo "<tr>";
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
						echo "<td><input type=password style='font-size:11px' id=user_passwd name=user_passwd value='' onkeypress='getEnter(event, \"".$phpFile."\", ".$resource.");'/></td>";
					echo "</tr>";
					
					echo "<tr>";
						echo "<td colspan=2 style='text-align:center'>";
							echo "<input type=button style='font-size:11px' onclick=submitUser('".$phpFile.".php',".$resource.",null,null) value='Login' />";
							echo "<input type=button style='font-size:11px' onclick=submitUser('../datumo/',null,null,null) value='AdminArea' />";
						echo "</td>";
					echo "</tr>";

					echo "<tr>";
						echo "<td align=center colspan=2>";
							echo "<input type=button style='font-size:11px' onclick=ajaxRecoverPWD() value='Recover Password' />";
						echo "</td>";
					echo "</tr>";

					// echo "<tr>";
						// echo "<td align=center colspan=2>";
							// echo "<input type=button style='font-size:11px' onclick=submitUser('../agendo/makeConfirmRes.php',null,null,null) value='Resource Settings' />";
						// echo "</td>";
					// echo "</tr>";
				echo "</table>";

				echo "<table>";
					echo "<tr>";
						echo "<td align=center colspan=2>";
							echo "<hr />";
						echo "</td>";
					echo "</tr>";
				
					// echo "<tr>";
							// echo "<td colspan=2 style='text-align:center'>";
								// echo "<input type=button style='font-size:11px' onclick=\"window.location='admin/cookie.php'\" value='Resource Settings' />";
								// echo "<input type=button style='font-size:11px' onclick=submitUser('../agendo/makeConfirmRes.php',null,null,null) value='Resource Settings' />";
							// echo "</td>";
					// echo "<tr>";

					echo "<tr>";
						echo "<td align=center>";
							echo "<input type=button style='display:".$display.";font-size:11px' value='New User' onclick=\"javascript:window.open('../agendo/application.php','_blank','directories=no,status=no,menubar=yes,location=yes,resizable=no,scrollbars=no,width=600,height=475')\" />";
						echo "</td>";

						echo "<td align=center>";
							echo "<input type=button style='font-size:11px' value='New Permission' onclick=\"javascript:window.open('../agendo/newperm.php','_blank','directories=no,status=no,menubar=yes,location=yes,resizable=no,scrollbars=no,width=400,height=275')\" />";
						echo "</td>";
					echo "<tr>";
					
					// if(isAdmin($_SESSION['user_id']) || isResp($_SESSION['user_id']) != false){ // Check if user is admin or resource responsible
						// echo "<tr>";
							// echo "<td colspan=2 style='text-align:center'>";
								// echo "<input type=button style='font-size:11px' onclick=\"window.location='../agendo/massPassRenewal.php'\" value='Password Generation' />";
							// echo "</td>";
						// echo "<tr>";

						// echo "<tr>";
							// echo "<td colspan=2 style='text-align:center'>";
								// echo "<input type=button style='font-size:11px' onclick=\"window.location='../agendo/givePermission.php'\" value='Resource Access' />";
							// echo "</td>";
						// echo "<tr>";
					// }
				echo "</table>";
			echo "</form>";
		echo "</div>";
	}	
// End of buttons for help, videos, resources and user/management
	
	// Checks if a user is an administrator
	function isAdmin($user_id){
		if(isset($user_id) && $user_id != ''){
			$sql = "select user_level from ".dbHelp::getSchemaName().".user where user_id = :0";
			$prepAdmin = dbHelp::query($sql, array($user_id));
			$row = dbHelp::fetchRowByIndex($prepAdmin);
			return $row[0] == 0;
		}
		
		return false;
	}
	
	// Checks if a user is a resource manager, returns the resource(s) or false
	function isResp($user_id, $resource = null){
		if(isset($user_id) && $user_id != ''){
			$resources = array();
			$sqlArray = array($user_id);
			$extraSql = "";
			
			if(isset($resource)){
				$extraSql = " and resource_id = :1";
				$sqlArray[] = $resource;
			}
			
			$sql = "select resource_id from resource where resource_resp = :0".$extraSql;
			$prepManager = dbHelp::query($sql, $sqlArray);
			while($row = dbHelp::fetchRowByIndex($prepManager)){
				$resources[] = $row[0];
			}
			
			if(sizeOf($resources) > 0){
				return $resources;
			}
		}
		return false;
	}
	
	// Checks if user $userId is allowed to use the resource $resourceId
	function hasPermission($userId, $resourceId){
		$sql = "select permissions_level from permissions where permissions_user = :0 and permissions_resource = :1";
		$prep = dbHelp::query($sql, array($userId, $resourceId));
		$permissionsLevel = dbHelp::fetchRowByIndex($prep);
		
		return $permissionsLevel != null && $permissionsLevel[0] != 0;
	}
	
	// Checks if a user is a department manager(PI), returns the resource or false
	function isPI($user_id){
		if(isset($user_id) && $user_id != ''){
			$departments = array();
			$sql = "select department_id from department where department_manager = :0";
			$prepManager = dbHelp::query($sql, array($user_id));
			while($row = dbHelp::fetchRowByIndex($prepManager)){
				$departments[] = $row[0];
			}
			
			if(sizeOf($departments) > 0){
				return $departments;
			}
		}
		return false;
	}
	
	function isBlacklisted($userId){
		$sql = "select blacklist_user from blacklist where blacklist_user = :0";
		$res = dbHelp::query($sql, array($userId));
		if(dbHelp::numberOfRows($res) > 0){
			throw new Exception("This account is inactive.");
		}		
	}
	
	// wtf = "write to file", not "what the fu..dge" 
	function wtf($string, $mode = "w", $path = "c:/a.txt"){
		// $fh = fopen($path, $mode) or die("Can't open file!");
		$fh = fopen($path, $mode);
		fwrite($fh, $string."\n");
		fclose($fh);
	}
	
	function wtf_array($array, $mode = "w", $path = "c:/a.txt"){
		wtf('----', $mode, $path);
		foreach($array as $line){
			wtf($line, 'a', $path);
		}
		wtf('----', 'a', $path);
	}
	
	function needProject(){
		$sql = "select configParams_value from configParams where configParams_name = 'useProjects'";
		$prep = dbHelp::query($sql);
		$row = dbHelp::fetchRowByIndex($prep);
		
		if($row[0] == 1){
			return true;
		}
		
		return false;
	}
	
	function same_department($user1, $user2){
		$sql = "select department_id from department join ".dbHelp::getSchemaName().".user on department_id = user_dep where user_id = :0 or user_id = :1";
		$prep = dbHelp::query($sql, array($user1, $user2));
		$row = dbHelp::fetchRowByIndex($prep);
		$depart1 = $row[0];
		$row = dbHelp::fetchRowByIndex($prep);
		
		return $row[0] == $depart1;
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
			if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != ''){
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
	
	function getMailObject($subject, $address, $message, $replyToPerson, $replyToPersonMail, $auth = null, $secure = null, $port = null, $host = null, $username = null, $password = null){
		require_once("../agendo/alert/class.phpmailer.php");
		$mail = new PHPMailer();
		
		$mail->IsSMTP();
		// $mail->isHtml($isHtml);
		$mail->SMTPDebug  = 1;
		if($auth == null){
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
		
		$mail->SetFrom($mail->Username, "[AGENDO]");
		$mail->Subject = $subject;
		$mail->ClearReplyTos();
		$mail->AddReplyTo($replyToPersonMail, $replyToPerson);

		$mail->Body = $message;
		$mail->AddAddress($address, "");

		// if($mail->Send() === false){
			// throw new Exception("Unable to send the email, please check the mail settings.");
		// }
		return $mail;
	}
	
	// Sends the mail object, its objective is to patch the crappy send method of the mailer class (seriously, send = false means error? ever heard of exceptions??)
	function sendMailObject($mailObject, $throwException = true){
		ob_start();
			$result = $mailObject->Send();
			$echoStr = ob_get_contents();
		ob_end_clean();

		if($result === false){
			$exceptionMessage = "Unable to send the email:";
			if($throwException){
				// throw new Exception($exceptionMessage." ".nl2br(strip_tags($echoStr)));
				throw new Exception($exceptionMessage." ".str_replace(array("\r\n", "\r", "\n"), "<br />", strip_tags($echoStr)));
			}
			return $echoStr;
		}
		
		return true;
	}
	
	function generate_password(){
		$vowels="aeiyou";
		$consonants="bcdfghjklmnpqrstvwxz";
		$pwd='';
		for ($i = 0; $i < 8; $i++) {
			if ($i%2==0) {
				$pwd.=$consonants[rand(0,strlen($consonants)-1)];
			} else {
				$pwd.=$vowels[rand(0,strlen($vowels)-1)];
			}
		}
		
		return $pwd;
	}
	
	// generates non existing code
	function generate_random_code($insertNewCodeInDb = true){
		$sql = "select pending_code from pending where pending_code = :0";
		$dontExit = true;
		while(isset($dontExit)){
			$code = generate_password();
			$prep = dbHelp::query($sql, array($code));
			$row = dbHelp::fetchRowByIndex($prep);
			$dontExit = $row[0];
		}
		
		if($insertNewCodeInDb){
			$sql = "insert into pending values (null, '".$code."')";
			dbHelp::query($sql);
		}
		
		return $code;
	}
	
	function getHappyHoursFromResource($resource){
		$hhArray = array();
		
		// getting all happyhours for this resource, in theory happy hours dont have any time colision(triggers (not by my choice))
		$sql = "select happyhour_assoc_happyhour from happyhour_assoc where happyhour_assoc_resource = :0 and happyhour_assoc_weekusage = 1";
		$prep = dbHelp::query($sql, array($resource));
		while($row = dbHelp::fetchRowByIndex($prep)){
			$hhArray[] = new HappyHour($row[0]);
		}
		
		return $hhArray;
	}
	
	// Returns all the number of slots used by a user in the week of the given date for a given resource, its resolution and the max ammount of hours a user can use the resource
	// $arr[0] = sum(entry_slots) as slotsUsed, $arr[1] = resource_resolution, $arr[2] = resource_maxhoursweek, $arr[3] = resource_resp 
	function getSlotsResolutionMaxHours($day, $month, $year, $user_id, $resource){
		require_once("hoursUsageAux.php");
		
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
		// $sql = "select sum(entry_slots), resource_resolution, resource_maxhoursweek, resource_resp from resource, entry where resource_id = :0 and entry_user = :1 and entry_status in (1,2) and entry_resource = :0 and ".dbHelp::dateBetween("entry_datetime", dbHelp::convertDateStringToTimeStamp($firstDayDate,'%Y%m%d'), dbHelp::convertDateStringToTimeStamp($lastDayDate,'%Y%m%d'));
		// $res = dbHelp::query($sql, array($resource, $user_id));
		// $arr = dbHelp::fetchRowByIndex($res);
		// if(!isset($arr[0])){
			// $arr[0] = 0;
		// }
		
		$hhArray = getHappyHoursFromResource($resource);
		
		$arr = array();
		$sql = "select resource_resolution, resource_maxhoursweek, resource_resp from resource where resource_id = :0";
		$prep = dbHelp::query($sql, array($resource));
		$row = dbHelp::fetchRowByIndex($prep);
		$arr[1] = $row[0]; // resource_resolution
		$arr[2] = $row[1]; // resource_maxhoursweek
		$arr[3] = $row[2]; // resource_resp
		
		$totalSlots = 0;
		$happyHourTime = 0;
		if($arr[2] > 0){
			$tempArray = array();
			$sql = "select entry_datetime, entry_slots from entry where entry_user = :0 and entry_status in (1,2) and entry_resource = :1 and ".dbHelp::dateBetween("entry_datetime", dbHelp::convertDateStringToTimeStamp($firstDayDate,'%Y%m%d'), dbHelp::convertDateStringToTimeStamp($lastDayDate,'%Y%m%d'));
			$prep = dbHelp::query($sql, array($user_id, $resource));
			while($row = dbHelp::fetchRowByName($prep)){
				$totalSlots += $row['entry_slots'];
				
				foreach($hhArray as $hh){
					if(($tempArray = $hh->getCostAndDiscountTime($row['entry_datetime'], $row['entry_slots'], $arr[1], 0)) !== false){
						$happyHourTime += $tempArray['time'];
					}
				}
			}
		}

		$arr[0] = $totalSlots - ceil($happyHourTime / $arr[1]);

		return $arr;
	}
	
	function cleanValue($value, $extraForbiddenOnes = ""){
		$forbiddenOnes = ";\/".$extraForbiddenOnes;
		$value = stripslashes($value);
		$value = strip_tags($value);
		$value = preg_replace("/[".$forbiddenOnes."]/",'',$value);
		return $value;
	}
	
	function convertDate($date, $toFormat){
		return date($toFormat, strtotime($date));
	}
	
	function inProjects($project, $projects){
		foreach($projects as $projectData){
			if($project == $projectData['id']){
				return true;
			}
		}
		
		return false;
	}
	
	function get_default_project($user){
		$sql = "
			select
				project_id, project_name
			from
				project join proj_dep_assoc on project_id = proj_dep_assoc_project
				join ".dbHelp::getSchemaName().".user on proj_dep_assoc_department = user_dep
				join department on department_id = user_dep and proj_dep_assoc_project = department_default
			where
				user_id = :0
				and proj_dep_assoc_active = 1
				and proj_dep_assoc_visible = 1
		";
		$prep = dbHelp::query($sql, array($user));
		$row = dbHelp::fetchRowByIndex($prep);
		if(isset($row[0])){
			return array('id' => $row[0], 'name' => $row[1]);
		}
		
		return false;
	}
	
	function get_projects($user){
		$projects = array();
		// $default = null;
		
		$sql = "
			select
				project_id, project_name
			from
				project join proj_dep_assoc on project_id = proj_dep_assoc_project
				join ".dbHelp::getSchemaName().".user on proj_dep_assoc_department = user_dep
			where
				user_id = :0
				and proj_dep_assoc_active = 1
				and proj_dep_assoc_visible = 1
			order by
				project_name
		";
		$prep = dbHelp::query($sql, array($user));
		while($row = dbHelp::fetchRowByIndex($prep)){
			// $default = $row[2];
			$projects[] = array('id' => $row[0], 'name' => $row[1]);
		}
		
		// $results = array('default' => $default);
		// if($projects[0] === null){
			// $projects = null;
		// }
		// $results = array('projects' => $projects);
		
		return (current($projects) === null) ? false : $projects;
	}
	
	function valid_project($user, $project){
		if(isset($user) && $user != '' && isset($project) && $project != ''){
			$sql = "
				select
					project_id
				from
					project join proj_dep_assoc on project_id = proj_dep_assoc_project
					join ".dbHelp::getSchemaName().".user on proj_dep_assoc_department = user_dep
					join department on department_id = user_dep and proj_dep_assoc_project = department_default
				where
					user_id = :0
					and proj_dep_assoc_project = :1
					and proj_dep_assoc_active = 1
					and proj_dep_assoc_visible = 1
			";
			$prep = dbHelp::query($sql, array($user, $project));
			$row = dbHelp::fetchRowByIndex($prep);
			if(isset($row) && isset($row[0])){
				return true;
			}
		}
		
		return false;
	}
	
	// Checks if the current php file is being viewed due to a ajax request
	function isAjax(){
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}
	
	function getProtocol(){
		$protocol = "http";
		if(!empty($_SERVER['HTTPS'])){
			$protocol = "https";
		}
		return $protocol;
	}
	
	// gets the current file path
	function get_server_address(){
		return getProtocol()."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	}
	
	// config params functions ****************
	function use_projects(){
		$sql = "select configParams_value from configParams where configParams_name = 'useProjects'";
		$prep = dbHelp::query($sql);
		$res = dbHelp::fetchRowByIndex($prep);
		
		return (isset($res[0]) && $res[0] == 1) ? true : false;
	}
	
	// ****************************************
	
	
	function convertSmartQuotes($string){ 
		// return str_replace($search, $replace, $string); 
		return str_replace("'", '"', $string); 
	} 
	
	// Detects if there was a get or post with ajax headers
	function visualExceptionHandling($exception){
		
		//initialize JSON object
		$json = new stdClass();
		
		if(isAjax()){
			$json->isError = true;
			$json->message = $exception->getMessage();
			echo json_encode($json);
		}
		else{
			global $jsWereImported;
			showMsg($exception->getMessage(), true, !$jsWereImported);
		}
	}
	
	$exceptionFilePath = "../agendo/exception.log";
	// Writes the exception error to a log file
	function logFileExceptionHandling($exception){
		global $exceptionFilePath;
		$maxSize = 5000; // bytes
		
		if(file_exists($exceptionFilePath) && filesize($exceptionFilePath)+strlen($error) > $maxSize){
			unlink($exceptionFilePath);
		}
		
		$logMessage = "Error: ".$exception->getMessage()."\nOn: ".date("d/m/Y H:i:s")."\n";
		wtf($logMessage, 'a', $exceptionFilePath);
	}
	
	function shutdownHandler(){
		$error = error_get_last();
		if(!empty($error)){
			// $json->message = "File:".$error['file']." | ln:".$error['line']." | msg:".$error['message'] .PHP_EOL;
			// $json->isError = true;
			// echo json_encode($json);
			// showMsg("File: ".str_replace("\\", "/", $error['file'])." | ln: ".$error['line']." | msg: ".$error['message'].PHP_EOL);
			showMsg("File: ".str_replace("\\", "/", $error['file'])." | ln: ".$error['line']." | msg: \"".convertSmartQuotes($error['message'])."\"", true);
			// throw new Exception("File:".$error['file']." | ln:".$error['line']." | msg:".$error['message'] .PHP_EOL);
		}
	}
	
	function exceptionHandling($byFile = false, $filePath = null){
		$exceptionHandlingFunction = null;
		
		if($byFile === true){
			global $exceptionFilePath;
			if(isset($filePath)){
				$exceptionFilePath = $filePath;
			}
			elseif(isset($_SESSION['path']) && $_SESSION['path'] != ''){
				$exceptionFilePath = $_SESSION['path']."/exception.log";
			}
			$exceptionHandlingFunction = "logFileExceptionHandling";
		}
		elseif($byFile === false){
			$exceptionHandlingFunction = "visualExceptionHandling";
		}

		set_exception_handler($exceptionHandlingFunction);
	}
	
	function htmlEncoding(){
		// echo "<meta http-equiv='Content-type' content='text/html;charset=UTF-8'>"; // html4
		echo "<meta charset='UTF-8'>"; // html5
	}
	
	function errorHandling($errno, $errstr, $errfile, $errline){
		if(
			$errno === E_ERROR
			|| $errno === E_WARNING
			|| $errno === E_PARSE
			// || $errno === E_NOTICE
		){
			$message = $errstr;
			$errfile = str_replace("\\", "/", $errfile);
			$errfile = explode("/", $errfile);
			$message .= " on file ".$errfile[sizeOf($errfile) - 1];
			$message .= " line ".$errline;
			throw new ErrorException($message);
		}
		return true;
	}
	
	// Errors handling
	exceptionHandling();
	set_error_handler('errorHandling');
	register_shutdown_function('shutdownHandler');
?>