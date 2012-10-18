<?php

require_once('commonCode.php');

	$statusArray = array(
		1 => array('name' => 'Confirmed', 'color' => '#e3f8a1')
		,2 => array('name' => 'Unconfirmed', 'color' => '#f39ea8')
		,3 => array('name' => 'Deleted', 'color' => 'grey')
	);
	
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
	
		$json = new stdClass();
		$dataArray = countStatusUsernameTotalEntries($user, $resource);
		$tempDivData = array();
		foreach($dataArray['entriesStatusArray'] as $status => $count){
			$divDataArray = nameProgressiveBarInfo($status, $statusArray[$status]['name'], $count, $dataArray['totalEntries']);
			$tempDivData[$divDataArray['id']] = array('width' => $divDataArray['width'], 'title' => $divDataArray['title']);
		}
		
		$json->divData = $tempDivData;
		$json->username = $dataArray['username'];
		echo json_encode($json);
		exit;
	}
	
	echo "<script type='text/javascript' src='../agendo/js/assiduity.js'></script>";
	echo "<link type='text/css' href='../agendo/css/assiduity.css' rel='stylesheet'/>";

	$resource = $_GET['resource'];
	$dataArray = countStatusUsernameTotalEntries($_SESSION['user_id'], $resource);
	echo "<label id='assiduityUserName' title='Shows how many entries were confirmed, unconfirmed and deleted by the user out of his/hers total entries'>";
		echo $dataArray['username'];
	echo "</label>";
	
	echo " assiduity";

	echo "<br>";

	echo "<div id='assiduityDiv' style='margin:auto;text-align:center;overflow:hidden;display:table;height:20px;'>";
		foreach($dataArray['entriesStatusArray'] as $status => $count){
			$divDataArray = nameProgressiveBarInfo($status, $statusArray[$status]['name'], $count, $dataArray['totalEntries']);
			echo "<div 
				id='".$divDataArray['id']."' 
				style='width:".$divDataArray['width']."px;background-color:".$statusArray[$status]['color'].";display:table-cell;' 
				title='".$divDataArray['title']."'
			></div>";
		}
	echo "</div>";
		
	
	function countStatusUsernameTotalEntries($user, $resource){
		global $statusArray;
		$resultsArray = array();
		$entriesStatusArray = array();
		foreach($statusArray as $status => $array){
			$entriesStatusArray[$status] = 0;
		}

		
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
				$entriesStatusArray[$res[0]] = $res[1];
			}
		}
		
		$resultsArray['entriesStatusArray'] = $entriesStatusArray;
		$resultsArray['totalEntries'] = $totalEntries;
		
		$sql = "select user_firstname, user_lastname from user where user_id = :0";
		$prep = dbHelp::query($sql, array($user));
		$res = dbHelp::fetchRowByIndex($prep);
		$resultsArray['username'] = $res[0]." ".$res[1];
		
		return $resultsArray;
	}

	function nameProgressiveBarInfo($status, $name, $upperDiv, $lowerDiv){
		$width100Percent = 140;
		$width = 5;
		if($lowerDiv > 0){
			$width = floor($upperDiv/$lowerDiv * $width100Percent);
		}
		$progBarNameExtra = 'AssProgDiv';

		return array('id' => $status."-".$progBarNameExtra, 'width' => $width, 'title' => $name.": ".$upperDiv."/".$lowerDiv);
	}
?>