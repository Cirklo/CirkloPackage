<?php
	require_once("commonCode.php");
	
	$isResp = isResp($_SESSION['user_id']);
	$isAdmin = isAdmin($_SESSION['user_id']);
	$isPI = isPI($_SESSION['user_id']);
	
	if(
		isset($_POST['search'])
		&& isset($_POST['userCheck'])
		&& isset($_POST['resourceCheck'])
		&& isset($_POST['entryCheck'])
		&& isset($_POST['beginDate'])
		&& isset($_POST['endDate'])
	){
		try{
			$json->tableData = generateResults($_POST['userCheck'], $_POST['resourceCheck'], $_POST['entryCheck'], $_POST['beginDate'], $_POST['endDate'], $isResp, $isAdmin, $isPI);
			$json->tableData .= "<br>";
			$json->success = true;
		}
		catch(Exception $e){
			$json->success = false;
			$json->message = "Error: ".$e->getMessage();
		}
		echo json_encode($json);
		exit;
	}
	elseif(
		isset($_POST['emailManagers'])
		&& isset($_POST['managers'])
		&& isset($_POST['totals'])
	){
		try{
			emailManagers($_POST['managers'], $_POST['totals']);
			$json->success = true;
			$json->message = "Report sent";
		}
		catch(Exception $e){
			$json->success = false;
			$json->message = "Error: ".$e->getMessage();
		}
		echo json_encode($json);
		exit;
	}
	
	// echo "<script type='text/javascript' src='js/jquery-1.5.2.min.js'></script>";
	importJs();
	echo "<link href='css/jquery.datepick.css' rel='stylesheet' type='text/css' />";
	echo "<link href='../agendo/css/hourUsage.css' rel='stylesheet' type='text/css' />";
	echo "<script type='text/javascript' src='js/jquery.datepick.js'></script>";
	echo "<script type='text/javascript' src='js/hoursUsage.js'></script>";
	
	echo "<br>";
	echo "<h1>Resource Usage</h1>";
	echo "<br>";
	
	echo "<table style='margin:auto;width:450px;text-align:right;'>";
		echo "<tr>";
			echo "<td style='text-align:left;'>";
				echo "<a>From date:</a>";
				echo "&nbsp";
				echo "<input type='text' id='beginDateText' style='text-align:center;'/>";
			echo "</td>";

			echo "<td style='text-align:right;'>";
				echo "<a>To date:</a>";
				echo "&nbsp";
				echo "<input type='text' id='endDateText' style='text-align:center;'/>";
			echo "</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td style='text-align:center;'>";
				echo "<label style='float:left;'>User";
					echo "<input type='checkBox' id='userCheck'/>";
				echo "</label>";

				echo "<label>Resource";
					echo "<input type='checkBox' id='resourceCheck'/>";
				echo "</label>";

				echo "<label style='float:right;'>Entry";
					echo "<input type='checkBox' id='entryCheck'/>";
				echo "</label>";
			echo "</td>";
			
			echo "<td style='text-align:right;'>";
				echo "<input type='button' id='searchButton' value='Search' onclick='sendChecksAndGetResult()'/>";
			echo "</td>";
		echo "</tr>";
	echo "</table>";
	
	echo "<br>";
	
	// Table where the results will appear
	echo "<div id='resultsTable' style='margin:auto;width:800;text-align:center;'>";
	echo "</div>";
	
	echo "<br>";

	echo "<div style='margin:auto;width:200px;text-align:center;'>";
		echo "<a class='link' href='".$_SESSION['path']."/'>Back to reservations</a>";
	echo "</div>";
	echo "<br>";
	
	// Returns a string with what the subtotal line should look for each department
	function showSubTotal($departmentName, $subTotal, $colspan, $isAdmin, $isPI, $showSubTotal){
			$style = "
				color: black;
				font-size: 16px;
				background-color: white;
			";

			$displaySubTotal = "none";
			if($showSubTotal){
				$displaySubTotal = "table";
			}

			$displayEmail = "none";
			if($isAdmin || $isPI != false){
				$displayEmail = "table";
			}
			
			$formatedString = "\n<tr style='".$style."'>";
				$formatedString .= "\n<td colspan='".$colspan."'>";
					$formatedString .=  "<label class='emailLabels' style='display:".$displayEmail.";margin-left:10px;float:left' title='Select to email the department manager'>Email";
						$formatedString .=  "<input type='checkBox' class='emailChecks' id='".$departmentName."-EmailCheck' value='".$departmentName."'/>";
					$formatedString .=  "</label>";
				
					$formatedString .=  "&nbsp";
				
					$formatedString .= "<label style='display:".$displaySubTotal.";float:right;margin-right:10px;'>";
						$formatedString .= "Total for department ".$departmentName.": <a id='".$departmentName."SubTotal' name='".$subTotal."'>".$subTotal."</a>";
					$formatedString .= "</label>";
				$formatedString .= "</td>";
			$formatedString .= "</tr>";
		$formatedString .= "</table>";
		$formatedString .= "<br>";
		
		return $formatedString;
	}
	
	// "Opens" a table (<table>) and adds the subHeader, subTotal function "closes" the table
	function startTable($department, $subHeader, $colspan){
		$sql = "select user_login from department, ".dbHelp::getSchemaName().".user where department_name = :0 and user_id = department_manager";
		$prep = dbHelp::query($sql, array($department));
		$row = dbHelp::fetchRowByIndex($prep);
		$formatedString = "\n<table id='".$department."Table' summary='".$row[0]."' style='width:100%;text-align:center;'>";
		$style = "
			color: black;
			font-size: 16px;
			background-color: white;
		";
		$formatedString .= " 
			\n<td style='".$style."' colspan='".$colspan."'>
				Department: ".$department."
			</td>
		";
		return $formatedString.$subHeader;
	}
	
	function showTotal($total){
		$formatedString = "";
		$style = "
			color: #bb3322;
			font-size: 16px;
			background-color: white;
			text-align: center;
		";
		$formatedString .= "\n<div style='".$style."'>";
			$formatedString .= "Total: ".$total;
		$formatedString .= "</div>";
		return $formatedString;
	}
	
	function generateResults($userCheck, $resourceCheck, $entryCheck, $beginDate, $endDate, $isResp, $isAdmin, $isPI){
		$showSubTotal = false;
		$colspan = 2;
		$formatedString = "";
		
		$whereIsResp = "";
		$whereDepartment = "user_id = entry_user and department_id = user_dep";
		if(!$isAdmin){
			if($isPI == false){
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
			
			if($isResp != false){
				$whereIsResp = "or user_id = entry_user and user_dep not in (".$departments.") and department_id = user_dep and entry_resource in (".implode(",", $isResp).")";
			}
		}

		$style = "
			color: black;
			font-size: 16px;
			background-color: white;
		";
		$subHeader .= "<tr style='".$style."'>";
				// sum(entry_slots * resource_resolution / 60) AS invoice_hours
			$entrySelect = "
				sum(entry_slots * resource_resolution) AS invoice_hours
				,sum(entry_slots * resource_resolution * price_value / 60) AS invoice_price
				,department_name AS invoice_department
			";
			$entryGroupBy = "";
			if($_POST['entryCheck'] == 1){
					// entry_slots * resource_resolution / 60 AS invoice_hours
				$entrySelect = "
					entry_slots * resource_resolution AS invoice_hours
					,entry_slots * resource_resolution * price_value / 60 AS invoice_price
					,department_name AS invoice_department
					,entry_datetime
				";
				$entryGroupBy = ", entry_id order by department_name, entry_datetime";
				
				$subHeader .= "<td>";
					$subHeader .= "Entry date";
				$subHeader .= "</td>";
				$colspan++;
			}
			
			$userSelect = "";
			$userGroupBy = "";
			if($_POST['userCheck'] == 1){
				$userSelect = ",user_firstname, user_lastname";
				$userGroupBy = ",user_firstname, user_lastname";
				$showSubTotal = true;
				
				$subHeader .= "<td colspan='2'>";
					$subHeader .= "User name";
				$subHeader .= "</td>";
				
				$colspan += 2;
			}
			
			$resourceSelect = "";
			$resourceGroupBy = "";
			if($_POST['resourceCheck'] == 1){
				$resourceSelect = ", resource_name, price_value";
				$resourceGroupBy = ", resource_name";
				$showSubTotal = true;
				
				$subHeader .= "<td>";
					$subHeader .= "Resource";
				$subHeader .= "</td>";
				
				$subHeader .= "<td>";
					$subHeader .= "Price per hour";
				$subHeader .= "</td>";
				
				$colspan += 2;
			}
		
			$subHeader .= "<td>";
				$subHeader .= "Usage time";
			$subHeader .= "</td>";
			
			$subHeader .= "<td>";
				$subHeader .= "Cost";
			$subHeader .= "</td>";
		$subHeader .= "</tr>";
		
		$beginDate = str_replace("/", "-", $_POST['beginDate']);
		$endDate = str_replace("/", "-", $_POST['endDate']);
		if(strtotime($beginDate) > strtotime($endDate)){
			throw new Exception("'From date' is after 'To date'");
		}
		elseif(!strtotime($beginDate) || !strtotime($endDate)){
			throw new Exception("Not a valid date");
		}
		
		$beginDate = dbHelp::convertToDate($beginDate);
		$endDate = dbHelp::convertToDate($endDate);
			
		$previousDepartmentName = "";
		$subTotal = 0;
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
			group by 
				department_name
				".$resourceGroupBy."
				".$userGroupBy."
				".$entryGroupBy."
		";
		$prep = dbHelp::query($sql, array($beginDate, $endDate));
		$departmentElements = 0;
		while($row = dbHelp::fetchRowByIndex($prep)){
			$departmentElements++;
			$department = $row[2];
			if(
				$previousDepartmentName != $department
			){
				if($previousDepartmentName != ""
				){
					$formatedString .= showSubTotal($previousDepartmentName, $subTotal, $colspan, $isAdmin, $isPI, dbHelp::numberOfRows($prep) > 1);
					// $formatedString .= showSubTotal($previousDepartmentName, $subTotal, $colspan, $isAdmin, $isPI, $departmentElements > 1);
					$subTotal = 0;
					$departmentElements = 0;
				}
				$formatedString .= startTable($department, $subHeader, $colspan);
			}

			// $hours = round($row[2], 2);
			$hours = floor($row[0] / 60);
			$minutes = $row[0] % 60;
			$priceTimesHours = round($row[1], 2);
			$style = "
				color: #444444;
				font-size: 14px;
				background-color: #cccccc;
			";
			// $formatedString .= "<tr class='resultsData'>";
			$formatedString .= "<tr style='".$style."'>";
				// Names if User is checked
				for($i=3; $i<sizeOf($row); $i++){
					$formatedString .= "<td>";
						$formatedString .= $row[$i];
					$formatedString .= "</td>";
				}
				
				// Hours
				$formatedString .= "<td>";
					// $formatedString .= $hours;
					$formatedString .= $hours."h : ".$minutes."m";
				$formatedString .= "</td>";
				
				// Price*Hours
				$formatedString .= "<td>";
					$formatedString .= $priceTimesHours;
				$formatedString .= "</td>";
			$formatedString .= "</tr>";
			
			$previousDepartmentName = $department;
			$subTotal += $priceTimesHours;
			$total += $priceTimesHours;
		}
		
		// Used to show the last subtotal
		$formatedString .= showSubTotal($previousDepartmentName, $subTotal, $colspan, $isAdmin, $isPI, dbHelp::numberOfRows($prep) > 1);
		// $formatedString .= showSubTotal($previousDepartmentName, $subTotal, $colspan, $isAdmin, $isPI, $departmentElements > 1);
		
		$formatedString .= showTotal($total);
		
		if($isAdmin || $isPI != false){
			$formatedString .=  "<div style='text-align:center;' colspan='".$colspan."'>";
				$formatedString .=  "<label style='float:left;'>Select all email addresses";
					$formatedString .=  "<input type='checkBox' id='allEmailsCheck' onclick='selectUnselectAll();'/>";
				$formatedString .=  "</label>";

				$formatedString .=  "<input style='float:right;' type='button' id='emailButton' value='Email Departments' onclick='email();'/>";
			$formatedString .=  "</div>";
		}
		
		return $formatedString;
	}
	
	function emailManagers($managers, $totals){
		$defaultMailTitle = "Usage report";
		$from = "Agendo";
		$managers = json_decode($managers, true);
		foreach($managers as $manager => $departments){
			// Get manager email here
			$sql = "select user_email from ".dbHelp::getSchemaName().".user where user_login = :0";
			$prep = dbHelp::query($sql, array($manager));
			$row = dbHelp::fetchRowByIndex($prep);
			$message = "<html>";
				$message .= "<body bgcolor='#1e4F54'>";
				foreach($departments as $department){
					$message .= $department;
					$message .= "\n<br>";
				}
				$message .= showTotal($totals[$manager]);
				$message .= "</body>";
			$message .= "</html>";
			
			sendMail($defaultMailTitle, $row[0], $message, $from, true, true);
		}
	}
?>