<?php
session_start();
$pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
$_SESSION['path'] = "../../".$pathOfIndex[sizeof($pathOfIndex)-2];
require_once("../../agendo/commonCode.php");

if(isset($_POST['functionName'])){
	call_user_func($_POST['functionName']);
	exit;
}

echo "<script type='text/javascript' src='../../agendo/js/jquery-1.5.2.min.js'></script>";
echo "<script type='text/javascript' src='../../agendo/js/commonCode.js'></script>";
echo "<link type='text/css' href='../../agendo/css/jquery.jnotify.css' rel='stylesheet' media='all' />";
echo "<script type='text/javascript' src='../../agendo/js/jquery.jnotify.js'></script>";
echo "<link href='../../agendo/css/tipTip.css' rel='stylesheet' type='text/css'>";
echo "<script type='text/javascript' src='../../agendo/js/jquery.tipTip.js'></script>";
echo "<link href='tablet.css' rel='stylesheet' type='text/css'>";
echo "<script type='text/javascript' src='tablet.js'></script>";

function tablet_login(){
	try{
		if(!isset($_POST['pin'])){
			throw new Exception('No pin detected');
		}
		$pin = $_POST['pin'];

		if(!isset($_GET['resource'])){
			throw new Exception('No resource detected');
		}
		$resource = $_GET['resource'];

		$sql = "select user_firstname, user_lastname from ".dbHelp::getSchemaName().".user where user_id = '".$pin."'";
		$res = dbHelp::mysql_query2($sql) or die ($sql);
		$arr = dbHelp::mysql_fetch_row2($res);
		if(!isset($arr[0])){
			throw new Exception('No user detected with this pin');
		}
		
		// Not being used for anything other then checking if theres a user associated to the $pin
		// $firstName = $arr[0];
		// $lastName = $arr[1];
		
		// creates a random number(int) for the reperition table
		$randNumber = rand();
		$sql = "insert into repetition(repetition_code) values ('".$randNumber."')";
		$res = dbHelp::mysql_query2($sql) or die ($sql);
		
		// gets the id from repetition for the entry table
		$sql = "select repetition_id from repetition where repetition_code = '".$randNumber."'";
		$res = dbHelp::mysql_query2($sql) or die ($sql);
		$arr = dbHelp::mysql_fetch_row2($res);
		$repId = $arr[0];
		
		// converts date to sqlformat
		$sql = "select resource_resolution from resource where resource_id = ".$resource;
		$res = dbHelp::mysql_query2($sql) or die ($sql);
		$arr = dbHelp::mysql_fetch_row2($res);
		$remainder = time() % ($arr[0] * 60);
		$currentTime = dbHelp::convertDateStringToTimeStamp(date("YmdHi", time() - $remainder),'%Y%m%d%H%i');
		$sql = "select ".$currentTime;
		$res = dbHelp::mysql_query2($sql) or die ($sql);
		$arr = dbHelp::mysql_fetch_row2($res);
		$currentTime = $arr[0];
		
		// inserts in entry all values
		$sql = "insert into entry(entry_user, entry_datetime, entry_slots, entry_assistance, entry_repeat, entry_status, entry_resource, entry_action, entry_comments) values (".$pin.",'".$currentTime."',1,0,".$repId.",5,".$resource.",'".$currentTime."', null)";
		$res = dbHelp::mysql_query2($sql) or die ($sql);
		
		$json->success = true;
		$json->message = "User logged in";
	}
	catch(Exception $e){
		$json->success = false;
		$json->message = $e->getMessage();
	}
	
	echo json_encode($json);
}

