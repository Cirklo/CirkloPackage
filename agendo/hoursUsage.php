<?php
	require_once("commonCode.php");

	if(!isset($_SESSION['user_id'])){
		throw new Exception('You need to be logged in');
	}

	$isResp = isResp($_SESSION['user_id']);
	$isAdmin = isAdmin($_SESSION['user_id']);
	$isPI = isPI($_SESSION['user_id']);

	$userLevel = $_POST['userLevel'];
	$userLevels = getUserLevels($isAdmin, $isPI, $isResp, $userLevel);
	
	$selectedDepartmentsArray = null;
	if(isset($_GET['departments'])){
		$selectedDepartmentsArray = json_decode($_GET['departments'], true);
	}

	$htmlDisplayArray = array();
	$htmlDisplayArray[] = array('name' => "Department", 'select' => 'department_name', 'where' => 'department_id', 'function' => 'htmlFilterLink', 'args' => array('department_id', 'department_name', sizeof($htmlDisplayArray)));
	$htmlDisplayArray[] = array('name' => "Username", 'select' => 'fullname', 'where' => 'user_id', 'function' => 'htmlFilterLink', 'args' => array('user_id', 'fullname', sizeof($htmlDisplayArray)));
	$htmlDisplayArray[] = array('name' => "Resource", 'select' => 'resource_name', 'where' => 'resource_id', 'function' => 'htmlFilterLink', 'args' => array('resource_id', 'resource_name', sizeof($htmlDisplayArray)));
	$htmlDisplayArray[] = array('name' => "Project", 'select' => 'project_name', 'where' => 'project_id', 'function' => 'htmlFilterLink', 'args' => array('project_id', 'project_name', sizeof($htmlDisplayArray)));
	$htmlDisplayArray[] = array('name' => "Entry date", 'select' => 'entry_datetime');
	$htmlDisplayArray[] = array('name' => "Duration", 'select' => 'duration', 'function' => 'htmlDuration', 'args' => 'duration');
	$htmlDisplayArray[] = array('name' => "Price/unit", 'select' => 'price_value');
	$htmlDisplayArray[] = array('name' => "Subtotal", 'select' => 'subtotal');
	$htmlDisplayArray[] = array('name' => "Discount", 'select' => 'discount');
	$htmlDisplayArray[] = array('name' => "Total", 'select' => 'total');
	
	if(isset($_POST['action']) && $_POST['action'] == 'generateJson'){
		$isResp = isResp($_SESSION['user_id']);
		$isAdmin = isAdmin($_SESSION['user_id']);
		$isPI = isPI($_SESSION['user_id']);
		
		$userLevels = getUserLevels($isAdmin, $isPI, $isResp, $userLevel);
		
		$isAdmin = true;
		$userCheck = $resourceCheck = $entryCheck = $projectCheck = true;

		$userLevel = "admin";
		echo json_encode(generateJson());
		exit;
	}
	
	htmlEncoding();
	importJs();
	
	// echo "<link href='css/reset-min.css' rel='stylesheet' type='text/css' />";
	// echo "<link href='css/demo_page.css' rel='stylesheet' type='text/css' />";
	// echo "<link href='css/demo_table_jui.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/jquery.dataTables_themeroller.css' rel='stylesheet' type='text/css' />";
	// echo "<link href='css/jquery-ui-1.10.0.custom.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/jquery-ui-1.10.0.custom.css' rel='stylesheet' type='text/css' />";
	// echo "<link href='css/demo_table.css' rel='stylesheet' type='text/css' />";
	// echo "<script type='text/javascript' src='js/jquery-1.8.2.min.js'></script>";
	echo "<link href='css/jquery.datepick.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/hourUsage.css' rel='stylesheet' type='text/css' />";
	// echo "<link href='css/demo_table.css' rel='stylesheet' type='text/css' />";
	// echo "<link href='css/jquery-ui-1.9.1.custom.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/base.css' rel='stylesheet' type='text/css' />";
	echo "<script type='text/javascript' src='js/jquery.datepick.js'></script>";
	echo "<script type='text/javascript' src='js/hoursUsage.js'></script>";
	// echo "<script type='text/javascript' src='js/jquery-ui-1.9.1.custom.js'></script>";
	// echo "<script type='text/javascript' src='js/jquery-ui-1.10.1.custom.js'></script>";
	echo "<script type='text/javascript' src='js/jquery-ui.js'></script>";
	echo "<script type='text/javascript' src='js/jquery.dataTables.min.js'></script>";
	
	echo "<a name='top'></a>";
	
	echo "<br>";
	echo "<h1>Resource Usage</h1>";
	echo "<br>";
	
	
	echo "<table id='userLevelTable' style='margin:auto;width:450px;'>";
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
				<label style='color:white;float:left;'>
					<input type='radio' name='privilegesRadio' id='adminRadio' ".$checkedPrivilege."/>
					Administrator
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
				<label style='color:white;float:left;'>
					<input type='radio' name='privilegesRadio' id='piRadio' ".$checkedPrivilege."/>
					Department Manager
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
				<label style='color:white;float:left;'>
					<input type='radio' name='privilegesRadio' id='respRadio' ".$checkedPrivilege."/>
					Resource Manager
				</label>
			";
		}
		
		// if($numberOfPrivileges > 1){
			echo "<tr style='text-align:center;color:#F7C439;'>";
				echo "<th title='Pick the user level you wish to view the information as'>";
					echo "<label>";
						echo "User level";
					echo "</label>";
				echo "</th>";
				
					
				echo "<th>";
					echo "<label>";
						echo "Period of time";
					echo "</label>";
				echo "</th>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td rowspan='2' style='text-align:center;color:#F7C439;' title='Pick the user level you wish to view the information as'>";
					echo $privilegeHtml;
				echo "</td>";

				echo "<td style='text-align:right;'>";
					echo "<a>From date:</a>";
					echo "&nbsp";
					echo "<input type='text' id='beginDateText' style='text-align:center;' value='".$beginDate."'/>";
				echo "</td>";
			echo "</tr>";
			
			// echo "<tr>";
				// echo "<td colspan='2'>";
					// echo "<br>";
				// echo "</td>";
			// echo "</tr>";
		// }
		// *********************************************
		echo "<tr>";
			echo "<td style='text-align:right;'>";
				echo "<a>To date:</a>";
				echo "&nbsp";
				echo "<input type='text' id='endDateText' style='text-align:center;' value='".$endDate."'/>";
			echo "</td>";
		echo "</tr>";
	echo "</table>";

	echo "<br>";
	echo "<br>";
	
	$backLink = "
		<div style='float:left;'>
			<a class='link' name='back' href='../datumo/'>Back to Admin Area</a>
		</div>
	";
	
	// Table where the results will appear
	echo "<div id='resultsDiv' style='margin:auto;width:1150;text-align:center;'>";
		echo $backLink;
		echo "<br>";
		
		// echo "<div style='margin-top: 10px;' >";
		// echo "</div>";
		
		echo "<div style='width: 100%;margin-top: 10px;'>";
			echo "<div style='float:left;'>";
				// echo "<label id='filterText' style='margin-left: 10px;'>";
				echo "<label id='filterText'>";
				echo "</label>";
				
				// echo "<a class='link' onclick='resetFilter();' style='text-decoration:underline; margin-left: 10px;'>X Reset filter</a>";
			echo "</div>";
			
			echo "<div style='float:right;'>";
				echo "<input type='text' id='searchField' style='width:300px;' onkeypress='return synchInfo(event);'/>";
				echo "&nbsp";
				echo "<input type='button' id='searchButton' value='Generate Report' onclick='oTable.fnReloadAjax();'/>";
			echo "</div>";
		
		echo "</div>";
		
		echo "<div style='clear:both;'></div>";
		
		// to be removed, usefull for now to make sure the number of tds in the footer is the same as the header
		$footer_tds = "";
		echo "<table id='datatable' style='color: black;width:100%';>";
			echo "<thead>";
			foreach($htmlDisplayArray as $data){
				echo "<th>";
					echo $data['name'];
				echo "</th>";
				
				$footer_tds .= "<td></td>";
			}
			echo "</thead>";
			
			echo "<tfoot style='background: grey;border: 1px solid white;'>";
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
		
		// echo "<br>";
		echo $backLink;
		echo "<a name='bottom'></a>";
		
		echo "<br>";
		echo "<br>";
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
		global $htmlDisplayArray;
		
		$beginDate = $_POST['beginDate'];
		$endDate = $_POST['endDate'];
		
		$order_by = $_POST['iSortCol_0'];
		$order_by_direction = $_POST['sSortDir_0'];
		
		$lowerLimit = $_POST['iDisplayStart'];
		$upperLimit = $_POST['iDisplayLength'];
		
		$iTotalDisplayRecords = 0;
		$iTotalRecords = 0;
		
		$date_sql = "";
		if(
			empty($beginDate) && !empty($endDate)
			|| !empty($beginDate) && empty($endDate)
		){
			throw new Exception("Date fields need to be both empty or both filled");
		}
		elseif(!empty($beginDate) && !empty($endDate)){
			// this way php knows its the european date format
			$formatedBeginDate = str_replace("/", "-", $beginDate);
			$formatedEndDate = str_replace("/", "-", $endDate);
			if(!strtotime($formatedBeginDate) || !strtotime($formatedEndDate)){
				throw new Exception("Not a valid date");
			}
			elseif(strtotime($formatedBeginDate) > strtotime($formatedEndDate)){
				throw new Exception("\'From date\' is after \'To date\'");
			}
			
			$formatedBeginDate = dbHelp::convertToDate($formatedBeginDate);
			$formatedEndDate = dbHelp::convertToDate($formatedEndDate);
			$date_sql = "and entry_datetime between '".$formatedBeginDate."' and '".$formatedEndDate."'";
		}
		
		
		$aaData = array();
		$output = array(
			"sEcho" => intval($_POST['sEcho']),
		);
		
		$order_by = intval($_POST['iSortCol_0']);
		$order_by_direction = $_POST['sSortDir_0'];
		$direction = array('asc' => 'asc', 'desc' => 'desc');
		if($htmlDisplayArray[$order_by]['select'] && $direction[$order_by_direction]){
			$order_by_sql = "order by ".$htmlDisplayArray[$order_by]['select']." ".$direction[$order_by_direction];
		}
		
		$limit = "";
		if($upperLimit != -1){
			$limit = "limit ".intval($lowerLimit).", ".intval($upperLimit);
		}
		
		$sql_array = array();
		$sql_array_length = 0;
		if(isset($_POST['searchField']) && $_POST['searchField'] != ''){
			$sql_array[] = $_POST['searchField'];
			$sql_array_length++;
			// $search_sql = "and department_name like '%:0%' || @fullname like '%:0%' || resource_name like '%:0%' || project_name like '%:0%'";
			$search_sql = "
				and (
				lower(department_name) like lower(concat('%',:".($sql_array_length - 1).",'%')) 
				|| lower(concat(user_firstname, ' ', user_lastname)) like lower(concat('%',:".($sql_array_length - 1).",'%'))
				|| lower(resource_name) like lower(concat('%',:".($sql_array_length - 1).",'%'))
				|| lower(ifnull(project_name, 'No project')) like lower(concat('%',:".($sql_array_length - 1).",'%'))
			)";
			
		}
		
		$filters = json_decode($_POST['filters']);
		$filter_sql = "";
		if($filters){
			foreach($filters as $key => $filter){
				if($filter === null){
					$filter_sql .= " and ".$htmlDisplayArray[$key]['where']." is null";
				}
				else{
					$sql_array[] = $filter;
					$sql_array_length++;
					$filter_sql .= " and ".$htmlDisplayArray[$key]['where']." = :".($sql_array_length - 1);
				}
			}
		}

		
		$sql = "
			select
				SQL_CALC_FOUND_ROWS
				department_id,
				department_name,
				user_id,
				@fullname := concat(user_firstname, ' ', user_lastname) as fullname,
				resource_id,
				resource_name,
				project_id,
				ifnull(project_name, 'No project') as project_name,
				entry_datetime,
				@duration := entry_slots * resource_resolution as duration,
				@pricevalue := ifnull(price_value, 0) as price_value,
				@discount := entry_discount(entry_datetime, entry_slots, entry_resource, user_dep, @pricevalue, resource_resolution) as discount,
				@subtotal := @duration * @pricevalue as subtotal,
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
				".$date_sql."
				".$search_sql."
				".$filter_sql."
			".$order_by_sql."
			".$limit."
		";
		$prep = dbHelp::query($sql, $sql_array);
		
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
					$value = $data['function']($row, $data['args']);
				}
				else{
					$value = $row[$data['select']];
				}
				$line[] = $value;
			}
			$aaData[] = $line;
		}
		$output['iTotalRecords'] = $iTotalDisplayRecords;
		$output['iTotalDisplayRecords'] = $iTotalDisplayRecords;
		$output['aaData'] = $aaData;
		
		return $output;
	}
	

	function htmlDuration($row, $arg){
		$value = $row[$arg];
		$hoursFloored = floor($value / 60);
		$minutes = $value % 60;
		
		$formatedValue = $hoursFloored."h : ".$minutes."m";
		return $formatedValue;
	}
	
	function htmlFilterLink($row, $argsArray){ // argsArray: id, value, column index
		$id = $row[$argsArray[0]];
		if($id === null){
			$id = 'null';
		}
		return "<a class='datatableLink' onclick='filter(".$id.", this.text,".$argsArray[2].");'>".$row[$argsArray[1]]."</a>";
	}
?>