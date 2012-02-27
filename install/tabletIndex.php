<?php

session_start();
$pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
$_SESSION['path'] = "../".$pathOfIndex[sizeof($pathOfIndex)-1];
require_once("../agendo/commonCode.php");

if(isset($argv) && sizeof($argv) >= 1 && $argv[1] == 'cron'){
	cronTask();
	exit;
}

importJs();
echo "<link href='tablet/tablet.css' rel='stylesheet' type='text/css'>";
echo "<script type='text/javascript' src='tablet/tablet.js'></script>";

try{
	// if (isset($_COOKIE["customInterface"])){
		// showResources($_COOKIE["customInterface"]);
		showResources();

		if(isset($_GET['message'])){
			showMsg($_GET['message']);
		}
	// }
	// else{
		// makeCookie();
	// }
}
catch(Exception $e){
	showMsg($e->getMessage());
	// echo $e->getMessage();
}

// one tablet per room
// function showResources($cookieValue){
function showResources(){
	$sql = "select distinct resource_id, resource_name, pics_path from resource, pics where resource_status = 5 and pics_resource = resource_id";
	$res = dbHelp::query($sql);
	if(dbHelp::numberOfRows($res) > 0){
		echo "<div id='resources' style='text-align:center;margin:auto;padding-left:7px;padding-right:7px;'>";

		// $arr=dbHelp::fetchRowByIndex($res);
		// for($i = 0; $i<10; $i++){
		while($arr=dbHelp::fetchRowByIndex($res)){
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
					echo "<img src='./pics/".$arr[2]."' class='resourceImg' title='".$title."'/>";
					echo "<div class='busyUserInfo'>";
						echo $extra;
					echo "</div>";
				echo "</div>";
			echo "</div>";
		}
		
		echo "</div>";
	}
	else{
		throw new Exception("No resources available for this interface.");
	}
}

function resBeingUsed($resource){
	// $sql = "select user_firstname, user_lastname, user_phone, user_phonext, user_email, entry_id, entry_datetime, entry_repeat from user, entry, (select max(entry_id) as maxentry from entry) as maxSelect where entry_id = maxentry and entry_resource=".$resource." and entry_status = 5 and entry_user = user_id";
	$sql = "select user_firstname, user_lastname, user_phone, user_phonext, user_email, maxentry, entry_datetime, entry_repeat, user_id from user, entry, (select max(entry_id) as maxentry from entry where entry_resource = ".$resource.") as maxSelect where entry_resource=".$resource." and entry_status = 5 and entry_user = user_id and entry_id = maxentry";
	$res = dbHelp::query($sql);
	if(dbHelp::numberOfRows($res) > 0){
		return dbHelp::fetchRowByIndex($res);
	}
	
	return false;
}

