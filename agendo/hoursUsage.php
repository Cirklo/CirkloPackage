<?php
	require_once("commonCode.php");

	if(!isset($_SESSION['user_id'])){
		throw new Exception('You need to be logged in');
	}

	$isAdmin = isAdmin($_SESSION['user_id']);
	$isPI = isPI($_SESSION['user_id']);
	$isResp = isResp($_SESSION['user_id']);
	
	$userLevel = $_GET['userLevel'];
	if(!isset($userLevel) || $userLevel == ''){
		$userLevel = 'user';
	}
	$userLevels = getUserLevels($isAdmin, $isPI, $isResp, $userLevel);
	$userLevelArray = array(
		'admin' => ""
		,'user' => 'and user_id = '.intval($_SESSION['user_id'])
	);
	if($isPI !== false){
		$userLevelArray['pi'] = "and department_id in (".implode(',', $isPI).")";
	}
	
	if($isResp !== false){
		$userLevelArray['resp'] = "and resource_id in (".implode(',', $isResp).")";
	}
	
	$userLevelSql = $userLevelArray[$userLevel];

	$htmlDisplayArray = array();
	$htmlDisplayArray[] = array('name' => "Department", 'select' => 'department_name', 'where' => 'department_id', 'function' => 'htmlFilterLink', 'args' => array('department_id', 'department_name', sizeof($htmlDisplayArray)));
	$htmlDisplayArray[] = array('name' => "Username", 'select' => 'fullname', 'where' => 'user_id', 'function' => 'htmlFilterLink', 'args' => array('user_id', 'fullname', sizeof($htmlDisplayArray)));
	$htmlDisplayArray[] = array('name' => "Resource", 'select' => 'resource_name', 'where' => 'resource_id', 'function' => 'htmlFilterLink', 'args' => array('resource_id', 'resource_name', sizeof($htmlDisplayArray)));
	$htmlDisplayArray[] = array('name' => "Project", 'select' => 'project_name', 'where' => 'project_id', 'function' => 'htmlFilterLink', 'args' => array('project_id', 'project_name', sizeof($htmlDisplayArray)));
	// $htmlDisplayArray[] = array('name' => "Status", 'select' => 'entry_status', 'where' => 'entry_status', 'function' => 'htmlFilterLink', 'args' => array('entry_status', 'entrystatus', sizeof($htmlDisplayArray)));
	$htmlDisplayArray[] = array('name' => "Status", 'select' => 'entry_status', 'orderby' => 'entrystatus', 'where' => 'entry_status', 'function' => 'htmlFilterLink', 'args' => array('entry_status', 'entrystatus', sizeof($htmlDisplayArray)));
	$htmlDisplayArray[] = array('name' => "Entry date", 'select' => 'datetime');
	$htmlDisplayArray[] = array('name' => "Unit", 'select' => 'units', 'function' => 'htmlUnits', 'args' => array('resource_status', 'units'));
	$htmlDisplayArray[] = array('name' => "Type", 'select' => 'resource_status', 'function' => 'htmlUnitType', 'args' => 'resource_status');
	// $htmlDisplayArray[] = array('name' => "Price", 'select' => 'price_value', 'function' => 'totals', 'args' => 'price_value');
	$htmlDisplayArray[] = array('name' => "Price", 'select' => 'price_value');
	$htmlDisplayArray[] = array('name' => "Sub", 'select' => 'subtotal', 'function' => 'totals', 'args' => 'subtotal');
	$htmlDisplayArray[] = array('name' => "Disc", 'select' => 'discount', 'function' => 'totals', 'args' => 'discount');
	$htmlDisplayArray[] = array('name' => "Total", 'select' => 'total', 'function' => 'totals', 'args' => 'total');
	
	$realTimeCheck = $_GET['realTimeCheck'];
	if(isset($_GET['action']) && ($_GET['action'] == 'generateJson' || $_GET['action'] == 'downloadCsv')){
		echo json_encode(generateJson());
		exit;
	}
	
	htmlEncoding();
	importJs();

	echo "<link href='css/jquery.dataTables_themeroller.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/jquery-ui-1.10.0.custom.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/jquery.datepick.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/hourUsage.css' rel='stylesheet' type='text/css' />";
	echo "<link href='css/base.css' rel='stylesheet' type='text/css' />";
	echo "<script type='text/javascript' src='js/jquery.datepick.js'></script>";
	echo "<script type='text/javascript' src='js/hoursUsage.js'></script>";
	echo "<script type='text/javascript' src='js/jquery-ui.js'></script>";
	echo "<script type='text/javascript' src='js/jquery.dataTables.min.js'></script>";
	
	echo "<a name='top'></a>";
	
	echo "<br>";
	echo "<h1>Resource Usage</h1>";
	
	$backLink = "
		<div style='float:left;'>
			<a class='link' name='back' href='../datumo/'>Back to Admin Area</a>
		</div>
	";
	
	$sql = "select count(computer_name) from computer";
	$prep = dbHelp::query($sql, array(dbHelp::getSchemaName()));
	$row = dbHelp::fetchRowByIndex($prep);
	$computerNumber = $row[0];
	$dontShowRealTime = false;

	// hide real usage if the needed tables dont exist
	if($computerNumber == 0){
		$realTimeCheck = null;
		$dontShowRealTime = true;
	}

	// Table where the results will appear
	echo "<div id='resultsDiv' style='margin:auto;width:1280;text-align:center;'>";
		echo "<div style='width: 100%;margin-top: 10px;'>";
			echo "<div style='float:left;margin-top: ".(($dontShowRealTime) ? 80 : 25)."px;text-align:left;'>";
				echo $backLink;

				if(!$dontShowRealTime){
					echo "<br>";
					echo "<br>";
					echo "<label>";
					echo "Show real usage where possible";
					echo "<input type='checkbox' id='realTimeCheck' onchange='refresh_table();'/>";
					echo "</label>";
					
					echo "<br>";
				}
				echo "<br>";
				echo "<label id='filterText'>";
				echo "</label>";
			echo "</div>";
			
			echo "<div style='float:right;margin-bottom: 0px;padding:0px;'>";
				echo "<table id='userLevelTable' style='margin:auto;width:380px;'>";
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
								<input type='radio' name='privilegesRadio' id='adminRadio' value='admin' ".$checkedPrivilege."/>
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
								<input type='radio' name='privilegesRadio' id='piRadio' value='pi' ".$checkedPrivilege."/>
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
								<input type='radio' name='privilegesRadio' id='respRadio' value='resp' ".$checkedPrivilege."/>
								Resource Manager
							</label>
						";
					}
					
					if($privilegeHtml == ""){
						$privilegeHtml = "<label>Regular users can only see their own entries</label>";
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
					
					// echo "<tr>";
						// echo "<td colspan='2'>";
							// echo "<br>";
						// echo "</td>";
					// echo "</tr>";
					
					echo "<tr>";
						echo "<td colspan='2' style='text-align:right;'>";
							echo "<input class='searchMessageFont' type='text' id='searchField' style='width:325px;' onkeypress='return synchInfo(event);' onfocus='clearField();' onblur='putDefaultMessage();'/>";
							echo "&nbsp";
							echo "<input type='button' id='searchButton' value='Go' onclick='refresh_table();'/>";
						echo "</td>";
					echo "</tr>";
				echo "</table>";
			// echo "<br>";	
			
			echo "</div>";
		
		echo "</div>";
		
		// echo "<div style='clear:both;'></div>";
		
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
			echo "<tfoot style='background:#F7C439;font-weight:bold;border:1px solid white;'>";
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
		
		echo "<div style='float:right;'>";
			echo "<input type='button' value='Download' title='Downloads the information on screen to CSV format' onclick='download_csv();' />";
		echo "</div>";
		
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
	
	
	function generateJson(){
		global $htmlDisplayArray, $userLevelSql, $realTimeCheck;
		
		$beginDate = $_GET['beginDate'];
		$endDate = $_GET['endDate'];
		
		$iTotalDisplayRecords = 0;
		$iTotalRecords = 0;
		
		$date_sql = "";
		$real_date_sql = "";
		$sql_array = array();
		$position = -1;
		
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
			$real_date_sql = "and loginstamp between '".$formatedBeginDate."' and '".$formatedEndDate."'";
		}
		
		
		$order_by = $_GET['iSortCol_0'];
		$order_by_direction = $_GET['sSortDir_0'];
		$direction = array('asc' => 'asc', 'desc' => 'desc'); // cant prepare this values in PDO, it will turn out 'asc' instead of asc
		if($htmlDisplayArray[$order_by]['select'] && $direction[$order_by_direction]){
			$orderByValue = $htmlDisplayArray[$order_by]['orderby'];
			if(isset($orderByValue)){
				$order_by_sql = "order by ".$orderByValue." ".$direction[$order_by_direction];
			}
			else{
				$order_by_sql = "order by ".$htmlDisplayArray[$order_by]['select']." ".$direction[$order_by_direction];
			}
		}

		$lowerLimit = $_GET['iDisplayStart'];
		$upperLimit = $_GET['iDisplayLength'];
		$limit = "";
		if($upperLimit != -1){
			$limit = "limit ".intval($lowerLimit).", ".intval($upperLimit); // cant prepare this values in PDO, it will turn out '10' instead of 10
		}
		
		if(isset($_GET['searchField']) && $_GET['searchField'] != ''){
			$sql_array[] = $_GET['searchField'];
			$position++;
			$search_sql = "
				and (
					lower(department_name) like lower(concat('%',:".$position.",'%'))
					|| lower(concat(user_firstname, ' ', user_lastname)) like lower(concat('%',:".$position.",'%'))
					|| lower(resource_name) like lower(concat('%',:".$position.",'%'))
					|| lower(ifnull(project_name, 'No project')) like lower(concat('%',:".$position.",'%'))
					|| lower(if(entry_status = 1, 'Confirmed', if(entry_status = 'real', 'Real usage', 'Unconfirmed'))) like lower(concat('%',:".$position.",'%'))
				)
			";
			
		}
		
		$filters = json_decode($_GET['filters']);
		$filter_sql = "";
		if($filters){
			foreach($filters as $key => $filter){
				if(!isset($filter) || $filter == 'null'){
					$filter_sql .= " and ".$htmlDisplayArray[$key]['where']." is null";
				}
				else{
					$sql_array[] = $filter;
					$position++;
					$filter_sql .= " and ".$htmlDisplayArray[$key]['where']." = :".$position;
				}
			}
		}

		// $realTimeFilterJoin = "";
		$realTimeFilterWhere = "";
		$unionWithRealTimeSql = "";
		if(isset($realTimeCheck) && $realTimeCheck == 'checked'){
			// $realTimeFilterJoin = "
			// 	left join machine on machine_resource = resource_id
			// ";
			// $realTimeFilterJoin = "
			// 	left join computer on computer_id = resource_computer
			// ";

			// $realTimeFilterWhere = "
			// 	and machine_resource is null
			// ";
			$realTimeFilterWhere = "
				and resource_computer is null
			";

			$unionWithRealTimeSql = "
				UNION
					(select
						department_id,
						department_name,
						user_id,
						@fullname := concat(user_firstname, ' ', user_lastname) as fullname,
						resource_id,
						resource_name,
						resource_status,
						project_id,
						ifnull(project_name, 'No project') as project_name,
						loginstamp as datetime,
						entry_status,
						'Real usage' as entrystatus,
						@pricevalue := ifnull(price_value, 0) as price_value,
						@units := TIMESTAMPDIFF(MINUTE,loginstamp,logoutstamp) as units,
						@subtotal := @units * @pricevalue as subtotal,
						@discount := entry_discount(loginstamp, @units, resource, project_discount, @subtotal, @pricevalue) as discount,
						@subtotal - @discount as total
					from 
						pginalogview join ".dbHelp::getSchemaName().".user on user_id = user
						join department on department_id = user_dep
						join institute on institute_id = department_inst
						join resource on resource_id = resource
						left join price on (price_resource = resource_id and price_type = institute_pricetype)
						left join project on project_id = department_default
					where
						resource_status in (1,2,3,4,5)
						".$search_sql."
						".$filter_sql."
						".$real_date_sql."
						".$userLevelSql."
					)
			";
		}

		$sql = "
			select
				SQL_CALC_FOUND_ROWS
				*
			from
				(
					(select
						department_id,
						department_name,
						user_id,
						fullname,
						resource_id,
						resource_name,
						resource_status,
						project_id,
						project_name,
						entry_datetime as datetime,
						entry_status,
						entrystatus,
						price_value,
						units,
						@subtotal := units * price_value * 60 as subtotal,
						@discount := sequencing_discount(entry_resource, entry_datetime, project_discount, @subtotal) as discount,
						@subtotal - @discount as total
					from 
						(select
							department_id,
							department_name,
							user_id,
							concat(user_firstname, ' ', user_lastname) as fullname,
							resource_id,
							resource_name,
							resource_status,
							project_id,
							project_discount,
							ifnull(project_name, 'No project') as project_name,
							entry_datetime,
							entry_status,
							entry_resource,
							if(entry_status = 1, 'Confirmed', 'Unconfirmed') as entrystatus,
							ifnull(price_value, 0) as price_value,
							count(item_id) as units
						from 
							entry join item_assoc on item_assoc_entry = entry_id
							join resource on resource_id = entry_resource
							join item on item_id = item_assoc_item
							join user on user_id = item_user
							join department on department_id = user_dep
							join institute on institute_id = department_inst
							left join project on project_id = item_project
							left join price on (price_resource = resource_id and price_type = institute_pricetype)
						where
							item_state = 3
							and entry_status in (1,2)
							and resource_status = 6
							".$search_sql."
							".$filter_sql."
							".$date_sql."
							".$userLevelSql."
						group by
							entry_datetime, user_id
						) as AuxSelect
					)
				UNION
					(select
						department_id,
						department_name,
						user_id,
						@fullname := concat(user_firstname, ' ', user_lastname) as fullname,
						resource_id,
						resource_name,
						resource_status,
						project_id,
						ifnull(project_name, 'No project') as project_name,
						entry_datetime as datetime,
						entry_status,
						if(entry_status = 1, 'Confirmed', 'Unconfirmed') as entrystatus,
						@pricevalue := ifnull(price_value, 0) as price_value,
						@units := entry_slots * resource_resolution as units,
						@subtotal := @units * @pricevalue as subtotal,
						@discount := entry_discount(entry_datetime, @units, entry_resource, project_discount, @subtotal, @pricevalue) as discount,
						@subtotal - @discount as total
					from 
						".dbHelp::getSchemaName().".user join entry on user_id = entry_user
						join department on department_id = user_dep
						join institute on institute_id = department_inst
						join resource on resource_id = entry_resource
						left join price on (price_resource = entry_resource and price_type = institute_pricetype)
						left join project on project_id = entry_project
						".
						//$realTimeFilterJoin.
						"
					where
						entry_status in (1,2)
						and resource_status in (1,3,4,5)
						".$search_sql."
						".$filter_sql."
						".$date_sql."
						".$userLevelSql."
						".$realTimeFilterWhere."
					)
					".$unionWithRealTimeSql."
				) as allData
			".$order_by_sql."
			".$limit."
		";

		$prep = dbHelp::query($sql, $sql_array);

		// csv generation
		if($_GET['action'] == 'downloadCsv'){
			download_csv($prep);
			exit;
		}
		
		// returns a number indicating how many rows the first SELECT would have returned had it been written without the LIMIT clause
		$sqlTotalRows = "select FOUND_ROWS()";
		$prepTR = dbHelp::query($sqlTotalRows);
		$resTR = dbHelp::fetchRowByIndex($prepTR);
		$iTotalDisplayRecords = $resTR[0];
		
		// html display
		$aaData = array();
		$output = array(
			"sEcho" => intval($_GET['sEcho']),
		);
		
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
	

	function htmlUnits($row, $args){
		if($row[$args[0]] == 6){
			return $row[$args[1]];
		}
		
		// $hoursFloored = floor($value / 60);
		// $minutes = $value % 60;
		
		// $formatedValue = $hoursFloored."h : ".$minutes."m";
		$value = $row[$args[1]];
		// return round($value / 60, 2);
		return roundFunction($value / 60);
	}
	
	function htmlUnitType($row, $arg){
		if($row[$arg] == 6){
			return "Items";
		}
		
		return "Hours";
	}
	
	function totals($row, $arg){
		return roundFunction($row[$arg] / 60);
	}
	
	function roundFunction($value){
		return round($value, 2);
	}
	
	function htmlFilterLink($row, $argsArray){ // argsArray: id, value, column index
		$id = $row[$argsArray[0]];
		if($id === null){
			$id = 'null';
		}

		return auxGenerateLink($id, $argsArray[2], $row[$argsArray[1]]);
	}
	
	function auxGenerateLink($id, $columnIndex, $value){
		return "<a class='datatableLink' onclick='filter(\"".$id."\", this.text,".$columnIndex.");'>".$value."</a>";
	}
	
	function download_csv(&$prep){
		global $htmlDisplayArray;
		
		$lineSeparator = "\n";
		$columnSeparator = "\t";
		$valueWrapper = "\"";
	
		unset($htmlDisplayArray[0]['function']);
		unset($htmlDisplayArray[0]['args']);
		
		unset($htmlDisplayArray[1]['function']);
		unset($htmlDisplayArray[1]['args']);
		
		unset($htmlDisplayArray[2]['function']);
		unset($htmlDisplayArray[2]['args']);
		
		unset($htmlDisplayArray[3]['function']);
		unset($htmlDisplayArray[3]['args']);
		
		unset($htmlDisplayArray[4]['function']);
		unset($htmlDisplayArray[4]['args']);
		
		foreach($htmlDisplayArray as $display){
			echo $valueWrapper.$display['name'].$valueWrapper.$columnSeparator;
		}
		echo $lineSeparator;
		
		header('Content-Type: application/force-download');
		header('Content-disposition: attachment; filename=report.csv');

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
				echo $valueWrapper.$value.$valueWrapper.$columnSeparator;
			}
			echo $lineSeparator;
		}
	}
?>