function tablet_logout(){
	try{
		if(!isset($_POST['pin'])){
			throw new Exception('No pin detected');
		}
		$pin = $_POST['pin'];

		if(!isset($_GET['resource'])){
			throw new Exception('No resource detected');
		}
		$resource = $_GET['resource'];

		$sql = "select max(entry_id) from entry where entry_resource = ".$resource;
		$res = dbHelp::mysql_query2($sql) or die ($sql);
		$arr = dbHelp::mysql_fetch_row2($res);
		$entry = $arr[0];
		
		$sql = "select resource_resolution, entry_datetime, entry_user from resource, entry where resource_id = ".$resource." and entry_id = ".$entry;
		$res = dbHelp::mysql_query2($sql) or die ($sql);
		$arr = dbHelp::mysql_fetch_row2($res);
		$slots = ceil(((time() - strtotime($arr[1])) / 60.0) / $arr[0]);
		$currentTime = dbHelp::convertDateStringToTimeStamp(date("YmdHi", time() + 60),'%Y%m%d%H%i');
		if($pin != $arr[2]){
			throw new Exception("Wrong login");
		}
		
		$sql = "update entry set entry_action = ".$currentTime.", entry_slots = ".$slots.", entry_status = 1 where entry_id = ".$entry;
		$res = dbHelp::mysql_query2($sql) or die ($sql);
		
		$json->success = true;
		$json->message = "User logged out";
	}
	catch(Exception $e){
		$json->success = false;
		$json->message = $e->getMessage();
	}
	
	echo json_encode($json);
}

$resource = $_GET['resource'];
$action = $_GET['action'];
if(!isset($resource) || !isset($action)){
	//mudar isto para mostrar erro e pôr disabled o botão de login/logout
	echo "<meta HTTP-EQUIV='REFRESH' content='0; url=./'>";
}
echo "<table id='all'>";
	echo "<tr align='center'>";
		echo "<td>";
			echo "<table id='idButtons'>";
				$buttonId = 0;
				for($line = 0; $line<3; $line++){
					echo "<tr align='center'>";
					for($column = 0; $column<3; $column++){
						$buttonId++;
						echo "<td><input id='".$buttonId."' type='button' value='".$buttonId."' class='normalButton'onclick=returnButton(".$buttonId.")></input></td>";
					}
					echo "</tr>";
				}
				echo "<tr align='center'>";
					echo "<td><input id='0' class='normalButton' type='button' value='0' onclick=returnButton(".$buttonId.")></button></td>";
					echo "<td colspan='2'><input id = 'clearButton' class='bigButton' onclick=clearPin() type='button' value='Clear'></input></td>";
				echo "</tr>";
			echo "</table>";
		echo "</td>";

		$loginButtonClass = 'bigButton';
		$loginButtonValue = 'Login';
		// $text = 'No user is using this resource now';
		if($action != 'tablet_login'){
			// $loginButtonClass = 'redBigButton';
			$loginButtonValue = 'Logout';
			// $userData = resBeingUsed($resource);
			// $text = $userData[0]." ".$userData[1]." - Phone number: ".$userData[2]." - Phone extension: ".$userData[3];
		}
		echo "<td>";
			echo "<table>";
				echo "<tr align='center'><td><input type='password' id='pin' width='50' style='text-align:center;' disabled></td></tr>";
				// echo "<tr></tr>";
				echo "<br>";
				// echo "<tr align='center'><td><input id='enterExit' class='bigButton' onclick=userEnter(".$resource.") type='button' value='Login'></input></td></tr>";
				echo "<tr align='center'><td><input id='enterExit' class='".$loginButtonClass."' onclick=loginLogout('".$action."',".$resource.") type='button' value='".$loginButtonValue."'></input></td></tr>";
				echo "<tr align='center'><td><input id='toIndex' class='bigButton' type='button' onclick=\"window.location = '../tabletIndex.php';\" value='Exit'></input></td></tr>";
			echo "</table>";
		echo "</td>";
	echo "</tr>";
	
	// echo "<tr style='text-align:center;' ><td colspan = 2><label class='stateMessage' id='userLabel'>".$text."</label></td></tr>";
echo "</table>";
?>