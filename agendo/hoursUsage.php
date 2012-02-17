<?php
	require_once("commonCode.php");
	
	$isResp = isResp($_SESSION['user_id']);
	$isAdmin = isAdmin($_SESSION['user_id']);
	$isPI = isPI($_SESSION['user_id']);
	
	if(
		isset($_POST['userCheck'])
		&& isset($_POST['resourceCheck'])
		&& isset($_POST['beginDate'])
		&& isset($_POST['endDate'])
	){
		try{
			$showSubTotal = false;
			$colspan = 3;
			$json->tableData = "";
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

			$json->tableData .= "<tr style='border-bottom: 1px solid black;'>";
				$json->tableData .= "<td>";
					$json->tableData .= "Department";
				$json->tableData .= "</td>";
			
				$entrySelect = "
					sum(entry_slots * resource_resolution / 60) AS invoice_hours
					,sum(entry_slots * resource_resolution * price_value / 60) AS invoice_price
					,department_name AS invoice_department
				";
				$entryGroupBy = "";
				if($_POST['entryCheck'] == 1){
					$entrySelect = "
						entry_slots * resource_resolution / 60 AS invoice_hours
						,entry_slots * resource_resolution * price_value / 60 AS invoice_price
						,department_name AS invoice_department
						,entry_datetime
					";
					$entryGroupBy = ", entry_id order by department_name, entry_datetime";
					
					$json->tableData .= "<td>";
						$json->tableData .= "Entry date";
					$json->tableData .= "</td>";
					$colspan++;
				}
				
				$userSelect = "";
				$userGroupBy = "";
				if($_POST['userCheck'] == 1){
					$userSelect = ",user_firstname, user_lastname";
					$userGroupBy = ",user_firstname, user_lastname";
					$showSubTotal = true;
					
					$json->tableData .= "<td colspan='2'>";
						$json->tableData .= "User name";
					$json->tableData .= "</td>";
					
					$colspan += 2;
				}
				
				$resourceSelect = "";
				$resourceGroupBy = "";
				if($_POST['resourceCheck'] == 1){
					$resourceSelect = ", resource_name, price_value";
					$resourceGroupBy = ", resource_name";
					$showSubTotal = true;
					
					$json->tableData .= "<td>";
						$json->tableData .= "Resource";
					$json->tableData .= "</td>";
					
					$json->tableData .= "<td>";
						$json->tableData .= "Price per hour";
					$json->tableData .= "</td>";
					
					$colspan += 2;
				}
			
				$json->tableData .= "<td>";
					$json->tableData .= "Usage time(hours)";
				$json->tableData .= "</td>";
				
				$json->tableData .= "<td>";
					$json->tableData .= "Cost";
				$json->tableData .= "</td>";
			$json->tableData .= "</tr>";
			
			$json->tableData .= "<tr>";
				$json->tableData .= "<td colspan='".$colspan."'>";
					$json->tableData .= "<hr>";
				$json->tableData .= "</td>";
			$json->tableData .= "</tr>";

			$beginDate = dbHelp::convertToDate(str_replace("/", "-", $_POST['beginDate']));
			$endDate = dbHelp::convertToDate(str_replace("/", "-", $_POST['endDate']));
			
			$json->tableData .= generateResults();

			$json->tableData .= "<tr>";
				$json->tableData .= "<td colspan='".$colspan."'>";
					$json->tableData .= "<hr>";
					$json->tableData .= "Total: ".$total;
				$json->tableData .= "</td>";
			$json->tableData .= "</tr>";
			$json->success = true;
		}
		catch(Exception $e){
			$json->success = false;
			$json->message = "Error: ".$e->getMessage();
		}
		echo json_encode($json);
		exit;
	}
	
	echo "<script type='text/javascript' src='js/jquery-1.5.2.min.js'></script>";
	echo "<link href='css/jquery.datepick.css' rel='stylesheet' type='text/css' />";
	echo "<script type='text/javascript' src='js/jquery.datepick.js'></script>";
	echo "<script type='text/javascript' src='js/hoursUsage.js'></script>";
	
	echo "<table style='margin:auto;width:320px;text-align:right;'>";
		echo "<tr>";
			echo "<td colspan='3 style='text-align:center;'>";
				echo "<a style='color:black;text-align:center;' onmouseover=\"this.style.color='#F7C439'\" onmouseout=\"this.style.color='black'\" href='".$_SESSION['path']."/'>Back to reservations</a>";
			echo "</td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td>";
				echo "<a>From date:</a>";
			echo "</td>";

			echo "<td style='text-align:center;'>";
				echo "<input type='text' id='beginDateText' style='text-align:center;'/>";
			echo "</td>";

			echo "<td>";
				echo "<label>Resource";
					echo "<input type='checkBox' id='resourceCheck'/>";
				echo "</label>";
			echo "</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td>";
				echo "<a>To date:</a>";
			echo "</td>";

			echo "<td style='text-align:center;'>";
				echo "<input type='text' id='endDateText' style='text-align:center;'/>";
			echo "</td>";

			echo "<td>";
				echo "<label>User";
					echo "<input type='checkBox' id='userCheck'/>";
				echo "</label>";
			echo "</td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td></td>";
			
			echo "<td style='text-align:center;'>";
				echo "<input type='button' id='searchButton' value='Search' onclick='sendChecksAndGetResult()'/>";
			echo "</td>";
			
			echo "<td>";
				echo "<label>Entry";
					echo "<input type='checkBox' id='entryCheck'/>";
				echo "</label>";
			echo "</td>";
		echo "</tr>";
	echo "</table>";
	
	// Table where the results will appear
	echo "<table id='resultsTable' style='margin:auto;border:1px solid black;width:800;text-align:center;'>";
	echo "</table>";
	
	// Returns a string with what the subtotal line should look for each department
	function showSubTotal($departmentName, $subTotal, $colspan, $isAdmin){
		$subColspan = 0;
		$extraHtml = "";
		if($isAdmin){
			$subColspan = 2;
			// Put the checkbox in a class for easy multi select and un-select
			$extraHtml = 
				"<td colspan='".$subColspan."' style='text-align:right;'>
					<label>Mail the department manager
						<input type='checkBox' id='department-".$departmentName."-Email'/>
					</label>
				</td>"
			;
		}
	
		$formatedString = "<tr>";
			$formatedString .= "<td colspan='".($colspan-$subColspan)."'>";
				$formatedString .= "Total for department '".$departmentName."': ".$subTotal;
			$formatedString .= "</td>";
			
			$formatedString .= $extraHtml;
		$formatedString .= "</tr>";
		
		return $formatedString;
	}
	
	function generateResults(){
		global 	$total, $colspan, $beginDate, $endDate, $showSubTotal, $isAdmin, $entrySelect, $userSelect, 
				$resourceSelect, $whereDepartment, $resourceGroupBy, $userGroupBy, $entryGroupBy, $whereIsResp;
		
		$formatedString = "";
		$previousDepartmentName = "";
		$subTotal = 0;
		$staticWhere = "
			and institute_id = department_inst
			and price_type = institute_pricetype
			and price_resource = entry_resource
			and entry_status in (1,5)
			and resource_id = entry_resource
			and entry_datetime between :0 and :1
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
		while($row = dbHelp::fetchRowByIndex($prep)){
			$department = $row[2];
			if(
				$previousDepartmentName != $department
				&& $showSubTotal
				&& $previousDepartmentName != ""
			){
				$formatedString .= showSubTotal($previousDepartmentName, $subTotal, $colspan, $isAdmin);
				$subTotal = 0;
			}
			$hours = round($row[0], 2);
			$priceTimesHours = round($row[1], 2);
			
			$formatedString .= "<tr>";
				// Department and Names if User is checked
				for($i=2; $i<sizeOf($row); $i++){
					$formatedString .= "<td>";
						$formatedString .= $row[$i];
					$formatedString .= "</td>";
				}
				
				// Hours
				$formatedString .= "<td>";
					$formatedString .= $hours;
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
		if($showSubTotal){
			$formatedString .= showSubTotal($previousDepartmentName, $subTotal, $colspan, $isAdmin);
		}
		
		return $formatedString;
	}
?>