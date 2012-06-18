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
	
	$dateString = strtotime($_GET['date']);
	if($dateString === false){
		$date = date('Ymd');
	}
	else{
		$date = date('Ymd', strtotime(" +1 days", strtotime($_GET['date'])));
	}
	
	define('simEquip', isset($_GET['simEquip']));
	// define('equipType', isset($_GET['equipType']));
	define('equipType', isset($_GET['equipType']) || isset($_GET['class']));
	define('userLogged', ($userChecked && isset($_SESSION['user_id'])));
	
	if(!isset($_SESSION['user_id']) && !isset($_GET['resource'])){
		return;
	}
	
	// ugly fix for the waiting list/unconfirmed entry bad sql structure....
	$entriesStarting = array();
	// yet another fix, this time for the eventual overlap of quickschedule entries
	$quickScheduleEntries = array();
	
	// start time for each resource
	define('startTime', 0);
	
	// end time for each resource
	define('endTime', 24);
	
	// slots per hour
	define('slotsPerHour', 4.0);
	
	// colors
	define('startOrEndColor', '#dddddd');
	define('notUsedColor', '#ffffff');
	
	$entryStatusColor = array(
		1 => '#e3f8a1', // confirmed entry
		2 => '#f8f3a5', // to be confirmed
		// 'red', 		// deleted
		4 => '#afdde5',	// monitored
		5 => '#fbc314', // in use
	);
	$notConfirmedName = 'Not Confirmed';
	$notConfirmedColor = '#f39ea8';
	
	// $sql = "select now()";
	// dbHelp::query($sql);
	
	$htmlToSend = "";
	$mondayTime = getMondayTimeFromDate($date);
	if(isset($_GET['gimmeGroupViewData'])){ 
		doWeekDataArray($mondayTime);
		exit;
	}
	
	// ************************************* htmlStuff ***************************************************
	importJs();
	echo "<script type='text/javascript' src='../agendo/js/monitoring.js'></script>";
	echo "<link href='../agendo/css/monitoring.css' rel='stylesheet' type='text/css' />";
	if($resource !== false){
		echo  "<script type='text/javascript'>setResourceAndDate('".$date."', '".$resource."');</script>";
	}
	
	if(isset($_GET['class']) && $_GET['class'] != 0){
		echo  "<script type='text/javascript'>setClass(".$_GET['class'].");</script>";
	}
	
	echo "<div id='groupdiv' class='dropMenu' style='text-align:center;'>";
		echo "<a style='text-align:center;color:#1E4F54;font-size:15px;'>Resource Multiview</a>";
		if($resource != false){
			echo "<div id='labelsDiv' class='checkLabel'>";
				echo "<a class='groupViewA' style='font-size:15px;'>Filter by: </a>";
				labelCheckText('similarCheck', 'simEquip', 'Similar', true, 'Shows similar resources');
				labelCheckText('equipTypeCheck', 'equipType', 'Type', equipType, 'Shows resources of the same type');
				if(isset($user)){
					labelCheckText('userCheck', 'userLogged', 'User', userLogged, 'Shows resources used by the currently logged user');
				}
			echo "</div>";
		}
		
		// This div will contain a table that contains the weekdays and its resources
		echo "<div id='tableHolder'>";
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
			echo "<div class='colorBlock' style='background-color: ".$notConfirmedColor."'></div>";
			echo "<div style='float:left;'><a>For time info click on calendar slots</a></div>";
		echo "</div>";
	echo "</div>";
	
	// ***************************************** functions *************************************************
	function doWeekDataArray($mondayTime){
		global $resource;
		global $user;
		global $htmlToSend;
		global $entriesStarting;
		global $quickScheduleEntries;
		
		$json;
		$json->errorMsg = "";
		$fromSimSql = "";
		$whereTypeSql = "";
		$whereSql = "resource_id = ".$resource;
		
		$htmlToSend .= "<table id='weekdaysResources'>";
		// Not entry divs
		$htmlToSend .= "<tr>";
			$htmlToSend .= "<td class='groupViewTd'>";
				$htmlToSend .= "<div class='resourcesNames' style='margin:auto;text-align:center;'>";
					$htmlToSend .= "<span style='margin:auto;text-align:center;'>";
						$htmlToSend .= "<a>".date('M Y', $mondayTime)."</a>";
						$htmlToSend .= "<br>";
						
						$htmlToSend .= "<img class='fakeLink' onClick='changeToDate(\"".date('Ymd', strtotime(" -7 days", $mondayTime))."\");' title='Shows previous week' width='12px' height='12px' src='".$_SESSION['path']."/pics/left.gif'/>";
						$htmlToSend .= "<img class='fakeLink' onClick='changeToDate(\"".date('Ymd')."\");' title='Shows current week' width='12px' height='12px' src='".$_SESSION['path']."/pics/today.gif'/>";
						$htmlToSend .= "<img class='fakeLink' onClick='changeToDate(\"".date('Ymd', strtotime(" +7 days", $mondayTime))."\");' title='Shows next week' width='12px' height='12px' src='".$_SESSION['path']."/pics/right.gif'/>";
					$htmlToSend .= "</span>";
				$htmlToSend .= "</div>";
			$htmlToSend .= "</td>";

			// each day of the week
			$weekDayWidth = (endTime-startTime)*slotsPerHour;
			for($i=0; $i<7; $i++){
				// $timeToAdd = $i*24*60*60;
				$htmlToSend .= "<td class ='weekdayTd' style='min-width: ".$weekDayWidth.";' onclick='scaleMe(".($i+1).")'>";
					// $dayTime = $mondayTime + $timeToAdd;
					$dayTime = strtotime(" +".$i." days", $mondayTime);
					$weekdayClass = "weekday";
					if(date('Ymd', $dayTime) == date('Ymd')){
						$weekdayClass = "weekdayRed";
					}
					$htmlToSend .= "<a class='".$weekdayClass."'>".date('d-', $dayTime).date('D', $dayTime)."</a>";
				$htmlToSend .= "</td>";
			}
		$htmlToSend .= "</tr>";
			
		// Entry divs code starting here
		if(equipType){
			$modifier = "and ";
			if(!userLogged && !simEquip){
				$modifier = "";
				$whereSql = "";
			}
			
			if(!isset($_GET['class'])){
				$sql = "select resource_type as restype from resource where resource_id = ".$resource;
				$prep = dbHelp::query($sql);
				$typeArray = dbHelp::fetchRowByIndex($prep);
				$whereTypeSql = $modifier."resource_type = ".$typeArray[0]; 
			}
			else{
				$whereTypeSql = $modifier."resource_type = ".(int)$_GET['class'];
			}
		}
	
		$fromUser = '';
		if(userLogged){
			$fromUser = ",(select resource_id as residuser from resource, permissions where (permissions_user = ".$user." and resource_id = permissions_resource) or resource_resp = ".$user.") as allowedRes";
			$whereSql = "resource_id = residuser";
		}

		if(simEquip){
			$sql = 
				"select distinct
					similarresources_id
				from
					similarresources
			";
			$prep = dbHelp::query($sql);
			if(dbHelp::numberOfRows($prep) > 0){
				$fromSimSql = ",similarresources";
				$whereSql = "similarresources_resource = ".$resource." and resource_id = similarresources_similar or resource_id = ".$resource;
				if(userLogged){
					$whereSql = "(similarresources_resource = ".$resource." and resource_id = similarresources_similar and resource_id = residuser or resource_id = ".$resource." and residuser = resource_id)";
				}
			}
		}

		$sql = "
			select distinct
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
				and resource_status not in (0, 2)
			order by
				resource_name
		";

		$prep = dbHelp::query($sql);
		if(dbHelp::numberOfRows($prep) > 0){
			while($row = dbHelp::fetchRowByName($prep)){
				$sqlPic = "select pics_path from pics where pics_resource = ".$row['resource_id'];
				$prepPic = dbHelp::query($sqlPic);
				$rowPic = dbHelp::fetchRowByName($prepPic);
				$htmlToSend .= "<tr>";
					$htmlToSend .= "<td class='groupViewTd'>";
						$htmlToSend .= "<div class='resourcesNames'>";
							$htmlToSend .= "<img class='picLinks' src='".$_SESSION['path']."/pics/".$rowPic['pics_path']."'/>";
							$htmlToSend .= "<a class='fakeLink' title='".$row['resource_name']."' onclick='changeParentWindow(".$row['resource_id'].",\"".date('Ymd', strtotime(" -1 days", $mondayTime))."\")' >";
								$htmlToSend .= $row['resource_name'];
							$htmlToSend .= "</a>";
						$htmlToSend .= "</div>";
					$htmlToSend .= "</td>";
					$startWidth = (startTime + $row['resource_starttime']) * slotsPerHour;
					$endWidth = (endTime - $row['resource_stoptime']) * slotsPerHour;
					// each day of the week
					for($i=0; $i<7; $i++){
						// $timeToAdd = $i*24*60*60;
						$htmlToSend .= "<td id='".$i.".".$row['resource_id']."' class='usage'>";
							// creates the "startOrEndBar" that indicates the resource's starttime
							$htmlToSend .= "<div class='usageDataShow' style='width:".$startWidth."px;background-color:".startOrEndColor."' title='Resource scheduling starts at: ".$row['resource_starttime'].":00'></div>";

							// makeUsageDivs($row['resource_starttime'], $row['resource_stoptime'], $row['resource_id'], ($mondayTime + $timeToAdd), !$resource);
							makeUsageDivs($row['resource_starttime'], $row['resource_stoptime'], $row['resource_id'], strtotime(" +".$i." days", $mondayTime));
							
							// creates the "startOrEndBar" that indicates the resource's stoptime
							$htmlToSend .= "<div class='usageDataShow' style='width:".$endWidth."px;background-color:".startOrEndColor."' title='Resource scheduling ends at: ".$row['resource_stoptime'].":00'></div>";
						$htmlToSend .= "</td>";
					}
				$htmlToSend .= "</tr>";
			}
		}
		else{
			$json->errorMsg = "No results found.";
		}
		$htmlToSend .= "</table>";
		$json->htmlCode = $htmlToSend;
		$json->entriesStarting = $entriesStarting;
		$json->quickScheduleEntries = $quickScheduleEntries;
		echo json_encode($json);
	}
	
	function makeUsageDivs($startTime, $endTime, $resource, $timeOfWeek){
		global $entryStatusColor;
		global $notConfirmedColor;
		global $user;
		global $htmlToSend;
		global $entriesStarting;
		global $quickScheduleEntries;
		
		$sql = "select 
			entry_datetime
			,entry_slots
			,entry_status
			,user_firstname
			,user_lastname
			,resource_resolution
			,resource_id
			,resource_status
			,resource_confirmtol
		from
			resource
			,entry
			,user
		where
			entry_resource = ".$resource."
			and resource_id = entry_resource
			and entry_datetime like '".date("Y-m-d", $timeOfWeek)."%'
			and entry_status in (1,2,4,5)
			and resource_status in (1, 3, 4, 5)
			and entry_user = user_id
			order by entry_datetime, entry_status asc, entry_action asc
		";
		$lastWidth = ($startTime - starTime) * slotsPerHour;
		$prep = dbHelp::query($sql);
		$availableStarting = date("H:i", strtotime($startTime.":00"));
		if(dbHelp::numberOfRows($prep) == 0){
			$htmlToSend .= "<div class='usageDataShow' style='width:".($endTime - $startTime)*slotsPerHour."px;background-color: ".notUsedColor.";' title='Available from ".$startTime.":00 to ".$endTime.":00'></div>";
		}
		else{
			$endAtWidth = $endTime * slotsPerHour;
			$entryEndTimes = array();
			while($row = dbHelp::fetchRowByName($prep)){
				$unusedWidth = ceil(getWidth($row['entry_datetime']) - $lastWidth);
				$usedWidth = floor($row['entry_slots'] * $row['resource_resolution'] / 60.0 * slotsPerHour);
				$lastWidth += $unusedWidth;
				if($lastWidth + $usedWidth > $endAtWidth){
					$usedWidth = $endAtWidth - $lastWidth;
				}
				$lastWidth += $usedWidth;
				
				// creates the "noUsage" bar that indicates when the resource isn't being used
				if($unusedWidth > 0){
					$htmlToSend .= "<div class='usageDataShow' style='width:".$unusedWidth."px;background-color: ".notUsedColor.";' title='Available from ".$availableStarting." to ".date('H:i', strtotime($row['entry_datetime']))."'></div>";
				}
				$entryLength = strtotime($row['entry_datetime']) + $row['entry_slots'] * $row['resource_resolution'] * 60;
				$availableStarting = date('H:i', $entryLength);
				$colorToUse = $entryStatusColor[$row['entry_status']];
				$weekDay = date('N', $timeOfWeek);
				$entryId = $row['resource_id']."-".$weekDay."-".convertDate($row['entry_datetime'], 'H:i');

				$createEntryDiv = !isset($entriesStarting[$entryId]);
				if(($row['entry_status'] == 2 || $row['entry_status'] == 4) && ($row['resource_status'] == 3 || $row['resource_status'] == 4)){
					if(time() > ($entryLength + $row['resource_confirmtol'] * $row['resource_resolution'] * 60)){
						$colorToUse = $notConfirmedColor;
					}
				}
				
				if($row['resource_status'] == 5){
					$entryEndTimesId = $row['resource_id']."-".$weekDay."-".date('H:i', ($entryLength - ($row['resource_resolution'] * 60)));
					if(isset($entryEndTimes[$entryId])){
						if(!isset($entriesStarting[$entryId])){
							$quickScheduleEntries[$entryEndTimes[$entryId]] = floor(1 * $row['resource_resolution'] / 60.0 * slotsPerHour);
						}
						else{
							$quickScheduleEntries[$entryEndTimes[$entryId]] = $usedWidth;
						}
					}
					else{
						$entryEndTimes[$entryEndTimesId] = $entryId;
					}
				}
				
				$entriesStarting[$entryId] = $colorToUse;
				if($createEntryDiv){
					// creates the "usageBar" that indicates when the resource is being used
					$htmlToSend .= "<div id='".$entryId."' class='usageDataShow' style='width:".$usedWidth."px;background-color: ".$colorToUse.";' title='Scheduled from ".convertDate($row['entry_datetime'], 'H:i')." to ".date("H:i", convertWidthToTime($usedWidth, $row['entry_datetime']))." by ".$row['user_firstname']." ".$row['user_lastname']."'></div>";
				}
			}
			
			if($lastWidth < $endAtWidth){
				// creates the "noUsage" bar that indicates when the resource isn't being used
				$htmlToSend .= "<div class='usageDataShow' style='width:".($endAtWidth - $lastWidth)."px;background-color: ".notUsedColor.";' title='Available from ".$availableStarting." to ".$endTime.":00'></div>";
			}
		}
	}
	
	function labelCheckText($id, $value, $text, $checked, $title){
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
		$date = strtotime("- ".($weekDay - 1)." days", $dateTime);
		return $date;
		// does not work when $date is a monday
		// return strtotime('last monday', strtotime($date));
	}
	
	// returns the number of slots from the starttime to the time given
	function getWidth($time){
		$minsSlots = (int)date('i' ,strtotime($time)) /60.0 * slotsPerHour;
		$hoursSlots = (int)date('H' ,strtotime($time)) * slotsPerHour;
		return  $minsSlots + $hoursSlots;
	}
	
	function convertWidthToTime($width, $startingFromDate = null){
		$widthTime = ($width*60/slotsPerHour)*60;
		if(isset($startingFromDate)){
			return strtotime($startingFromDate) + $widthTime;
		}
		return $widthTime;
	}
?>