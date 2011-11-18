<?php
	require_once("commonCode.php");

	// variables
	$resource = false;
	$userChecked = true;
	if(isset($_GET['resource'])){
		$resource = (int)($_GET['resource']);
		$userChecked = isset($_GET['userLogged']);
	}
	
	if(isset($_SESSION['user_id'])){
		$user = (int)$_SESSION['user_id'];
	}
	
	if(isset($_GET['date'])){
		$date = date('Ymd', strtotime($_GET['date']));
		if(!isset($_GET['gimmeGroupViewData'])){
			$date = date('Ymd', strtotime(" +1 days", strtotime($_GET['date'])));
		}
	}
	else{
		$date = date('Ymd');
	}
	
	define('simEquip', isset($_GET['simEquip']));
	define('equipType', isset($_GET['equipType']));
	define('userLogged', ($userChecked && isset($_SESSION['user_id'])));
	
	if(!isset($_SESSION['user_id']) && !isset($_GET['resource'])){
		return;
	}
	
	// ugly fix for the waiting list/unconfirmed entry bad sql structure....
	$status2Entries = array();
	
	// start time for each resource
	define('startTime', 0);
	
	// end time for each resource
	define('endTime', 24);
	
	// slots per hour
	define('slotsPerHour', 4.0);
	
	// colors
	define('startOrEndColor', '#dddddd');
	define('notUsedColor', '#ffffff');
	// define('usedColor', '#7bb382');
	
	$entryStatusColor = array(
		1 => '#e3f8a1', // confirmed entry
		2 => '#f8f3a5', // to be confirmed
		// 'red', 		// deleted
		4 => '#afdde5',	// monitored
		5 => '#fbc314', // in use
	);
	$notConfirmedName = 'Not Confirmed';
	$notConfirmedColor = '#f39ea8';
	
	$mondayTime = getMondayTimeFromDate($date);
	if(isset($_GET['gimmeGroupViewData'])){ 
		doWeekDataArray($mondayTime);
		exit;
	}
	
	// ************************************* htmlStuff ***************************************************
	importJs();
	// echo "<link href='css/monitoring.css' rel='stylesheet' type='text/css' />";
	// echo "<script type='text/javascript' src='js/monitoring.js'></script>";
	echo "<script type='text/javascript' src='../agendo/js/monitoring.js'></script>";
	echo "<link href='../agendo/css/monitoring.css' rel='stylesheet' type='text/css' />";
	echo  "<script type='text/javascript'>setResourceAndDate('".$date."', '".$resource."');</script>";
	
	// echo "<div id='groupdiv' style='display:table;margin:auto;'>";
	echo "<div id='groupdiv' style='padding:5px;display:none;position:absolute;margin:auto;color:#444444;background-color:#FFFFFF;opacity:0.9;'>";
		// echo "<h1 style='text-align:center;color:#F7C439'>Resource Multiview</h1>";
		if($resource != false){
			echo "<div id='labelsDiv' class='checkLabel'>";
				// echo "<a style='color:white;'>Filter by: </a>";
				echo "<a class='groupViewA' style='font-size:15px;'>Filter by: </a>";
				// labelCheckText('similarCheck', 'simEquip', 'Similar', simEquip, 'Shows similar resources');
				labelCheckText('similarCheck', 'simEquip', 'Similar', true, 'Shows similar resources');
				labelCheckText('equipTypeCheck', 'equipType', 'Type', equipType, 'Shows resources of the same type');
				if(isset($user)){
					labelCheckText('userCheck', 'userLogged', 'User', userLogged, 'Shows resources used by the currently logged user');
				}
			echo "</div>";
		}
		
		// This div will contain a table that contains the weekdays and its resources
		echo "<div id='tableHolder'>";
			// doWeekDataArray($mondayTime);
		echo "</div>";
			
		echo "<div style='text-align:right;margin-top:3px;'>";
			global $entryStatusColor;
			foreach($entryStatusColor as $key => $value){
				$sql = "select status_name from status where status_id = ".$key;
				$prepSql = dbHelp::query($sql);
				$row = dbHelp::fetchRowByIndex($prepSql);
				echo "<div class='colorBlockText'><a>".$row[0]."</a></div>";
				echo "<div class='colorBlock' style='background-color: ".$value."'></div>";
			}
			echo "<div class='colorBlockText'><a>".$notConfirmedName."</a></div>";
			echo "<div id='bla' class='colorBlock' style='background-color: ".$notConfirmedColor."'></div>";
			echo "<div style='float:left;'><a>For time info click on calendar slots</a></div>";
		echo "</div>";
	echo "</div>";
	
	// ***************************************** functions *************************************************
	function doWeekDataArray($mondayTime){
		global $resource;
		global $user;
		$fromSimSql = "";
		$whereTypeSql = "";
		$whereSql = "resource_id = ".$resource;
		
		echo "<table id='weekdaysResources'>";
		// Not entry divs
		echo "<tr>";
			echo "<td class='groupViewTd'>";
				echo "<div class='resourcesNames' style='margin:auto;text-align:center;'>";
					echo "<span style='margin:auto;text-align:center;'>";
						echo "<a>".date('M Y', $mondayTime)."</a>";
						echo "<br>";
						
						echo "<img class='fakeLink' onClick='changeToDate(\"".date('Ymd', strtotime(" -7 days", $mondayTime))."\");' title='Shows previous week' width='12px' height='12px' src='".$_SESSION['path']."/pics/left.gif'/>";
						echo "<img class='fakeLink' onClick='changeToDate(\"".date('Ymd')."\");' title='Shows current week' width='12px' height='12px' src='".$_SESSION['path']."/pics/today.gif'/>";
						echo "<img class='fakeLink' onClick='changeToDate(\"".date('Ymd', strtotime(" +7 days", $mondayTime))."\");' title='Shows next week' width='12px' height='12px' src='".$_SESSION['path']."/pics/right.gif'/>";
					echo "</span>";
				echo "</div>";
			echo "</td>";

			// each day of the week
			for($i=0; $i<7; $i++){
				$timeToAdd = $i*24*60*60;
				echo "<td class ='weekday'>";
					$dayTime = $mondayTime + $timeToAdd;
					$weekdayColorText = "black";
					if(date('Ymd', $dayTime) == date('Ymd')){
						$weekdayColorText = "#bb3322";
					}
					echo "<a style='color:".$weekdayColorText.";'>".date('d-', $dayTime).date('D', $dayTime)."</a>";
				echo "</td>";
			}
		echo "</tr>";
			
		// Entry divs code starting here
		if(equipType){
			$modifier = "and ";
			if(!userLogged && !simEquip){
				$modifier = "";
				$whereSql = "";
			}
			$sql = "select resource_type as restype from resource where resource_id = ".$resource;
			$prep = dbHelp::query($sql);
			$typeArray = dbHelp::fetchRowByIndex($prep);
			$whereTypeSql = $modifier."resource_type = ".$typeArray[0]; 
		}
	
		$fromUser = '';
		if(userLogged){
			$fromUser = ",(select resource_id as residuser from resource, permissions where (permissions_user = ".$user." or resource_resp = ".$user.") and resource_id = permissions_resource) as allowedRes";
			$whereSql = "resource_id = residuser";
		}

		if(simEquip){
			$fromSimSql = ",similarresources";
			$whereSql = "similarresources_resource = ".$resource." and resource_id = similarresources_similar or resource_id = ".$resource;
			if(userLogged){
				// $whereSql = "similarresources_resource = residuser and resource_id = similarresources_similar or resource_id = residuser and resource_id = ".$resource;
				// $whereSql = "similarresources_resource = residuser and (resource_id = similarresources_similar or resource_id = residuser)";
				$whereSql = "(similarresources_resource = ".$resource." and resource_id = similarresources_similar and resource_id = residuser or resource_id = ".$resource." and residuser = resource_id)";
			}
		}

		$sql = "select distinct
			resource_id
			,resource_name
			,resource_starttime
			,resource_stoptime
		from
			resource
			".$fromType." 
			".$fromUser." 
			".$fromSimSql." 
		where
			".$whereSql." 
			".$whereTypeSql." 
		order by
			resource_name
		";

		$prep = dbHelp::query($sql);
		if(dbHelp::numberOfRows($prep) > 0){
			while($row = dbHelp::fetchRowByName($prep)){
				$sqlPic = "select pics_path from pics where pics_resource = ".$row['resource_id'];
				$prepPic = dbHelp::query($sqlPic);
				$rowPic = dbHelp::fetchRowByName($prepPic);
				echo "<tr>";
					echo "<td class='groupViewTd'>";
						echo "<div class='resourcesNames'>";
						echo "<img class='picLinks' src='".$_SESSION['path']."/pics/".$rowPic['pics_path']."'/>";
						echo "<a class='fakeLink' title='".$row['resource_name']."' onclick='changeParentWindow(".$row['resource_id'].",\"".date('Ymd', strtotime(" -1 days", $mondayTime))."\")' >";
							echo $row['resource_name'];
						echo "</a>";
						echo "</div>";
					echo "</td>";
					$startWidth = (startTime + $row['resource_starttime']) * slotsPerHour;
					$endWidth = (endTime - $row['resource_stoptime']) * slotsPerHour;
					// each day of the week
					for($i=0; $i<7; $i++){
						$timeToAdd = $i*24*60*60;
						// id = weekDay.resource
						echo "<td id='".$i.".".$row['resource_id']."' class='usage'>";
							// creates the "startOrEndBar" that indicates the resource's starttime
							echo "<div class='usageDataShow' style='width:".$startWidth."px;background-color:".startOrEndColor."' title='Resource scheduling starts at: ".$row['resource_starttime'].":00'></div>";
							
							makeUsageDivs($row['resource_starttime'], $row['resource_stoptime'], $row['resource_id'], ($mondayTime + $timeToAdd), !$resource);
							
							// creates the "startOrEndBar" that indicates the resource's stoptime
							echo "<div class='usageDataShow' style='width:".$endWidth."px;background-color:".startOrEndColor."' title='Resource scheduling ends at: ".$row['resource_stoptime'].":00'></div>";
						echo "</td>";
					}
				echo "</tr>";
			}
		}
		else{
			showMsg('No results found.');
		}
		echo "</table>";
	}
	
	function makeUsageDivs($startTime, $endTime, $resource, $timeOfWeek){
		global $entryStatusColor;
		global $notConfirmedColor;
		global $user;
		// and the fix shows its ugly head here
		global $status2Entries;
		
		$fromUser = '';
		$whereUser = '';
		// if(userLogged){
			// $fromUser = ',permissions';
			// $whereUser = " and permissions_user = ".$user." or resource_resp = ".$user;
		// }
		$sql = "select 
			entry_datetime
			,entry_slots
			,entry_status
			,resource_resolution
		from
			resource
			,entry
			".$fromUser."
		where
			entry_resource = ".$resource."
			and resource_id = entry_resource
			and entry_datetime like '".date("Y-m-d", $timeOfWeek)."%'
			and entry_status in (1,2,4,5)
			".$whereUser."
			order by entry_datetime, entry_status asc
		";
		$lastWidth = ($startTime - starTime) * slotsPerHour;
		$prep = dbHelp::query($sql);
		$availableStarting = date("H:i", strtotime($startTime.":00"));
		if(dbHelp::numberOfRows($prep) == 0){
			echo "<div class='usageDataShow' style='width:".($endTime - $startTime)*slotsPerHour."px;background-color: ".notUsedColor.";' title='Available from ".$startTime.":00 to ".$endTime.":00'></div>";
		}
		else{
			while($row = dbHelp::fetchRowByName($prep)){
				$unusedWidth = floor(getWidth($row['entry_datetime']) - $lastWidth);
				$usedWidth = ceil($row['entry_slots'] * $row['resource_resolution'] / 60.0 * slotsPerHour);
				$lastWidth += $unusedWidth + $usedWidth;
				// creates the "noUsage" bar that indicates when the resource isn't being used
				echo "<div class='usageDataShow' style='width:".$unusedWidth."px;background-color: ".notUsedColor.";' title='Available from ".$availableStarting." to ".date('H:i', strtotime($row['entry_datetime']))."'></div>";
				$entryLength = strtotime($row['entry_datetime']) + $row['entry_slots'] * $row['resource_resolution'] * 60;
				$availableStarting = date('H:i', $entryLength);
				// method to "convert" pre-reserved entries to unconfirmed ones
				$colorToUse = $entryStatusColor[$row['entry_status']];
				$entryStatus2Id = date('N', $timeOfWeek)."-".convertDate($row['entry_datetime'], 'H:i');
				if($row['entry_status'] == 2){
					if($entryLength < time()){
						$colorToUse = $notConfirmedColor;
					}
					$status2Entries[$entryStatus2Id] = $colorToUse;
				}
				
				// behold the ugly patch!!
				if($row['entry_status'] == 4){
					if(!isset($status2Entries[$entryStatus2Id])){
						// need to clean this cr... stuff somehow
						if($entryLength < time()){
							$colorToUse = $notConfirmedColor;
						}
						$status2Entries[$entryStatus2Id] = $colorToUse;
						echo "<div id='".$entryStatus2Id."' class='usageDataShow' style='width:".$usedWidth."px;background-color: ".$colorToUse.";' title='Scheduled for ".convertDate($row['entry_datetime'], 'H:i')."'></div>";
					}
					elseif($status2Entries[$entryStatus2Id] != $notConfirmedColor){
						echo "<script type='text/javascript'>changeDivColor('".$entryStatus2Id."', '".$colorToUse."');</script>";
					}
				}
				else{
					// creates the "usageBar" that indicates when the resource is being used
					echo "<div id='".$entryStatus2Id."' class='usageDataShow' style='width:".$usedWidth."px;background-color: ".$colorToUse.";' title='Scheduled for ".convertDate($row['entry_datetime'], 'H:i')." to ".date("H:i", convertWidthToTime($usedWidth, $row['entry_datetime']))."'></div>";
				}
			}
			
			$endAtWidth = $endTime * slotsPerHour;
			if($lastWidth < $endAtWidth){
				// creates the "noUsage" bar that indicates when the resource isn't being used
				echo "<div class='usageDataShow' style='width:".($endAtWidth - $lastWidth)."px;background-color: ".notUsedColor.";' title='Available from ".$availableStarting." to ".$endTime.":00'></div>";
			}
		}
	}
	
	function labelCheckText($id, $value, $text, $checked, $title){
		// $action = "onChange='checkRedirect(this);'";
		$action = "onChange='getTableData();'";

 		$extraHtml = "";
		if($checked){
			$extraHtml = "checked";
		}
		
		echo "<label title='".$title."'>";
			echo "<input id='".$id."' style='margin-left:5px;' type='checkbox' value='".$value."' ".$action." ".$extraHtml."/>";
			echo "&nbsp";
			echo "<a>".$text."</a>";
			echo "&nbsp";
		echo "</label>";
	}
	
	function getMondayTimeFromDate($date){
		$dateTime = strtotime($date);
		// int number corresponding to $date's  day of the week
		$weekDay = date('N', $dateTime);
		// gets $date's monday time
		$date = $dateTime - ($weekDay - 1)*24*60*60;
		return $date;
	}
	
	// returns the number of slots from the starttime to the time given
	function getWidth($time){
		$minsSlots = (int)date('i' ,strtotime($time)) /60.0 * slotsPerHour;
		$hoursSlots = (int)date('H' ,strtotime($time)) * slotsPerHour;

		return  $minsSlots + $hoursSlots;
	}
	
	function convertDate($date, $toFormat){
		return date($toFormat, strtotime($date));
	}
	
	function convertWidthToTime($width, $startingFromDate = null){
		$widthTime = ($width*60/slotsPerHour)*60;
		if(isset($startingFromDate)){
			return strtotime($startingFromDate) + $widthTime;
		}
		return $widthTime;
	}
?>