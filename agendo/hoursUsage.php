<?php
	require_once("commonCode.php");
	require_once("hoursUsageAux.php");
	
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
		"Department" => array('order_by' => array('department_name'))
		, "Username" => array('order_by' => array('user_firstname', 'user_lastname'))
		, "Resource" => array('order_by' => array('resource_name'))
		, "Entry date" => array('order_by' => array('entry_datetime'))
		, "Duration" => array('function' => 'usageHtml')
		, "Price per hour" => array('order_by' => array('price_value'))
		, "Subtotal" => array('function' => 'costFormat')
		, "Discount" => array('function' => 'costFormat')
		, "Project" => array('order_by' => array('project_name'))
		, "Total" => array('function' => 'costFormat')
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
	echo "<script type='text/javascript' src='js/hoursUsage.js'></script>";
	// echo "<script type='text/javascript' src='js/jquery-ui-1.9.1.custom.js'></script>";
	echo "<script type='text/javascript' src='js/jquery-ui.js'></script>";
	echo "<script type='text/javascript' src='js/jquery.dataTables.min.js'></script>";
	
	$backLink = "
		<div style='margin:auto;width:200px;text-align:center;'>
			<a class='link' name='back' href='../datumo/'>Back to Admin Area</a>
		</div>
	";

	if(isset($_GET['action']) && $_GET['action'] != 'generateReport'){
		switch($_GET['action']){
			// Opens a file dialog to download a csv with all the selected info (select all and select none will generate the same csv file)
			case "downloadFile":
				header('Content-Type: application/force-download');
				header('Content-disposition: attachment; filename=report.xls');
				echo generateCsv($selectedDepartmentsArray);
			break;
			
			// Emails the department managers 
			case "emailDepartments":
				emailDepartments();
				$json->success = true;
				$json->message = "Report sent";
				echo json_encode($json);
			break;
			
		}
		exit;
	}

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
			foreach($htmlDisplayArray as $key=>$value){
				$number_of_tds++;
				echo "<td>";
					echo $key;
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
	

	function emailDepartments(){
		global $selectedDepartmentsArray;
		
		$defaultMailTitle = "Usage report";
		$message = "The report was sent by attachment as html.";
		$mansAndDeps = getManagersAndTheirDepartmentsFromDepartmentsList($selectedDepartmentsArray);
		if(!$mansAndDeps){
			throw new Exception("Could not find any managers associated to the selected departaments");
		}

		foreach($mansAndDeps as $man => $deps){
			// Get manager email here
			$sql = "select user_email from ".dbHelp::getSchemaName().".user where user_id = :0";
			$prepMail = dbHelp::query($sql, array($man));
			$row = dbHelp::fetchRowByIndex($prepMail);
			$mail = getMailObject($defaultMailTitle, $row[0], $message, "[AGENDO]", "support@cirklo.org");
			
			// Adds the content of the xls file to the email as an attachment
			// $mail->AddStringAttachment(generateCsv(), "report.xls");
			
			// Adds the content of the html file to the email as an attachment
			$mail->isHtml(true);
			$html = "<html>";
				$html .= "<body bgcolor='#1e4F54'>";
					$html .= "<div>";
						$html .= generateHtml($deps, false);
					$html .= "</div>";
				$html .= "</html>";
			$html .= "</body>";
			$mail->AddStringAttachment($html, "report.html");
			
			// Sends the email
			sendMailObject($mail);
		}
	}
	
	// Big name, i know, but self explanatory and no comment is needed to describe this function. But my mind wasnt working so i wrote this.
	function getManagersAndTheirDepartmentsFromDepartmentsList($departments){
		$mansAndDeps = array();
		foreach($departments as $department){
			$sql = "select user_id, department_id from ".dbHelp::getSchemaName().".user, department where user_id = department_manager and department_id = :0";
			$prep = dbHelp::query($sql, array($department));
			$row = dbHelp::fetchRowByIndex($prep);
			if(!isset($mansAndDeps[$row[0]])){
				$mansAndDeps[$row[0]] = array();
			}
			$mansAndDeps[$row[0]][] = $row[1];
		}
		
		$testVar = current($mansAndDeps);
		if(!isset($testVar)){
			return false;
		}
		
		return $mansAndDeps;
	}
	
	function fieldLabelFunctionAssoc($notCsv = true){
		global $userCheck, $resourceCheck, $entryCheck, $projectCheck, $argumentsArray, $labelWidthArray;
		
		$regularSelectArray = array();
		$functionSelectArray = array();
		$selectArray = array();
		$orderByArray = array();
		$size = 0;
	
		// static selects
		$selectArray[] = "department_id";
		$selectArray[] = "department_name";
		$selectArray[] = "entry_datetime";
		$selectArray[] = "entry_slots";
		$selectArray[] = "entry_resource";
		$selectArray[] = "price_value";
		$selectArray[] = "project_discount";
		
		// static group by
		$orderByArray[] = "department_name";
	
		// label to field association
		$regularSelectArray["Entry date"] = array("select" => "entry_datetime", 'lineKey' => 'entry_id');
		$size++;

		// extra fields on the select section of the query
		$selectArray[] = "entry_id";
		
		// extra group by on the select section of the query
		$orderByArray[] = "entry_datetime";
		
		// field function association
		$functionSelectArray['formatUserName'] = array("args" => array("user_firstname", "user_lastname"));
		$size++;
		
		// extra fields on the select section of the query
		$selectArray[] = "user_firstname";
		$selectArray[] = "user_lastname";
		$selectArray[] = "entry_user";
		
		// extra group by on the select section of the query
		$orderByArray[] = "user_firstname";
		$orderByArray[] = "user_lastname";
		
		// field function association
		$functionSelectArray['getResource'] = array("args" => array("entry_resource"));
		$size++;
		$functionSelectArray['getPrice'] = array("args" => array("price_value"));
		$size++;
		
		// extra fields on the select section of the query
		$selectArray[] = "price_id";
		$selectArray[] = "entry_resource";

		// label to field association
		$functionSelectArray['projectFormat'] = array("args" => array("project_name"));
		$size++;

		// extra fields on the select section of the query
		$selectArray[] = "project_name";
		$selectArray[] = "project_id";

		// static function field association
		$functionSelectArray['usageCost'] = array("args" => array("entry_resource", "entry_datetime", "entry_slots", "price_value", "project_discount"));
		$size += 2;

		// static regular field association
		$regularSelectArray["Department Id"] = array("select" => "department_id", 'lineKey' => 'department_id');
		$regularSelectArray["Department"] = array("select" => "department_name", 'lineKey' => 'department_id');
		$size++;
	
		$argumentsArray["regular"] = $regularSelectArray;
		$argumentsArray["functions"] = $functionSelectArray;
		
		$argumentsArray["select"] = $selectArray;
		$argumentsArray["orderBy"] = $orderByArray;
		
		$argumentsArray["size"] = $size;

		return $argumentsArray;
	}
	
	function generateResults($selectedDepartmentsArray = null){
		global $results, $iTotalDisplayRecords, $lineKey, $argumentsArray, $userLevels, $beginDate, $endDate, $lowerLimit, $upperLimit, $iTotalRecords, $order_by, $order_by_direction, $htmlDisplayArray;
		$results = array();
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
		
		$isAdmin = $userLevels['isAdmin'];
		$isPI = $userLevels['isPI'];
		$isResp = $userLevels['isResp'];

		$argumentsArray = fieldLabelFunctionAssoc();

		// sql injection danger here
		$direction = array('asc' => 'asc', 'desc' => 'desc');
		if($htmlDisplayArray[$order_by]['order_by'] && $direction[$order_by_direction]){
			$order_by_sql = "order by ".implode(',', $htmlDisplayArray[$order_by]['order_by'])." ".$direction[$order_by_direction];
		}

		
		// Just a precautionary measure to prevent sql injection
		$selectedDepartmentsSql = "";
		$inDepartmentData = dbHelp::inDataFromArray($selectedDepartmentsArray);
		if($inDepartmentData !== false){
			$selectedDepartmentsSql = " and department_id in ".$inDepartmentData['inData'];
		}
		
		$whereIsResp = "";
		$whereDepartment = "";
		if(!$isAdmin){
			if($isPI === false){
				$sql = "select user_dep from ".dbHelp::getSchemaName().".user where user_id = :0";
				$prep = dbHelp::query($sql, array($_SESSION['user_id']));
				$row = dbHelp::fetchRowByIndex($prep);
				$departments = $row[0];
				$whereDepartment = "and user_dep = ".$departments;
				
				// just a regular user
				if($isResp === false){
					$selectedDepartmentsArray = array($departments, $_SESSION['user_id']);
					$selectedDepartmentsSql = "and department_id = :0 and entry_user = :1";
				}
			}
			else{
				$departments = implode(",", $isPI);
				$whereDepartment = "and user_dep in (".$departments.")";
			}
			
			if($isResp !== false){
				$whereIsResp = "or (user_dep not in (".$departments.") and entry_resource in (".implode(",", $isResp).")";
			}
		}

		$staticWhere = "
			entry_status = 1
			".$date_sql."
		";

		$resourcesManagerWhere = "";
		if($whereIsResp != ""){
			$resourcesManagerWhere = $whereIsResp." and ".$staticWhere.")";
		}

		$select = implode(",", $argumentsArray['select']);
		// $orderBy = implode(",", $argumentsArray['orderBy']);
		
		// sql injection danger here
		$limit = "";
		if($upperLimit != -1){
			// $limit = "limit ".$lowerLimit.", ".$upperLimit;
			$limit = "limit ".$lowerLimit.", ".$upperLimit;
		}
		
		$sql = "
			select
				SQL_CALC_FOUND_ROWS
				".$select."
			from 
				".dbHelp::getSchemaName().".user inner join entry on user_id = entry_user
				inner join department on department_id = user_dep
				inner join institute on institute_id = department_inst
				left join price on (price_resource = entry_resource and price_type = institute_pricetype)
				left join project on project_id = entry_project
			where 
				".$staticWhere."
				".$whereDepartment."
				".$resourcesManagerWhere."
				".$selectedDepartmentsSql."
			".$order_by_sql."
			".$limit."
		";

		$prep = dbHelp::query($sql, $selectedDepartmentsArray);
		
		// returns a number indicating how many rows the first SELECT would have returned had it been written without the LIMIT clause
		$sqlTotalRows = "select FOUND_ROWS()";
		$prepTR = dbHelp::query($sqlTotalRows);
		$resTR = dbHelp::fetchRowByIndex($prepTR);
		$iTotalDisplayRecords = $resTR[0];

		while($res = dbHelp::fetchRowByName($prep)){
			$lineValue = array();
			$lineKey = "";
			
			// $argumentsArray["regular"] = $regularSelectArray;
			// $regularSelectArray["Price per hour"] = array("select" => "price_value", "lineKey" => entry_id);
			// regular selects
			foreach($argumentsArray['regular'] as $regularKey => $regularValue){
				$lineValue[$regularKey] = $res[$regularValue['select']];
				$lineKey .= $res[$regularValue['lineKey']];
			}

			// $argumentsArray["functions"] = $functionSelectArray;
			// $functionSelectArray["User name"] = array("function" => "formatUserName", "args" => array("user_firstname", "user_lastname"), "width" => 200);
			// function selects
			foreach($argumentsArray['functions'] as $functionName => $functionData){
				foreach($functionName($res, $functionData['args']) as $functionLabel => $functionResult){
					$lineValue[$functionLabel] = $functionResult['value'];
					
					if(isset($functionResult['lineKey'])){
						$lineKey .= $res[$functionResult['lineKey']];
					}
				}
			}

			$results[$lineKey] = $lineValue;
			$iTotalRecords++;
		}
		

		return $results;
	}
	
	// ********************** select functions ******************************
	function formatUserName(&$res, &$args){
		$value = $res[$args[0]]." ".$res[$args[1]];
		return array('Username' => array('value' => $value, 'lineKey' => 'entry_user'));
	}
	
	// args: 0 => entry_resource, 1 => entry_datetime, 2 => entry_slots, 3 => price_value, 4 => project_discount
	function usageCost(&$res, &$args){
		global $results, $lineKey, $resources, $happyHourArray;
		
		if(!isset($resources[$res[$args[0]]])){
			$resources[$res[$args[0]]] = new Resource($res[$args[0]], $happyHourArray);
		}
		$resource = $resources[$res[$args[0]]];

		$price = 0;
		if(isset($res[$args[3]])){
			$price = $res[$args[3]];
		}
		
		$projDiscount = 0;
		if(isset($res[$args[4]])){
			$projDiscount = $res[$args[4]];
		}

		$usageAndCost = $resource->getCostFromEntry($res[$args[1]], $res[$args[2]], $price, $happyHourArray);
		$usage = 0;
		$discountCost = 0;
		$discountedCost = 0;
		$noDiscountCost = 0;
		if(isset($results[$lineKey])){
			$usage = $results[$lineKey]["Duration"];
			$discountCost = $results[$lineKey]['Discount'];
			$discountedCost = $results[$lineKey]['Total'];
			$noDiscountCost = $results[$lineKey]['Subtotal'];
		}
		$tempUsage = $usageAndCost["noDiscountTime"] + $usageAndCost['discountTime'];
		$usage += $tempUsage;
		$tempDiscountCost = $usageAndCost["discountCost"] * (100 - $projDiscount) * 0.01;

		// needed to round both of these so there wouldnt be results like -0, in cases like both $discountedCost and $noDiscountCost were 3,3333333333333
		$roundBy = 10;
		$discountedCost += round(($usageAndCost["noDiscountCost"] * (100 - $projDiscount) * 0.01) + $tempDiscountCost, $roundBy);
		$noDiscountCost += round($tempUsage * $price / 60, $roundBy);

		$discountCost = $noDiscountCost - $discountedCost;
		
		return array(
			"Duration" => array('value' => $usage)
			, 'Discount' => array('value' => $discountCost)
			, 'Total' => array('value' => $discountedCost)
			, 'Subtotal' => array('value' => $noDiscountCost)
		);
	}
	
	function getResource(&$res, &$args){
		global $resources, $happyHourArray;
		
		if(!isset($resources[$res[$args[0]]])){
			$resources[$res[$args[0]]] = new Resource($res[$args[0]], $happyHourArray);
		}
		$resource = $resources[$res[$args[0]]];
		
		return array(
			'Resource' => array('value' => $resources[$res[$args[0]]]->getName(), 'lineKey' => 'entry_resource')
		);
	}
	
	function getPrice(&$res, &$args){
		$price = 0;
		if(isset($res[$args[0]])){
			$price = $res[$args[0]];
		}
		
		return array(
			'Price per hour' => array('value' => $price, 'lineKey' => 'price_id')
		);
	}
	
	function projectFormat(&$res, &$args){
		$formatedValue = "No project";
		if(isset($res[$args[0]])){
			$formatedValue = $res[$args[0]];
		}
		
		return array(
			'Project' => array('value' => $formatedValue, 'lineKey' => 'project_id')
		);
	}
	
	// function generateHtml(){
	function generateHtml($selectedDepartmentsArray = null, $showSelects = true){
		global $labelWidthArray, $argumentsArray, $results;
		
		generateResults($selectedDepartmentsArray);
		$htmlData = "";
		$displayArray = array(
			"Username" => array('width' => 200)
			, "Resource" => array('width' => 200)
			, "Entry date" => array('width' => 140)
			, "Price per hour" => array('width' => 80)
			, "Duration" => array('function' => 'usageHtml', 'width' => 90, 'value' => 0)
			, "Subtotal" => array('function' => 'costFormat', 'width' => 75, 'value' => 0)
			, "Discount" => array('function' => 'costFormat', 'width' => 75, 'value' => 0)
			, "Project" => array('width' => 80)
			, "Total" => array('function' => 'costFormat', 'width' => 100, 'value' => 0)
		);
		
		// cumulative variables
		$subTotalArray = array();
		$subTotalColspan = 0;
		foreach($displayArray as $key => $argsArray){
			if(isset($argsArray['value'])){
				$subTotalArray[$key] = $argsArray['value'];
				$subTotalColspan++;
			}
		}
		$totalArray = $subTotalArray;

		// headers ****************
		$colspan = 0;
		$firstLine = current($results);
		$headerArray = array();
		foreach($displayArray as $header => $args){
			if(isset($firstLine[$header])){
				$headerArray[] = $header;
				$colspan++;
			}
		}
	
		// data ********************
		$style = "
			color: #444444;
			font-size: 14px;
			background-color: #cccccc;
		";
		$currentDepartment = "";
		$currentDepartmentId = "";
		foreach($results as $line){
			// headers and footers ********************
			if($currentDepartment != $line["Department"]){
				if($currentDepartment != ""){
					// footer *************************
					$htmlData .= showSubTotal($currentDepartmentId, $currentDepartment, $subTotalArray, $subTotalColspan, $colspan, $displayArray, $showSelects);
					$htmlData .= "</table>";
					$htmlData .= "<br>";
				}
				// header *****************************
				$htmlData .= startTable($line["Department Id"], $line["Department"], $headerArray, $colspan);
				
				foreach($subTotalArray as $subTotalKey => $subTotalValue){
					$subTotalArray[$subTotalKey] = 0;
				}
			}
			
			// fields *********************************
			$htmlData .= "\n<tr style='".$style."'>";
			foreach($displayArray as $label => $functionAndWidth){
				if(isset($line[$label])){
					$value = $line[$label];
					if(isset($functionAndWidth['function'])){
						$value = $functionAndWidth['function']($line[$label]);
					}
					$htmlData .= "\n<td style='min-width:".$functionAndWidth['width']."px;'>".$value."</td>";
					if(isset($subTotalArray[$label])){
						$subTotalArray[$label] += $line[$label];
						$totalArray[$label] += $line[$label];
					}
				}
			}
			$htmlData .= "\n</tr>";
			$currentDepartment = $line["Department"];
			$currentDepartmentId = $line["Department Id"];
		}
		
		// check if $results wasnt empty and show the last subTotal
		if($currentDepartment != ""){
			$htmlData .= showSubTotal($line["Department Id"], $line["Department"], $subTotalArray, $subTotalColspan, $colspan, $displayArray, $showSelects);
			$htmlData .= "</table>";
			$htmlData .= "<br>";
			
			$htmlData .= showTotal($totalArray, $colspan, $displayArray);
		}
		
		return $htmlData;
	}
	
	function costFormat($value){
		$formatedValue = round($value, 2);
		return $formatedValue;
	}
	
	function usageHtml($value){
		$hoursFloored = floor($value / 60);
		$minutes = $value % 60;
		
		$formatedValue = $hoursFloored."h : ".$minutes."m";
		return $formatedValue;
	}
	
	function generateJson($selectedDepartmentsArray = null){
		global $results, $iTotalDisplayRecords, $iTotalRecords, $htmlDisplayArray;
		generateResults($selectedDepartmentsArray);
		$aaData = array();
		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $iTotalRecords,
			"iTotalDisplayRecords" => $iTotalDisplayRecords,
		);
		
		$row = array();
		$value = null;
		// data ********************
		foreach($results as $line){
			$row = array();
			// fields *********************************
			foreach($htmlDisplayArray as $label => $data){
				$function = $data['function'];
				if(isset($function)){
					$value = $function($line[$label]);
				}
				else{
					$value = $line[$label];
				}
				
				$row[] = $value;
			}
			$aaData[] = $row;
		}
		$output['aaData'] = $aaData;
		
		return $output;
	}
	
	
	function generateCsv($selectedDepartmentsArray){
		global $results;
		generateResults($selectedDepartmentsArray);
		
		$csvData = "";
		$columnSeparator = "\t";
		$lineSeparator = "\n";
		
		$displayArray = array(
			"Department" => null
			, "Username" => null
			, "Resource" => null
			, "Entry date" => null
			, "Duration" => 'usageCsv'
			, "Price per hour" => null
			, "Subtotal" => 'costFormat'
			, "Discount" => 'costFormat'
			, "Project" => null
			, "Total" => 'costFormat'
		);

		// headers ****************
		$firstLine = current($results);
		$headerArray = array();
		foreach($displayArray as $header => $functionName){
			if(isset($firstLine[$header])){
				$csvData .= "\"".$header."\"".$columnSeparator;
			}
		}
	
		// data ********************
		$currentDepartment = "";
		foreach($results as $line){
			if($currentDepartment != $line["Department"]){
				$csvData .= $lineSeparator;
			}
			
			// fields *********************************
			foreach($displayArray as $label => $function){
				$value = $line[$label];
				if(isset($function)){
					$value = $function($line[$label]);
				}
				
				if(isset($value)){
					$csvData .= "\"".$value."\"".$columnSeparator;
				}
			}

			$csvData .= $lineSeparator;
			$currentDepartment = $line["Department"];
		}
		
		return $csvData;	
	}
	
	function usageCsv($value){
		return round($value / 60, 2);
	}

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
?>