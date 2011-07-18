<?php

session_start();
// remove cookie
// setcookie("customInterface", '', -1);
// unset($_COOKIE['customInterface']); 
// echo "cookie removed";

$pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
$_SESSION['path'] = "../".$pathOfIndex[sizeof($pathOfIndex)-1];
require_once("../agendo/commonCode.php");

if(isset($_GET['cron']){
	cronTask();
	exit;
}

importJs();
echo "<link href='tablet/tablet.css' rel='stylesheet' type='text/css'>";
echo "<script type='text/javascript' src='tablet/tablet.js'></script>";

try{
	if (isset($_COOKIE["customInterface"])){
		showResources($_COOKIE["customInterface"]);

		if(isset($_GET['message'])){
			showMsg($_GET['message']);
		}
	}
	else{
		makeCookie();
	}
}
catch(Exception $e){
	showMsg($e->getMessage());
	// echo $e->getMessage();
}

// one tablet per room
function showResources($cookieValue){
	$sql = "select distinct resinterface_resource, resource_name from resinterface, resource where resinterface_room=".$cookieValue." and resinterface_resource = resource_id";
	$res = dbHelp::mysql_query2($sql);
	if(dbHelp::mysql_numrows2($res) > 0){
		echo "<div id='resources' style='text-align:center;margin:auto;padding-left:7px;padding-right:7px;'>";

		// $arr=dbHelp::mysql_fetch_row2($res);
		// for($i = 0; $i<10; $i++){
		while($arr=dbHelp::mysql_fetch_row2($res)){
			$action = 'tablet_login';
			$resClassName = 'resource';
			$displayMessage = ' <br>is available';
			$displayMessageClass = 'available';
			// $title = 'Click to login on this resource';
			$extra = "";
			if(($resData = resBeingUsed($arr[0])) != false){
				$action = 'tablet_logout';
				$resClassName = 'resourceInUse';
				$displayMessage = ' <br>in use';
				$displayMessageClass = 'busy';
				// $title = "Resource is being used by ".$resData[0]." ".$resData[1]."<br> Phone number: ".$resData[2]."<br> Phone extension: ".$resData[3];
				$extra = "By: ".$resData[0]." ".$resData[1]."<br> Phone number: ".$resData[2]."<br> Phone extension: ".$resData[3];
			}
			
			echo "<div style='padding:13px;display:inline-block;'>";
			// echo "<div>";
				echo "<text class='".$displayMessageClass ."'>";
					echo $arr[1].$displayMessage;
				echo "</text>";
				
				echo "<br>";
				echo "<div class=".$resClassName." onclick=\"resourceClick(".$arr[0].", '".$action."')\">";
					echo "<img src='./pics/resource".$arr[0].".png' class='resourceImg' title='".$title."'/>";
					echo "<div class='busyUserInfo'>";
						echo $extra;
					echo "</div>";
				echo "</div>";
			echo "</div>";
		}
		
		echo "</div>";
	}
	else{
		throw new Exception("No resources associated to this room.");
	}
}

function resBeingUsed($resource){
	$sql = "select user_firstname, user_lastname, user_phone, user_phonext, user_email, entry_id from user, entry, (select max(entry_id) as maxentry from entry) as maxSelect where entry_id = maxentry and entry_resource=".$resource." and entry_status = 5 and entry_user = user_id";
	$res = dbHelp::mysql_query2($sql);
	if(dbHelp::mysql_numrows2($res) > 0){
		return dbHelp::mysql_fetch_row2($res);
	}
	
	return false;
}

function cronTask(){
	try{
		$sql = "select distinct resinterface_resource, resource_resolution, resource_maxslots, resource_name, user_firstname, user_lastname, user_email, user_phone from resinterface, resource, user where resinterface_resource = resource_id and resource_resp = user_id";
		$res = dbHelp::mysql_query2($sql);
		if(dbHelp::mysql_numrows2($res) > 0){
			while($arr=dbHelp::mysql_fetch_row2($res)){
				if(($resData = resBeingUsed($arr[0])) != false){ // $arr[0] = resinterface_resource
					$slots = ceil(((time() - strtotime($arr[1])) / 60.0) / $arr[1]); // $arr[1] = resource_resolution
					$currentTime = dbHelp::convertDateStringToTimeStamp(date("YmdHi", time()),'%Y%m%d%H%i');
					
					$entryStatus = 5;
					if($slots > $arr[2]){ // $arr[2] = resource_maxslots
						$entryStatus = 1;
						
						$subject = "Resource ".$arr[3]." time usage was exceeded"; // $arr[3] = resource_name
						$userMessage = "Resource ".$arr[3]." time usage was exceeded and the resource's responsible was notified."; // Add 'blood will be spilled' in the message and give the user a proper beating so he won't do this again.
						$responsibleMessage =  "Resource ".$arr[3]." time usage was exceeded by user ".$resData[0]." ".$resData[1].".\n" // $resData[0] = user first name, $resData[1] = user last name
												."The user's details are:\n"
												."Phone number: ".$resData[2] // $resData[2] = user Phone number
												."Phone extension: ".$resData[3] // $resData[3] = user Phone extension
												."Email: ".$resData[4] // $resData[4] = user Email
												;
						// $replyToPerson = "uicweb@igc.gulbenkian.pt";
						$replyToPerson = "";
						sendMail($subject,$resData[4], $userMessage, $replyToPerson, true);
						sendMail($subject, $arr[6], $responsibleMessage, $replyToPerson, true); // $arr[6] = responsible's email
						$sql = "update entry set entry_action = ".$currentTime.", entry_slots = ".$slots.", entry_status = ".$entryStatus." where entry_id = ".$resData[5];// $resData[5] = entry
						dbHelp::mysql_query2($sql);
					}
				}
			}
		}
		else{
			echo "No resources associated to this room.";
		}
	}
	catch(Exception $e){
		echo $e->getMessage();
	}
}

function makeCookie(){
		echo "<script type='text/javascript'>";
		echo "window.location = \"./tablet/tabletCookie.php?message='Cookie was created'\";";
		echo "</script>";
}
?>