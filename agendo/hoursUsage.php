<?php
	require_once("commonCode.php");
	
	// If user not logged shows error message
	if(isset($_SESSION['user_id'])){
		$isResp = isResp($_SESSION['user_id']);
		$isAdmin = isAdmin($_SESSION['user_id']);
		$isPI = isPI($_SESSION['user_id']);
		
		$userCheck = isset($_GET['userCheck']);
		$resourceCheck = isset($_GET['resourceCheck']);
		$entryCheck = isset($_GET['entryCheck']);
		$beginDate = $_GET['beginDate'];
		$endDate = $_GET['endDate'];
		$userLevel = $_GET['userLevel'];
		
		// show the selected departs as selected when refreshing the page? wont be trivial... make usercheck and other vars as global?
		$selectedDepartmentsArray = null;
		if(isset($_GET['departments'])){
			$selectedDepartmentsArray = json_decode($_GET['departments'], true);
		}
		
		if(isset($_GET['action'])){
			try{
				$returnErrorByEcho = $_GET['changeLocation'] == 'false' ? false : true;
				switch($_GET['action']){
					// Shows the results 
					case "generateReport":
						generateBaseHtml($userCheck, $resourceCheck, $entryCheck, $beginDate, $endDate, $isResp, $isAdmin, $isPI, $userLevel, $selectedDepartmentsArray);
					break;
					// Opens a file dialog to download a csv with all the selected info (select all and select none will generate the same csv file)
					case "downloadFile":
						header('Content-Type: application/force-download');
						header('Content-disposition: attachment; filename=report.xls');
						$prep = generatePrep($userCheck, $resourceCheck, $entryCheck, $beginDate, $endDate, $isResp, $isAdmin, $isPI, $selectedDepartmentsArray);
						echo generateCsvString($prep, $userCheck, $resourceCheck, $entryCheck);
					break;
					// Emails the department managers 
					case "emailDepartments":
						emailDepartments($userCheck, $resourceCheck, $entryCheck, $beginDate, $endDate, $isResp, $isAdmin, $isPI, $selectedDepartmentsArray);
						$json->success = true;
						$json->message = "Report sent";
						echo json_encode($json);
					break;
				}
			}
			catch(Exception $e){
				if($returnErrorByEcho){
					showMsg($e->getMessage(), true, true);
					generateBaseHtml($userCheck, $resourceCheck, $entryCheck, $beginDate, $endDate, $isResp, $isAdmin, $isPI, $userLevel, $selectedDepartmentsArray);
				}
				else{
					$json->success = false;
					$json->message = $e->getMessage();
					echo json_encode($json);
				}
			}
			exit;
		}
		
		generateBaseHtml($userCheck, $resourceCheck, $entryCheck, $beginDate, $endDate, $isResp, $isAdmin, $isPI, $userLevel, $selectedDepartmentsArray);
	}
	else{
		showMsg("You need to be logged in", true);
		echo "<br>";

		echo "<div style='margin:auto;width:200px;text-align:center;'>";
			echo "<a class='link' href='".$_SESSION['path']."/'>Back to reservations</a>";
		echo "</div>";
	}

	function generateBaseHtml($userCheck, $resourceCheck, $entryCheck, $beginDate, $endDate, $isResp, $isAdmin, $isPI, $userLevel, $selectedDepartmentsArray = null){
		importJs();
		echo "<link href='css/jquery.datepick.css' rel='stylesheet' type='text/css' />";
		echo "<link href='../agendo/css/hourUsage.css' rel='stylesheet' type='text/css' />";
		echo "<script type='text/javascript' src='js/jquery.datepick.js'></script>";
		echo "<script type='text/javascript' src='js/hoursUsage.js'></script>";
		
		echo "<br>";
		echo "<h1>Resource Usage</h1>";
		echo "<br>";
		
		echo "<table style='margin:auto;width:450px;text-align:right;'>";
			// Show by user priviledges
			$numberOfPrivileges = 0;
			$privilegeHtml = "";
			$isPITemp = $isPI;
			$isAdminTemp = $isAdmin;
			$isRespTemp = $isResp;
			
			if($isAdmin){
				$checkedPrivilege = "";
				if(!isset($userLevel)){
					$checkedPrivilege = 'checked';
				}
				elseif($userLevel == 'admin'){
					$checkedPrivilege = 'checked';
					$isRespTemp = $isPITemp = false;
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
				if(!isset($userLevel)){
					$checkedPrivilege = 'checked';
				}
				elseif($userLevel == 'pi'){
					$checkedPrivilege = 'checked';
					$isRespTemp = $isAdminTemp = false;
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
				if(!isset($userLevel)){
					$checkedPrivilege = 'checked';
				}
				elseif($userLevel == 'resp'){
					$checkedPrivilege = 'checked';
					$isPITemp = $isAdminTemp = false;
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
					echo "<td>";
						echo "<br>";
					echo "</td>";
				echo "</tr>";
			}
			
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

			$checked = "";
			echo "<tr>";
				echo "<td style='text-align:center;'>";
					echo "<label style='float:left;'>User";
						$checked = ($userCheck) ? "checked" : "";
						echo "<input type='checkBox' id='userCheck' ".$checked."/>";
					echo "</label>";

					echo "<label>Resource";
						$checked = ($resourceCheck) ? "checked" : "";
						echo "<input type='checkBox' id='resourceCheck' ".$checked."/>";
					echo "</label>";

					echo "<label style='float:right;'>Entry";
						$checked = ($entryCheck) ? "checked" : "";
						echo "<input type='checkBox' id='entryCheck' ".$checked."/>";
					echo "</label>";
				echo "</td>";
				
				echo "<td style='text-align:right;'>";
					// echo "<input type='button' id='searchButton' value='Generate Report' onclick='sendChecksAndDate(\"generateReport\")'/>";
					echo "<input type='button' id='searchButton' value='Generate Report' onclick='generateReport();'/>";
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td colspan='2' style='text-align:left;color:#F7C439;font-size:14px;'>";
					echo "Check the boxes above for additional information";
				echo "</td>";
			echo "</tr>";
			
		echo "</table>";
		
		echo "<br>";
		
		// Table where the results will appear
		echo "<div id='resultsDiv' style='margin:auto;width:800;text-align:center;'>";
			if($beginDate != "" && $endDate != ""){
				$prep = generatePrep($userCheck, $resourceCheck, $entryCheck, $beginDate, $endDate, $isRespTemp, $isAdminTemp, $isPITemp, $selectedDepartmentsArray);
				echo generateHtmlResults($prep, $userCheck, $resourceCheck, $entryCheck, ($isAdmin || $isPI != false || $isResp != false));
			}
		echo "</div>";
		
		echo "<br>";

		echo "<div style='margin:auto;width:200px;text-align:center;'>";
			echo "<a class='link' href='".$_SESSION['path']."/'>Back to reservations</a>";
		echo "</div>";
		echo "<br>";		
	}
	
	// Returns a string with what the subtotal line should look for each department
	function showSubTotal($departmentId, $subTotal, $colspan, $showSelects, $showSubTotal){
		$formatedString = "";
		$sql = "select department_name from department where department_id = :0";
		$prep = dbHelp::query($sql, array($departmentId));
		if(dbHelp::numberOfRows($prep) > 0){
			$row = dbHelp::fetchRowByIndex($prep);
			$departmentName = $row[0];
			
			$displaySubTotal = "none";
			if($showSubTotal){
				$displaySubTotal = "table";
			}

			$displaySelects = "none";
			if($showSelects){
				$displaySelects = "table";
			}
				
				$style = "
					color: black;
					font-size: 16px;
					background-color: white;
				";

				$formatedString = "\n<tr style='".$style."'>";
					$formatedString .= "\n<td colspan='".$colspan."'>";
						$formatedString .=  "<label class='emailLabels' style='display:".$displaySelects.";margin-left:10px;float:left'>Select";
							$formatedString .=  "<input type='checkBox' class='departmentChecks' id='".$departmentId."-EmailCheck' value='".$departmentId."'/>";
						$formatedString .=  "</label>";
					
						$formatedString .=  "&nbsp";
					
						$formatedString .= "<label style='display:".$displaySubTotal.";float:right;margin-right:10px;'>";
							$formatedString .= "Total for department ".$departmentName.": <a id='".$departmentId."SubTotal' name='".$subTotal."'>".$subTotal."</a>";
						$formatedString .= "</label>";
					$formatedString .= "</td>";
				$formatedString .= "</tr>";
			$formatedString .= "</table>";
			$formatedString .= "<br>";
		}

		return $formatedString;
	}
	
	// "Opens" a table (<table>) and adds the subHeader, subTotal function "closes" the table
	function startTable($departmentId, $subHeader, $colspan){
		$sql = "select user_login, department_name from department, ".dbHelp::getSchemaName().".user where department_id = :0 and user_id = department_manager";
		$prep = dbHelp::query($sql, array($departmentId));
		$row = dbHelp::fetchRowByIndex($prep);
		$formatedString = "\n<table id='".$departmentId."Table' summary='".$row[0]."' style='width:100%;text-align:center;'>";
		$style = "
			color: black;
			font-size: 16px;
			background-color: white;
		";
		$formatedString .= " 
			\n<td style='".$style."' colspan='".$colspan."'>
				Department: ".$row[1]."
			</td>\n
		";
		return $formatedString.$subHeader;
	}
	
	function showTotal($total, $colspan){
		$formatedString = "";
		$style = "
			color: #bb3322;
			font-size: 16px;
			background-color: white;
			text-align: center;
			margin: 2px;
		";
		$formatedString .= "\n<div style='".$style."'>";
			$formatedString .= "Total: ".$total;
		$formatedString .= "</div>";
		return $formatedString;
	}
	
	function generateHtmlResults($prep, $userCheck, $resourceCheck, $entryCheck, $showSelects){
		$formatedString = "";
	
		$headerArray = getHeaderArray($userCheck, $resourceCheck, $entryCheck);
		$colspan = sizeOf($headerArray);
		$extraOptions = "";
		if($showSelects){
			$extraOptions .= "<tr>";
				$extraOptions .=  "<td colspan='".$colspan."'>";
					$extraOptions .=  "<div style='display:table;text-align:center;width:100%;'>";
						$extraOptions .=  "<label style='float:left;'>Select all";
							$extraOptions .=  "<input type='checkBox' class='allCheck' onclick='selectUnselectAll(this);'/>";
						$extraOptions .=  "</label>";

						// $extraOptions .=  "<input style='float:right;' type='button' value='Email Departments' onclick='sendChecksAndDate(\"emailDepartments\", false);'/>";
						// $extraOptions .=  "<input style='float:right;' type='button' value='Export to Excel' onclick='sendChecksAndDate(\"downloadFile\");'/>";
						$extraOptions .=  "<input style='float:right;' type='button' value='Email Departments' onclick='emailDepartments();'/>";
						$extraOptions .=  "<input style='float:right;' type='button' value='Export to Excel' onclick='downloadFile();'/>";
					$extraOptions .=  "</div>";
				$extraOptions .=  "</td>";
			$extraOptions .= "</tr>";
		}
		$formatedString .= $extraOptions;
		
		$style = "
			color: black;
			font-size: 16px;
			background-color: white;
		";
		$subHeader .= "<tr style='".$style."'>";
		foreach($headerArray as $headerKey=>$headerValue){
			$subHeader .= "<td style='width:".$headerValue[sizeOf($headerValue)-1]."px'>";
			$subHeader .= $headerKey;
			$subHeader .= "</td>";
		}
		$subHeader .= "</tr>";
		$departmentElements = 0;
		while($row = dbHelp::fetchRowByName($prep)){
			$hours = current($row);
			$price = next($row);
			$department = next($row);
			unset($headerArray['Usage']);
			unset($headerArray['Cost']);
			
			$hoursRounded = round($hours/60, 2);
			$hoursFloored = floor($hours / 60);
			$minutes = $hours % 60;
			$priceTimesHours = round($price, 2);
			$departmentElements++;
			
			if(
				$previousDepartmentName != $department
			){
				if($previousDepartmentName != ""
				){
					$formatedString .= showSubTotal($previousDepartmentName, $subTotal, $colspan, $showSelects && !$isEmail, dbHelp::numberOfRows($prep) > 1);
					$subTotal = 0;
					$departmentElements = 0;
				}
				$formatedString .= startTable($department, $subHeader, $colspan);
			}

			$style = "
				color: #444444;
				font-size: 14px;
				background-color: #cccccc;
			";
			$formatedString .= "\n<tr style='".$style."'>";
				// Names if User is checked
				foreach($headerArray as $header){
					$tempColumn = "";
					$formatedString .= "\n<td>";
					for($i=0; $i<sizeOf($header)-1; $i++){
						$tempColumn .= $row[$header[$i]]." ";
					}
					$formatedString .= trim($tempColumn);
					$formatedString .= "</td>";
				}
				
				// Hours
				$formatedString .= "\n<td>";
					$formatedString .= $hoursFloored."h : ".$minutes."m";
				$formatedString .= "</td>";
				
				// Price*Hours
				$formatedString .= "\n<td>";
					$formatedString .= $priceTimesHours;
				$formatedString .= "</td>";
			$formatedString .= "</tr>";
			
			$previousDepartmentName = $department;
			$subTotal += $priceTimesHours;
			$total += $priceTimesHours;
		}
		
		// Used to show the last subtotal
		$formatedString .= showSubTotal($previousDepartmentName, $subTotal, $colspan, $showSelects && !$isEmail, dbHelp::numberOfRows($prep) > 1);
		
		$formatedString .= showTotal($total, $colspan);
		
		$formatedString .= $extraOptions;
		
		return $formatedString;
	}
	
	function generatePrep($userCheck, $resourceCheck, $entryCheck, $beginDate, $endDate, $isResp, $isAdmin, $isPI, $selectedDepartmentsArray = null){
		// Just a precautionary measure to prevent sql injection
		$selectedDepartmentsSql = "";
		$inDepartmentData = dbHelp::inDataFromArray($selectedDepartmentsArray); 
		if($inDepartmentData !== false){
			$selectedDepartmentsSql = " and department_id in ".$inDepartmentData['inData'];
		}
		
		$whereIsResp = "";
		$whereDepartment = "user_id = entry_user and department_id = user_dep";
		if(!$isAdmin){
			if($isPI === false){
				$sql = "select user_dep from ".dbHelp::getSchemaName().".user where user_id = :0";
				$prep = dbHelp::query($sql, array($_SESSION['user_id']));
				$row = dbHelp::fetchRowByIndex($prep);
				$departments = $row[0];
				$whereDepartment = "user_id = entry_user and user_dep = ".$departments." and department_id = user_dep";
			}
			else{
				$departments = implode(",", $isPI);
				$whereDepartment = "user_id = entry_user and user_dep in (".$departments.") and department_id = user_dep";
			}
			
			if($isResp !== false){
				$whereIsResp = "or user_id = entry_user and user_dep not in (".$departments.") and department_id = user_dep and entry_resource in (".implode(",", $isResp).")";
			}
		}

		$entrySelect = "
			sum(entry_slots * resource_resolution) AS invoice_hours
			,sum(entry_slots * resource_resolution * price_value / 60) AS invoice_price
			,department_id AS invoice_department
		";
		$entryGroupBy = "";
		if($entryCheck == 1){
			$entrySelect = "
				entry_slots * resource_resolution AS invoice_hours
				,entry_slots * resource_resolution * price_value / 60 AS invoice_price
				,department_id AS invoice_department
				,entry_datetime
			";
			$entryGroupBy = ", entry_id order by department_name, entry_datetime";
		}
			
		$userSelect = "";
		$userGroupBy = "";
		if($userCheck == 1){
			$userSelect = ",user_firstname, user_lastname";
			$userGroupBy = ",user_firstname, user_lastname";
		}
			
		$resourceSelect = "";
		$resourceGroupBy = "";
		if($resourceCheck == 1){
			$resourceSelect = ", resource_name, price_value";
			$resourceGroupBy = ", resource_name";
		}
		
		$beginDate = str_replace("/", "-", $beginDate);
		$endDate = str_replace("/", "-", $endDate);
		if(!strtotime($beginDate) || !strtotime($endDate)){
			throw new Exception("Not a valid date");
		}
		elseif(strtotime($beginDate) > strtotime($endDate)){
			throw new Exception("\'From date\' is after \'To date\'");
		}
		
		$beginDate = dbHelp::convertToDate($beginDate);
		$endDate = dbHelp::convertToDate($endDate);
			
		$staticWhere = "
			and institute_id = department_inst
			and price_type = institute_pricetype
			and price_resource = entry_resource
			and entry_status = 1
			and resource_id = entry_resource
			and entry_datetime between '".$beginDate."' and '".$endDate."'
		";

		$resourcesManagerWhere = "";
		if($whereIsResp != ""){
			$resourcesManagerWhere = $whereIsResp." ".$staticWhere;
		}

		$sql = "
			select 
				".$entrySelect." 
				".$userSelect."
				".$resourceSelect."
			from 
				entry
				,".dbHelp::getSchemaName().".user
				,resource
				,department
				,price
				,institute
			where 
				".$whereDepartment."
				".$staticWhere."
				".$resourcesManagerWhere."
				".$selectedDepartmentsSql."
			group by 
				department_name
				".$resourceGroupBy."
				".$userGroupBy."
				".$entryGroupBy."
		";
		return dbHelp::query($sql, $selectedDepartmentsArray);
	}
	
	function emailDepartments($userCheck, $resourceCheck, $entryCheck, $beginDate, $endDate, $isResp, $isAdmin, $isPI, $selectedDepartmentsArray){
		$defaultMailTitle = "Usage report";
		$message = "The report was sent by attachment as html.";
		$mansAndDeps = getManagersAndTheirDepartmentsFromDepartmentsList($selectedDepartmentsArray);
		foreach($mansAndDeps as $man => $deps){
			// Get manager email here
			$sql = "select user_email from ".dbHelp::getSchemaName().".user where user_id = :0";
			$prepMail = dbHelp::query($sql, array($man));
			$row = dbHelp::fetchRowByIndex($prepMail);
			$mail = getMailObject($defaultMailTitle, $row[0], $message, "[AGENDO]", "support@cirklo.org");
			
			// Adds the content of the xls file to the email as an attachment
			// $prep = generatePrep($userCheck, $resourceCheck, $entryCheck, $beginDate, $endDate, $isResp, $isAdmin, $isPI, $deps);
			// $mail->AddStringAttachment(generateCsvString($prep, $userCheck, $resourceCheck, $entryCheck), "report.xls");

			// Adds the content of the html file to the email as an attachment
			$mail->isHtml(true);
			$html = "<html>";
				$html .= "<body bgcolor='#1e4F54'>";
					$html .= "<div style='width:800px'>";
							// Need to recall this method, because the previous $prep is "used up"
							$prep = generatePrep($userCheck, $resourceCheck, $entryCheck, $beginDate, $endDate, $isResp, $isAdmin, $isPI, $deps);
							$html .= generateHtmlResults($prep, $userCheck, $resourceCheck, $entryCheck, false);
					$html .= "</div>";
				$html .= "</html>";
			$html .= "</body>";
			$mail->AddStringAttachment($html, "report.html");
			
			// Sends the email
			sendMailObject($mail);
		}
	}
	
	function generateCsvString($prep, $userCheck, $resourceCheck, $entryCheck){
		$csv = "";
		$column = "";
		$previousDepartment = "";
		$departmentName = "";
		$columnSeparator = "\t";
		$lineSeparator = "\n";
		$headerArray = getHeaderArray($userCheck, $resourceCheck, $entryCheck);
		
		// Introduces the headers on xls file before the rest of the data
		$csv .= "Department".$columnSeparator;
		$csv .= implode($columnSeparator, array_keys($headerArray)).$lineSeparator;
		
		while($row = dbHelp::fetchRowByName($prep)){
			if($row['invoice_department'] != $previousDepartment){
				if($previousDepartment != ""){
					$csv .= $lineSeparator;
				}
				$previousDepartment = $row['invoice_department'];
				$sql = "select department_name from department where department_id = :0";
				$prepDepartment = dbHelp::query($sql, array($previousDepartment));
				$rowDep = dbHelp::fetchRowByIndex($prepDepartment);
				$departmentName = $rowDep[0];
			}
			$csv .= $departmentName.$columnSeparator;
			foreach($headerArray as $subHeader){
				for($i=0; $i<sizeOf($subHeader)-1;$i++){
					$column .= $row[$subHeader[$i]]." ";
				}
				$csv .= trim($column).$columnSeparator;
				$column = "";
			}
			$csv .= $lineSeparator;
		}
		return $csv;
	}
	
	function getHeaderArray($userCheck, $resourceCheck, $entryCheck){
		$headerArray = array();
		
		if($entryCheck == 1){
			$headerArray["Entry date"] = array("entry_datetime", 150);
		}
		
		if($userCheck == 1){
			$headerArray["User name"] = array("user_firstname", "user_lastname", 200);
		}
		
		if($resourceCheck == 1){
			$headerArray["Resource"] = array("resource_name", 200);
			$headerArray["Price per hour"] = array("price_value", 75);
		}
	
		$headerArray["Usage"] = array("invoice_hours", 100);
		$headerArray["Cost"] = array("invoice_price", 75);
		return $headerArray;
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
		return $mansAndDeps;
	}
?>