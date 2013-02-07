<?php
	require_once("commonCode.php");

	if(!isset($_SESSION['user_id'])){
		throw new Exception('You need to be logged in');
	}

	$isResp = isResp($_SESSION['user_id']);
	$isAdmin = isAdmin($_SESSION['user_id']);
	$isPI = isPI($_SESSION['user_id']);
	$userLevel = $_GET['userLevel'];
	
	$userCheck = isset($_GET['userCheck']);
	$resourceCheck = isset($_GET['resourceCheck']);
	$entryCheck = isset($_GET['entryCheck']);
	$projectCheck = isset($_GET['projectCheck']);
	
	$beginDate = $_POST['beginDate'];
	$endDate = $_POST['endDate'];
	
	$order_by = $_POST['iSortCol_0'];
	$order_by_direction = $_POST['sSortDir_0'];
	
	$happyHourArray = array();
	$resources = array();
	$results = array();
	$iTotalDisplayRecords = 0;
	$lineKey = "";
	$argumentsArray = array();
	
	$selectedDepartmentsArray = null;
	if(isset($_GET['departments'])){
		$selectedDepartmentsArray = json_decode($_GET['departments'], true);
	}
	$userLevels = getUserLevels($isAdmin, $isPI, $isResp, $userLevel);

	$htmlDisplayArray = array(
		array('name' => "Department", 'select' => 'department_name')
		, array('name' => "Username", 'select' => 'fullname')
		, array('name' => "Resource", 'select' => 'resource_name')
		, array('name' => "Project", 'select' => 'project_name')
		, array('name' => "Entry date", 'select' => 'entry_datetime')
		, array('name' => "Duration", 'select' => 'duration', 'function' => 'htmlDuration', 'args' => 'duration')
		, array('name' => "Price per hour", 'select' => 'price_value')
		, array('name' => "Discount", 'select' => 'discount')
		, array('name' => "Subtotal", 'select' => 'subtotal')
		, array('name' => "Total", 'select' => 'total')
	);
	
	$lowerLimit = $_POST['iDisplayStart'];
	$upperLimit = $_POST['iDisplayLength'];
	$iTotalRecords = 0;
	
	if(isset($_POST['action']) && $_POST['action'] == 'generateJson'){
		$isResp = isResp($_SESSION['user_id']);
		$isAdmin = isAdmin($_SESSION['user_id']);
		$isPI = isPI($_SESSION['user_id']);
		
		$userLevels = getUserLevels($isAdmin, $isPI, $isResp, $userLevel);
		
		$isAdmin = true;
		$userCheck = $resourceCheck = $entryCheck = $projectCheck = true;

		// $beginDate = "01/01/2001";
		// $endDate = "31/03/2013";
		
		$userLevel = "admin";
		echo json_encode(generateJson());
		exit;
	}
	
	htmlEncoding();
	importJs();
	
	// echo "<link href='css/reset-min.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/jquery.dataTables_themeroller.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/demo_table_jui.css' rel='stylesheet' type='text/css' />";
	// echo "<script type='text/javascript' src='js/jquery-1.8.2.min.js'></script>";
	echo "<link href='css/jquery.datepick.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/hourUsage.css' rel='stylesheet' type='text/css' />";
	// echo "<link href='css/demo_table.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/jquery-ui-1.9.1.custom.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/base.css' rel='stylesheet' type='text/css' />";
	echo "<script type='text/javascript' src='js/jquery.datepick.js'></script>";
	echo "<script type='text/javascript' src='js/hoursUsageSQL.js'></script>";
	// echo "<script type='text/javascript' src='js/jquery-ui-1.9.1.custom.js'></script>";
	echo "<script type='text/javascript' src='js/jquery-ui.js'></script>";
	echo "<script type='text/javascript' src='js/jquery.dataTables.min.js'></script>";
	
	$backLink = "
		<div style='margin:auto;width:200px;text-align:center;'>
			<a class='link' name='back' href='../datumo/'>Back to Admin Area</a>
		</div>
	";

	echo "<a name='top'></a>";
	
	echo "<br>";
	echo "<h1>Resource Usage</h1>";
	echo "<br>";
	
	
	echo "<table style='margin:auto;width:500px;'>";
		// ******* User priviledges checkboxes ********
		$numberOfPrivileges = 0;
		$privilegeHtml = "";

		if($isAdmin !== false){
			$checkedPrivilege = "";
			if($userLevels['isAdmin'] !== false){
				$checkedPrivilege = 'checked';
			}
			
			$numberOfPrivileges++;
			$privilegeHtml .= "
				<label style='color:white;float:right;margin-right:20px;'>
					Administrator
					<input type='radio' name='privilegesRadio' id='adminRadio' ".$checkedPrivilege."/>
				</label>
			";
		}
		
		if($isPI !== false){
			$checkedPrivilege = "";
			if($userLevels['isPI'] !== false){
				$checkedPrivilege = 'checked';
			}
			
			$numberOfPrivileges++;
			if($numberOfPrivileges > 1){
				$privilegeHtml .= "<br>";
			}
			
			$privilegeHtml .= "
				<label style='color:white;float:right;margin-right:20px;'>
					Department Manager
					<input type='radio' name='privilegesRadio' id='piRadio' ".$checkedPrivilege."/>
				</label>
			";
		}

		if($isResp !== false){
			$checkedPrivilege = "";
			if($userLevels['isResp'] !== false){
				$checkedPrivilege = 'checked';
			}
		
			$numberOfPrivileges++;
			if($numberOfPrivileges > 1){
				$privilegeHtml .= "<br>";
			}
			
			$privilegeHtml .= "
				<label style='color:white;float:right;margin-right:20px;'>
					Resource Manager
					<input type='radio' name='privilegesRadio' id='respRadio' ".$checkedPrivilege."/>
				</label>
			";
		}
		
		if($numberOfPrivileges > 1){
			echo "<tr>";
				echo "<td colspan='2' style='text-align:center;color:#F7C439;font-size:14px;' title='Pick the user level you wish to view the information as'>";
					echo "<fieldset style='width:200px;margin:auto;'>";
					echo "<legend>User level:</legend>";
					echo $privilegeHtml;
					echo "</fieldset>";
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td colspan='2'>";
					echo "<br>";
				echo "</td>";
			echo "</tr>";
		}
		// *********************************************
		
		echo "<tr>";
			echo "<td style='text-align:left;'>";
				echo "<a>From date:</a>";
				echo "&nbsp";
				echo "<input type='text' id='beginDateText' style='text-align:center;' value='".$beginDate."'/>";
			echo "</td>";

			echo "<td style='text-align:right;'>";
				echo "<a>To date:</a>";
				echo "&nbsp";
				echo "<input type='text' id='endDateText' style='text-align:center;' value='".$endDate."'/>";
			echo "</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td colspan='2'>";
				echo "<br>";
			echo "</td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td colspan='2' style='text-align:center;'>";
				echo "<input type='text' id='searchField' style='width:300px;' onkeypress='return synchInfo(event);'/>";

				echo "&nbsp";

				echo "<input type='button' id='searchButton' value='Generate Report' onclick='oTable.fnReloadAjax();'/>";
			echo "</td>";
		echo "</tr>";
		
	echo "</table>";

	echo "<br>";

	// Table where the results will appear
	echo "<div id='resultsDiv' style='margin:auto;width:1150;text-align:center;'>";
		echo $backLink;
		
		// to be removed, usefull for now to make sure the number of tds in the footer is the same as the header
		$footer_tds = "";
		echo "<table id='datatable' style='color: black;'>";
			echo "<thead>";
			foreach($htmlDisplayArray as $data){
				$number_of_tds++;
				echo "<td>";
					echo $data['name'];
				echo "</td>";
				
				$footer_tds .= "<td></td>";
			}
			echo "</thead>";
			
			echo "<tfoot style='background: white;border: 1px solid red;'>";
				echo "<tr>";
					echo $footer_tds;
				echo "</tr>";
			echo "</tfoot>";
		echo "</table>";
					
		echo "<div style='position:fixed;right:0px;bottom:0px;'>";
			echo "<a class='link' href='#top'>Top</a>";
			echo "&nbsp";
			echo "<a class='link' href='#bottom'>Bottom</a>";
		echo "</div>";
		
		echo "<br>";
		echo $backLink;
		echo "<a name='bottom'></a>";
	echo "</div>";
	
	// returns the available priviledges according to the userlevel if it exists
	function getUserLevels($isAdmin, $isPI, $isResp, $userLevel){
		if($isAdmin !== false && (empty($userLevel) || $userLevel == 'admin')){
			return array('isAdmin' => $isAdmin, 'isPI' => false, 'isResp' => false);
		}
		
		if($isPI !== false && (empty($userLevel) || $userLevel == 'pi')){
			return array('isAdmin' => false, 'isPI' => $isPI, 'isResp' => false);
		}

		if($isResp !== false && (empty($userLevel) || $userLevel == 'resp')){
			return array('isAdmin' => false, 'isPI' => false, 'isResp' => $isResp);
		}
		
		return array('isAdmin' => $isAdmin, 'isPI' => $isPI, 'isResp' => $isResp);
	}
	
	
	function generateJson($selectedDepartmentsArray = null){
		global $results, $iTotalDisplayRecords, $iTotalRecords, $htmlDisplayArray, $upperLimit, $lowerLimit;
		$aaData = array();
		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $iTotalRecords,
		);
		
		// sql injection danger here
		$order_by = $_POST['iSortCol_0'];
		$order_by_direction = $_POST['sSortDir_0'];
		$direction = array('asc' => 'asc', 'desc' => 'desc');
		if($htmlDisplayArray[$order_by]['select'] && $direction[$order_by_direction]){
			$order_by_sql = "order by ".$htmlDisplayArray[$order_by]['select']." ".$direction[$order_by_direction];
		}
		
		// $selectElement = next($htmlDisplayArray);
		// $selectSQL = $selectElement['select'];
		// while($selectElement = next($htmlDisplayArray)){
			// $selectSQL .= ", ".$selectElement['select'];
		// }
		
		// sql injection danger here
		$limit = "";
		if($upperLimit != -1){
			// $limit = "limit ".$lowerLimit.", ".$upperLimit;
			$limit = "limit ".intval($lowerLimit).", ".intval($upperLimit);
		}
		
		$sql = "
			select
				SQL_CALC_FOUND_ROWS
				department_name,
				concat(user_firstname, ' ', user_lastname) as fullname,
				resource_name,
				ifnull(project_name, 'No project') as project_name,
				entry_datetime,
				@duration := entry_slots * resource_resolution as duration,
				ifnull(price_value, 0) as price_value,
				@discount := entry_discount(entry_datetime, entry_slots, entry_resource, user_dep) as discount,
				@subtotal := @duration * ifnull(price_value, 0) as subtotal,
				@subtotal - @discount as total
			from 
				".dbHelp::getSchemaName().".user join entry on user_id = entry_user
				join department on department_id = user_dep
				join institute on institute_id = department_inst
				join resource on resource_id = entry_resource
				left join price on (price_resource = entry_resource and price_type = institute_pricetype)
				left join project on project_id = entry_project
			where
				entry_status in (1,2)
			".$order_by_sql."
			".$limit."
		";

		wtf($order_by_sql);
		$prep = dbHelp::query($sql, $selectedDepartmentsArray);
		
		// returns a number indicating how many rows the first SELECT would have returned had it been written without the LIMIT clause
		$sqlTotalRows = "select FOUND_ROWS()";
		$prepTR = dbHelp::query($sqlTotalRows);
		$resTR = dbHelp::fetchRowByIndex($prepTR);
		$iTotalDisplayRecords = $resTR[0];		
		
		$value = null;
		// data ********************
		while($row = dbHelp::fetchRowByName($prep)){
			$line = array();
			// fields *********************************
			foreach($htmlDisplayArray as $data){
				if(isset($data['function'])){
					$value = $data['function']($row[$data['select']]);
				}
				else{
					$value = $row[$data['select']];
				}
				$line[] = $value;
			}
			$aaData[] = $line;
		}

		$output['iTotalDisplayRecords'] = $iTotalDisplayRecords;
		$output['aaData'] = $aaData;
		
		return $output;
	}
	

	function htmlDuration($value){
		$hoursFloored = floor($value / 60);
		$minutes = $value % 60;
		
		$formatedValue = $hoursFloored."h : ".$minutes."m";
		return $formatedValue;
	}
?>