function cronTask(){
	try{
		$sql = "select distinct resource_id, resource_resolution, resource_maxslots, resource_name, user_firstname, user_lastname, user_email, user_phone, resource_starttime, resource_stoptime, user_id from resource, user where resource_status = 5 and resource_resp = user_id";
		$res = dbHelp::query($sql);
		if(dbHelp::numberOfRows($res) > 0){
			while($arr=dbHelp::fetchRowByIndex($res)){
				if(($resData = resBeingUsed($arr[0])) != false){ // $arr[0] = resource_id
					$slots = ceil(((time() - strtotime($resData[6])) / 60.0) / $arr[1]); // $resData[6] = entry_datetime, $arr[1] = resource_resolution
					$currentTime = dbHelp::convertDateStringToTimeStamp(date("YmdHi", time()),'%Y%m%d%H%i');
					
					$startTimeSecs = (int)$arr[8]*3600; // $arr[8] = resource_starttime
					$endTimeSecs = (int)$arr[9]*3600; // $arr[9] = resource_stoptime
					$currentTimeSecs = (int)date('H')*3600 + (int)date('i')*60;
					
					$sql = "update entry set entry_action = ".$currentTime.", entry_slots = ".$slots.", entry_status = 5 where entry_id = ".$resData[5];// $resData[5] = entry
					// if currentTime == resource_starttime || its a different day from when the the entry was made
					// insert newEntry, sameUser, entry_datetime = resource_starttime
					$previousDay = date('w', strtotime($resData[6]));
					$today = date('w');
					if($currentTimeSecs == $startTimeSecs || $previousDay != $today){
						// getting current timeslot
						$sqlSlot = "select resource_resolution from resource where resource_id = ".$arr[0];
						$resSlot = dbHelp::query($sqlSlot);
						$arrSlot = dbHelp::fetchRowByIndex($resSlot);
						$remainder = time() % ($arrSlot[0] * 60);
						$currentTimeSlot = dbHelp::convertDateStringToTimeStamp(date("YmdHi", time() - $remainder),'%Y%m%d%H%i');
						
						// entry_user = $resData[8], entry_datetime = $currentTime, entry_slots = 1, entry_slots = 0, entry_repeat = $resData[7], entry_status = 5, entry_resource = $arr[0], entry_action = $currentTime, entry_comments = null
						$sql = "insert into entry(entry_user, entry_datetime, entry_slots, entry_assistance, entry_repeat, entry_status, entry_resource, entry_action, entry_comments) values (".$resData[8].",".$currentTimeSlot.",1,0,".$resData[7].",5,".$arr[0].",".$currentTime.", null)";
						echo "Resource start time reached.\n";
					}
					// if currentTime == resource_endtime
					// send email, set status to logged out
					else if($currentTimeSecs == $endTimeSecs){
						echo "Resource stop time reached.\n";
						notifyUserAndResp($arr[3], $resData[0]." ".$resData[1], $resData[2], $resData[3], $resData[4], $arr[6]);
					}
					// if currentTime > resource_starttime and currentTime < resource_endtime
					// update number of slots, if maxSlotNumber*i is reached send email
					else if($currentTimeSecs > $startTimeSecs && $currentTimeSecs < $endTimeSecs){
						// if(($slots - 1) % $arr[2] == 0){ // $arr[2] = resource_maxslots
						if(($slots - 1) % $arr[2] == 0 && ($slots - 1) / $arr[2] != 0){ // $arr[2] = resource_maxslots
							// $arr[3] = resource_name, $resData[0] = user first name, $resData[1] = user last name, $resData[2] = user Phone number, $resData[3] = user Phone extension, $resData[4] = user Email, $arr[6] = responsible's email
							echo "Maximum slot time reached.\n";
							notifyUserAndResp($arr[3], $resData[0]." ".$resData[1], $resData[2], $resData[3], $resData[4], $arr[6]);
						}
						else{
							echo "Number of slots update.\n";
						}
					}
					dbHelp::query($sql);
				}
			}
			echo "Cron job completed.";
		}
		else{
			echo "No resources associated to this room.";
		}
		echo "(".date('d/m/Y H:i').")"."\n\n";
	}
	catch(Exception $e){
		echo "\nError:\n".str_replace("<br>", "\n", $e->getMessage())."\n"."(".date('d/m/Y H:i').")"."\n\n";
	}
}

function notifyUserAndResp($resource_name, $usersName, $usersPhoneNumber, $usersPhoneExtension, $usersEmail, $respEmail){
	$subject = "Resource ".$resource_name." time usage was exceeded"; // $resource_name = resource_name
	$userMessage = "Resource ".$resource_name." time usage was exceeded and the resource's responsible has been notified."; // $resource_name = resource_name, Add 'blood will be spilled' in the message and give the user a proper beating so he won't do this again.
	$responsibleMessage =  "Resource ".$resource_name." time usage was exceeded by user ".$usersName.".\n" // $resource_name = resource_name, $resData[0] = user first name, $resData[1] = user last name
							."The user's details are:\n"
							."Phone number: ".$usersPhoneNumber."\n" // $resData[2] = user Phone number
							."Phone extension: ".$usersPhoneExtension."\n" // $resData[3] = user Phone extension
							."Email: ".$usersEmail // $resData[4] = user Email
							;
	// $replyToPerson = "uicweb@igc.gulbenkian.pt";
	echo "Resource ".$resource_name." time usage was exceeded by user ".$usersName.".\n";
	$replyToPerson = "";
	sendMail($subject,$usersEmail, $userMessage, $replyToPerson);
	sendMail($subject, $respEmail, $responsibleMessage, $replyToPerson);
}

function makeCookie(){
		echo "<script type='text/javascript'>";
		echo "window.location = \"./tablet/tabletCookie.php\";";
		echo "</script>";
}
?>