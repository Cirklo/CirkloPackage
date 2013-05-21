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
	$htmlDisplayArray[] = array('name' => "Entry duraton", 'select' => 'entryduration', 'function' => 'htmlUnits', 'args' => array('entryduration'));
	$htmlDisplayArray[] = array('name' => "Real duraton", 'select' => 'realduration', 'function' => 'htmlUnits', 'args' => array('realduration'));

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
	echo "<script type='text/javascript' src='js/realUsageComparison.js'></script>";
	echo "<script type='text/javascript' src='js/jquery-ui.js'></script>";
	echo "<script type='text/javascript' src='js/jquery.dataTables.min.js'></script>";
	
	echo "<a name='top'></a>";
	
	echo "<br>";
	echo "<h1>Usage comparison</h1>";
	
	$backLink = "
		<div style='float:left;'>
			<a class='link' name='back' href='../datumo/'>Back to Admin Area</a>
		</div>
	";
	
	// Table where the results will appear
	echo "<div id='resultsDiv' style='margin:auto;width:1280;text-align:center;'>";
		echo "<div style='width: 100%;margin-top: 10px;'>";
			echo "<div style='float:left;margin-top: 80px;text-align:left;'>";
				echo $backLink;

				// echo "<br>";
				// echo "<br>";
				// echo "<label>";
				// echo "Show real usage where possible";
				// echo "<input type='checkbox' id='realTimeCheck' onchange='refresh_table();'/>";
				// echo "</label>";
				// echo "<br>";
				
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
						
					// }
					// *********************************************
					echo "<tr>";
						echo "<td style='text-align:right;'>";
							echo "<a>To date:</a>";
							echo "&nbsp";
							echo "<input type='text' id='endDateText' style='text-align:center;' value='".$endDate."'/>";
						echo "</td>";
					echo "</tr>";
					
					echo "<tr>";
						echo "<td colspan='2' style='text-align:right;'>";
							echo "<input class='searchMessageFont' type='text' id='searchField' style='width:325px;' onkeypress='return synchInfo(event);' onfocus='clearField();' onblur='putDefaultMessage();'/>";
							echo "&nbsp";
							echo "<input type='button' id='searchButton' value='Go' onclick='refresh_table();'/>";
						echo "</td>";
					echo "</tr>";
				echo "</table>";
			echo "</div>";
		echo "</div>";
		
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
		global $htmlDisplayArray, $userLevelSql;
		
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
				)
			";
			
		}
		
		$filters = json_decode($_GET['filters']);
		$filter_sql = "";
		if($filters){
			foreach($filters as $key => $filter){
				if($filter === null){
					$filter_sql .= " and ".$htmlDisplayArray[$key]['where']." is null";
				}
				else{
					$sql_array[] = $filter;
					$position++;
					$filter_sql .= " and ".$htmlDisplayArray[$key]['where']." = :".$position;
				}
			}
		}


		$sql = "
			select 
				department_id,
				department_name,
				user, 
				concat(user_firstname, ' ', user_lastname) as fullname,
				resource, 
				resource_name,
				entryduration, 
				realduration 
			from 
				(select user, resource, sum(TIMESTAMPDIFF(MINUTE, loginstamp, logoutstamp)) as realduration
			 	from pginalogview 
			 	group by user, resource) as realData 
			join 
				(select entry_user, entry_resource, sum(entry_slots) * resource_resolution as entryduration 
				from entry join resource on resource_id = entry_resource 
				where entry_status in (1,2) 
				group by entry_user, entry_resource) as entryData 
			on (entry_user = user and entry_resource = resource)
			join resource on resource_id = resource
			join production.user on user_id = user
			join department on department_id = user_dep
			where realduration > 0 and realduration is not null
				".$search_sql."
				".$filter_sql."
				".$date_sql."
				".$userLevelSql."
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
		// $hoursFloored = floor($value / 60);
		// $minutes = $value % 60;
		
		// $formatedValue = $hoursFloored."h : ".$minutes."m";
		$value = $row[$args[0]];
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