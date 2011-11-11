<?php
	require_once("commonCode.php");
	
	importJs();
	echo "<link href='css/monitoring.css' rel='stylesheet' type='text/css' />";
	echo "<script type='text/javascript' src='js/monitoring.js'></script>";

	// variables
	define('simEquip', isset($_GET['simEquip']));
	define('equipType', isset($_GET['equipType']));
	define('userLogged', (isset($_GET['userLogged']) && isset($_SESSION['user_id'])));
	
	// ugly fix for the waiting list/unconfirmed entry bad sql structure....
	$status2Entries = array();
	
	// if($_GET['res'] == '' && !isset($_SESSION['user_id'])){
		// showMsg('Resource needs to be specified or user has to be logged on.', true);
		// exit;
	// }
	// else{
		$resource = false;
		if(isset($_GET['res'])){
			$resource = (int)($_GET['res']);
		}
		
		if(isset($_SESSION['user_id'])){
			$user = (int)$_SESSION['user_id'];
		}
		
		if(isset($_GET['date'])){
			$date = date('Ymd', strtotime($_GET['date']));
		}
		else{
			$date = date('Ymd');
		}
	// }
	
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
	
	// ************************************* htmlStuff ***************************************************
	echo "<div id='everything' style='display:table;margin:auto;'>";
		if($resource != false){
			echo "<div class='checkLabel'>";
				labelCheckText('similarCheck', 'simEquip', 'Similar', simEquip, 'Shows similar resources');
				labelCheckText('equipTypepCheck', 'equipType', 'Type', equipType, 'Shows resources of the same type');
				if(isset($user)){
					labelCheckText('userCheck', 'userLogged', 'User', userLogged, 'Shows resources used by the currently logged user');
				}
			echo "</div>";
		}
		
		$mondayTime = getMondayTimeFromDate($date);
		echo "<table id='weekdaysResources'>";
			echo "<tr>";
				echo "<td>";
					echo "<div style='margin:auto;text-align:center;'>";
						echo "<span style='margin:auto;text-align:center;'>";
							echo "<a>".date('M Y', $mondayTime)."</a>";
							echo "<br>";
							
							echo "<img class='fakeLink' onClick='changeToDate(\"".date('Ymd', strtotime(" -7 days", $mondayTime))."\");' title='Shows previous week' width='12px' height='12px' src='".$_SESSION['path']."/pics/left.gif'/>";
							echo "<img class='fakeLink' onClick='changeToDate();' title='Shows current week' width='12px' height='12px' src='".$_SESSION['path']."/pics/today.gif'/>";
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
			
			doWeekDataArray($mondayTime);
		echo "</table>";
		
		echo "<div style='text-align:right;margin-top:3px;'>";
			global $entryStatusColor;
			foreach($entryStatusColor as $key => $value){
				$sql = "select status_name from status where status_id = ".$key;
				$prepSql = dbHelp::query($sql);
				$row = dbHelp::fetchRowByIndex($prepSql);
				echo "<div class='colorBlockText'><a style='color:white;'>".$row[0]."</a></div>";
				echo "<div class='colorBlock' style='background-color: ".$value."'></div>";
			}
			echo "<div class='colorBlockText'><a style='color:white;'>".$notConfirmedName."</a></div>";
			echo "<div id='bla' class='colorBlock' style='background-color: ".$notConfirmedColor."'></div>";
		echo "</div>";
	echo "</div>";
	
	// ***************************************** functions *************************************************
	function doWeekDataArray($mondayTime){
		global $resource;
		global $user;
		$fromSimSql = "";
		$whereSimSql = "";
		$fromTypeSql = "";
		$whereTypeSql = "";
		$whereSql = "";
		
		if(simEquip){
			$fromSimSql = ",similarresources";
			$whereSimSql = "or similarresources_resource = ".$resource." and resource_id = similarresources_similar";
		}

		if(equipType){
			$fromTypeSql = ",(select resource_type as restype from resource where resource_id = ".$resource.") as resTypes";
			$modifier = " and";
			if(!simEquip){
				$modifier = " or";
			}
			$whereTypeSql = $modifier." resource_type = restype";
		}
	
		$fromSql = 'resource';
		$whereSql = "resource_id = ".$resource;
		if(!$resource || (!simEquip && !equipType && userLogged)){
			$fromSql = 'resource, entry';
			$whereSql = "entry_user = ".$user." and entry_resource = resource_id";
		}
		
		$sql = "select distinct
			resource_id
			,resource_name
			,resource_starttime
			,resource_stoptime
		from
			".$fromSql."
			".$fromSimSql." 
			".$fromTypeSql." 
		where
			".$whereSql." 
			".$whereSimSql." 
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
					echo "<td>";
						echo "<img class='picLinks' src='".$_SESSION['path']."/pics/".$rowPic['pics_path']."'/>";
						echo "<a class='fakeLink' onclick='changeParentWindow(".$row['resource_id'].",\"".date('Ymd', $mondayTime - 24*60*60)."\", \"".$_SESSION['path']."\")' >";
							echo $row['resource_name'];
						echo "</a>";
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
	}
	
	function makeUsageDivs($startTime, $endTime, $resource, $timeOfWeek){
		global $entryStatusColor;
		global $notConfirmedColor;
		global $user;
		// and the fix shows its ugly head here
		global $status2Entries;
		
		$userSql = '';
		if(userLogged){
				$userSql = " and entry_user = ".$user." ";
		}
		$sql = "select 
			entry_datetime
			,entry_slots
			,entry_status
			,resource_resolution
		from
			resource
			,entry
		where
			entry_resource = ".$resource."
			and resource_id = entry_resource
			and entry_datetime like '".date("Y-m-d", $timeOfWeek)."%'
			and entry_status in (1,2,4,5)
			".$userSql."
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
					if($status2Entries[$entryStatus2Id] != $notConfirmedColor){
						echo "<script type='text/javascript'>changeDivColor('".$entryStatus2Id."', '".$colorToUse."');</script>";
					}
				}
				else{
					// creates the "usageBar" that indicates when the resource is being used
					echo "<div id='".$entryStatus2Id."' class='usageDataShow' style='width:".$usedWidth."px;background-color: ".$colorToUse.";' title='Scheduled for ".convertDate($row['entry_datetime'], 'H:i')."'></div>";
				}
			}
			
			$endAtWidth = $endTime * slotsPerHour;
			if($lastWidth < $endAtWidth){
				// creates the "noUsage" bar that indicates when the resource isn't being used
				echo "<div class='usageDataShow' style='width:".($endAtWidth - $lastWidth)."px;background-color: ".notUsedColor.";' title='Available from ".$availableStarting." to ".$endTime.":00'></div>";
			}
		}
	}
	
	function labelCheckText($id, $value, $text, $checked, $title, $showMessage = false , $message = ""){
		$action = "onChange='checkRedirect(this, ".(int)($showMessage).", \"".$message."\");'";

 		$extraHtml = "";
		if($checked){
			$extraHtml = "checked";
		}
		
		echo "<label id='".$id."' title='".$title."'>";
			echo "<input type='checkbox' value='".$value."' ".$action." ".$extraHtml."/>";
			echo "&nbsp";
			echo "<a style='color: white;'>".$text."</a>";
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
?>