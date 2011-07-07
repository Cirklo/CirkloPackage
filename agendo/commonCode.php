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
	
	function logIn(){
		$userLogin=$_POST['login'];
		$pass=$_POST['pass'];
		// $resource=$_GET['resource'];
		$passCrypted=$_POST['passCrypted'];

		if(
			!isset($userLogin) ||
			!isset($pass) ||
			!isset($passCrypted)
			// !isset($resource)
		){
			$json->success = false;
			$json->msg = "Did not get all the data needed to login.";
		}
		else{
			if($passCrypted == 'false'){
				$pass = cryptPassword($pass);
			}
			$sql= "select user_firstname, user_lastname, user_passwd, user_id from ".dbHelp::getSchemaName().".user where user_login = '".$userLogin."' and user_passwd = '".$pass."'";
			$res=dbHelp::mysql_query2($sql) or die ($sql);

			if (dbHelp::mysql_numrows2($res) == 0){
				$json->success = false;
				$json->msg = "Wrong Login!";
			}
			else{
				$arr=dbHelp::mysql_fetch_row2($res);
				$_SESSION['user_name'] = $arr[0];
				$_SESSION['user_lastName'] = $arr[1];
				$_SESSION['user_pass'] = $arr[2];
				$_SESSION['user_id'] = $arr[3];
				$_SESSION['database'] = dbHelp::getSchemaName();

				$json->success = true;
				$json->firstName = $arr[0];
				$json->lastName = $arr[1];
				// $json->resourceId = $resource;
			}
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
				// echo "<a style='cursor:pointer;font-size:".$textSize.";color:".$textColor."' onmouseover=\"this.style.color='".$textColorHover."'\" onmouseout=\"this.style.color='".$textColor."'\" title='Click here to go to the admin area' onclick=\"window.location='../datumo/admin.php'\"> AdminArea</a>";
			echo "</div>";
		}
	}
	
	function validUserAndPass($user, $pass, $passCrypted = false){
		if(!$passCrypted)
			$pass = cryptPassword($pass);

		$sql= "select user_id from ".dbHelp::getSchemaName().".user where user_login = '".$user."' and user_passwd = '".$pass."'";
		$res=dbHelp::mysql_query2($sql) or die ($sql);
		if(dbHelp::mysql_numrows2($res) <= 0)
			echo "Wrong Login";
		else{
			$arr=dbHelp::mysql_fetch_row2($res);
			$_SESSION['user_id'] = $arr[0];
			$_SESSION['user_pass'] = $pass;
			$_SESSION['database'] = dbHelp::getSchemaName();
			echo "";
		}
	}

	function cryptPassword($uncryptedPass){
	//admin
	//*B3580074E39C5F3D8A640E21295E2DC1098387F1
		// Current encrypting method
		// $sql="select password('". $uncryptedPass ."')";
		// $res=dbHelp::mysql_query2($sql);
		// $arrcheck=dbHelp::mysql_fetch_row2($res);
		// return $arrcheck[0];
		
	//admin
	//2127f97535023818d7add4a3c2428e06d382160daab440a9183690f18e285010
		// The future way to encrypt the password
		return hash('sha256', $uncryptedPass);
	}
	
	// Buttons for help, videos, resources and user/management
	function echoUserVideosResourceHelpLinks(){
		echo "<div id='linksImages'>";
			echo "<img style='cursor:pointer' width=30px id=help title='help' src=pics/ask.png onclick=\"javascript:window.open('http://www.cirklo.org/agendo_help.php','_blank','directories=no,status=no,menubar=yes,location=yes,resizable=yes,scrollbars=yes,width=1000,height=600')\" align='right'>";
			echo "<img style='cursor:pointer' width=30px id=video title='feature videos' src=pics/video.png onclick=go(this) align='right'>";
			echo "<img style='cursor:pointer' width=30px id=resources title='resource type' src=pics/resource.png onclick=go(this) align='right'>";
			echo "<img style='cursor:pointer' width=30px id=user title='user area' src=pics/user.png onclick=go(this) align='right'>";
		echo "</div>";
	}
	
	// Videos div
	function echoVideosDiv(){
		echo "<div id=videodiv align='center' style='cursor:pointer;padding:5px;display:none;position:absolute;width:150px;color:#444444;background-color:#FFFFFF;opacity:0.9;'>";
			$sql= "select media_name, media_link, media_description from media order by media_name";
			$res=dbHelp::mysql_query2($sql) or die ($sql);
			$vidWidth = 640;
			$vidHeight = 480;
			for ($i=0;$i<dbHelp::mysql_numrows2($res);$i++) {
				$arr=dbHelp::mysql_fetch_row2($res);
				echo "<a title='".$arr[2]."' onclick=\"javascript:window.open('".$arr[1]."','_blank','directories=no,status=no,menubar=yes,location=yes,resizable=yes,scrollbars=no,width=".$vidWidth.",height=".$vidHeight."')\">".$arr[0]."</a><br>";
			}
		echo "</div>";		
	}
	
	// Resources div
	function echoResourcesDiv(){
		echo "<div id=resourcesdiv  align='center' style='padding:5px;display:none;position:absolute;left:540px;width:250px;color:#444444;background-color:#FFFFFF;opacity:0.9'>\n";
			echo "<a href=index.php?class=0>All Resources</a><br>";
			echo "<a href=index.php>Most used</a>";
			echo "<hr>";
			$sql= "select * from resourcetype where resourcetype_id in (select distinct resource_type from resource) order by resourcetype_name";
			$res=dbHelp::mysql_query2($sql) or die ($sql);
			for ($i=0;$i<dbHelp::mysql_numrows2($res);$i++) {
				$arr=dbHelp::mysql_fetch_row2($res);
				echo "<a href=index.php?class=" .$arr[0] . ">" . $arr[1] . "</a><br>";
			}
		echo "</div>";
	}
	
	// User/management div
	function echoUserDiv($phpFile, $resource){
		// echo "<script type='text/javascript' src='../agendo/js/jquery-1.5.2.min.js'></script>";
		// echo "<script type='text/javascript' src='../agendo/js/commonCode.js'></script>";

		
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
								echo "<input type=button style='font-size:11px' onclick=\"window.location='../datumo/admin.php'\" value='AdminArea' />";
							echo "</td>";
						echo "</tr>";
					echo "</table>";
				}
				echo "<table style='display:".$display."'>";
					echo "<tr>";
						echo "<td><label>User Name</label></td>";
						echo "<td><input style='font-size:11px;' name=user_idm id=user_idm value='' onblur=ajaxUser(this) /></td>";
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
								echo "<input type=button style='font-size:11px' onclick=\"window.location='../agendo/admin/cookie.php'\" value='MakeCookie' />";
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
		$resFlag=dbHelp::mysql_query2($sqlFlag);
		$arrFlag=dbHelp::mysql_fetch_row2($resFlag);
		if($arrFlag[0] == '1'){
			if(isset($_SESSION['user_name']) && $_SESSION['user_name'] != ''){
				return true;
			}
			
			$sqlFlag = "select allowedips_iprange from allowedips";
			$resFlag=dbHelp::mysql_query2($sqlFlag);
			$ip = $_SERVER['REMOTE_ADDR'];
			$ip = substr($ip, 0, strripos($ip, '.'));
			while($arrFlag=dbHelp::mysql_fetch_row2($resFlag)){
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
	
?>