<?php

require_once('commonCode.php');
	$assidVisualArray = array('lineBrkTr', 'titleTr');
	$statusArray = array(1 => array('confirmed', 'Confirmed', '#e3f8a1'), 2 => array('unconfirmed', 'Unconfirmed', '#f39ea8'), 3 => array('deleted', 'Deleted', 'grey'));
	
	if(isset($_POST['action'])
	&& $_POST['action'] == 'getDivData'
	){
		$resource = $_POST['resource'];
		if(isset($_POST['entry'])){
			$sql = "select entry_user from entry where entry_id = :0";
			$prep = dbHelp::query($sql, array($_POST['entry']));
			$res = dbHelp::fetchRowByIndex($prep);
			$user = $res[0];
		}
		else{
			$user = $_SESSION['user_id'];
		}
	
		$json = countStatusUsernameTotalEntries($user, $resource);
		echo json_encode($json);
		exit;
	}
	
	echo "<tr id='".$assidVisualArray[0]."' style='display:none;'><td colspan='2'><hr></hr></td></tr>";
	echo "<tr id='".$assidVisualArray[1]."' style='display:none;font-weight:bold;text-align:center;'><td colspan='2'>Assiduity in 0 entries</td></tr>";
	foreach($statusArray as $status){
		echo "
			<tr id='".$status[0]."Tr' style='display:none;'>
				<td>".$status[1]."</td>
				<td>
					<div id='".$status[0]."Div' style='height:12px;width:5px;background-color:".$status[2].";float:left;margin-left:5px;'></div>
					<label id='".$status[0]."Label' style='float:left;margin-left:10px;'>
						0%
					</label>
				</td>
			</tr>
		";
	}
	
	function countStatusUsernameTotalEntries($user, $resource){
		global $statusArray, $assidVisualArray;
		$resultsArray = array();
		$entriesStatusArray = array();
		
		$sql = "
			SELECT 
				`entry_status`
				,count( DISTINCT `entry_id` )
			FROM 
				entry left join resource on resource_id = entry_resource
			WHERE
				entry_user = :0
			AND (
					entry_status IN ( 1, 3 )
				OR (
					entry_status = 2 
					AND resource_id = entry_resource 
					AND date_add(".dbHelp::now().",INTERVAL resource_confirmtol HOUR ) > entry_datetime
				)
			)
			AND
				entry_resource = :1
			GROUP BY 
				entry_status
		";
		
		$totalEntries = 0;
		$prep = dbHelp::query($sql, array($user, $resource));
		// this is to go through all the entry status 
		while($res = dbHelp::fetchRowByIndex($prep)){
			if(isset($res[1])){
				$totalEntries += $res[1];
				$entriesStatusArray[$statusArray[$res[0]][0]] = array($res[1], $statusArray[$res[0]][1]);
			}
			else{
				$entriesStatusArray[$statusArray[$res[0]][0]] = array(0, $statusArray[$res[0]][1]);
			}
		}
		
		$resultsArray['assidVisualArray'] = $assidVisualArray;
		$resultsArray['entriesStatusArray'] = $entriesStatusArray;
		$resultsArray['totalEntries'] = $totalEntries;
		
		return $resultsArray;
	}

	// function nameProgressiveBarInfo($status, $name, $upperDiv, $lowerDiv){
		// $width100Percent = 140;
		// $width = 5;
		// if($lowerDiv > 0){
			// $width = floor($upperDiv/$lowerDiv * $width100Percent);
		// }
		// $progBarNameExtra = 'AssProgDiv';

		// return array('id' => $status."-".$progBarNameExtra, 'width' => $width, 'title' => $name.": ".$upperDiv."/".$lowerDiv);
	// }
?>