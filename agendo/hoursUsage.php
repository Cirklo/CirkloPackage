<?php
	// session_start();
	// $pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
	// $_SESSION['path'] = "../".$pathOfIndex[sizeof($pathOfIndex)-1];
	require_once("commonCode.php");

	if(
		isset($_POST['userCheck'])
		&& isset($_POST['resourceCheck'])
		&& isset($_POST['beginDate'])
		&& isset($_POST['endDate'])
	){
		try{
			$beginDate = dbHelp::convertToDate($_POST['beginDate']);
			$endDate = dbHelp::convertToDate($_POST['endDate']);
			$showSubTotal = false;
			$previousDepartmentName = "";
			
			$resourceSelect = "";
			$resourceGroupBy = "";
			if($_POST['resourceCheck'] == 1){
				$resourceSelect = ",resource_name AS invoice_resource";
				$resourceGroupBy = ",resource_name";
				$showSubTotal = true;
			}
			
			$userSelect = "";
			$userGroupBy = "";
			if($_POST['userCheck'] == 1){
				$userSelect = ",user_firstname, user_lastname";
				$userGroupBy = ",user_firstname, user_lastname";
				$showSubTotal = true;
			}

			$sql = "
				select 
					sum(entry_slots * resource_resolution) / 60 AS invoice_hours
					,sum(entry_slots * resource_resolution / 60 * price_value) AS invoice_price 
					,department_name AS invoice_department
					".$resourceSelect."
					".$userSelect."
				from 
					entry
					,user
					,resource
					,department
					,price
				where 
					user.user_dep = department_id
					and entry_user = user.user_id
					and resource_id = entry_resource
					and price_resource = entry_resource
					and entry_datetime between :0 and :1
				group by 
					department_name
					".$resourceGroupBy."
					".$userGroupBy."
			";
			
			$subtotal = 0;
			$total = 0;
			$prep = dbHelp::query($sql, array($beginDate, $endDate));
			$json->tableData = "";
			$colspan = 0;
			while($row = dbHelp::fetchRowByIndex($prep)){
				$department = $row[2];
				$colspan = sizeOf($row);
				if($previousDepartmentName != $department){
					// $json->tableData .= "<tr>";
						// $json->tableData .= "<td>";
							// $json->tableData .= $department;
							// $json->tableData .= "<hr>";
						// $json->tableData .= "</td>";
					// $json->tableData .= "</tr>";
					
					if($showSubTotal){
						if($previousDepartmentName != ""){
							$json->tableData .= "<tr>";
								$json->tableData .= "<td colspan='".$colspan."'>";
									$json->tableData .= "<hr>";
									$json->tableData .= "Subtotal for department '".$previousDepartmentName."': ".$subtotal;
									$json->tableData .= "<hr>";
								$json->tableData .= "</td>";
							$json->tableData .= "</tr>";
							$subtotal = 0;
						}
					}
				}
				$hours = round($row[0], 2);
				$priceTimesHours = round($row[1], 2);
				
				$json->tableData .= "<tr>";
					// Department and Names if User is checked
					for($i=2; $i<sizeOf($row); $i++){
						$json->tableData .= "<td>";
							$json->tableData .= $row[$i];
						$json->tableData .= "</td>";
					}
					
					// Hours
					$json->tableData .= "<td>";
						$json->tableData .= $hours;
					$json->tableData .= "</td>";
					
					// Price*Hours
					$json->tableData .= "<td>";
						$json->tableData .= $priceTimesHours;
					$json->tableData .= "</td>";
				$json->tableData .= "</tr>";
				
				$previousDepartmentName = $department;
				$subtotal += $priceTimesHours;
				$total += $priceTimesHours;
			}
			
			if($showSubTotal){
				$json->tableData .= "<tr>";
					$json->tableData .= "<td colspan='".$colspan."'>";
						$json->tableData .= "<hr>";
						$json->tableData .= "Subtotal for department '".$previousDepartmentName."': ".$subtotal;
					$json->tableData .= "</td>";
				$json->tableData .= "</tr>";
			}
			
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
	// else{
		// $json->success = false;
		// $json->message = "Please check the input parameters";
		// echo json_encode($json);
		// exit;
	// }
	
	echo "<script type='text/javascript' src='js/jquery-1.5.2.min.js'></script>";
	echo "<link href='css/jquery.datepick.css' rel='stylesheet' type='text/css' />";
	echo "<script type='text/javascript' src='js/jquery.datepick.js'></script>";
	echo "<script type='text/javascript' src='js/hoursUsage.js'></script>";
	
	echo "<table style='margin:auto;width:320px;text-align:right;'>";
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
			
			echo "<td></td>";
		echo "</tr>";
	echo "</table>";
	
	echo "<table id='resultsTable' style='margin:auto;border:1px solid black;width:800;text-align:center;'>";
		
	echo "</table>";
?>