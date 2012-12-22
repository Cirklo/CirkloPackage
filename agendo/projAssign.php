<?php
	require_once("commonCode.php");
	
	$isPI = isPI($_SESSION['user_id']);
	$isAdmin = isAdmin($_SESSION['user_id']);
	initSession();
	
	// fazer check da info enviada, ver se o user logged pode realmente fazer estas operações
	if($_POST['project'] && $_POST['resources']){
		
		exit;
	}
	
	$backLink = "
		<div style='margin:auto;width:200px;text-align:center;'>
			<a class='link' name='back' href='../datumo/'>Back to Admin Area</a>
		</div>
	";
	echo "<html>";
		importJs();
		echo "<script type='text/javascript' src='../agendo/js/jquery-ui-1.8.14.custom.min.js'></script>";
		echo "<link href='../agendo/css/base.css' rel='stylesheet' type='text/css'>";
		echo "<link href='../agendo/css/jquery-ui-1.9.2.custom.css' rel='stylesheet' type='text/css'>";
		echo "<link href='../agendo/css/jquery.multiselect.css' rel='stylesheet' type='text/css'>";
		echo "<script type='text/javascript' src='../agendo/js/jquery.multiselect.js'></script>";
		echo "<link href='../agendo/css/jquery.multiselect.filter.css' rel='stylesheet' type='text/css'>";
		echo "<script type='text/javascript' src='../agendo/js/jquery.multiselect.filter.js'></script>";
		echo "<script type='text/javascript' src='../agendo/js/projAssign.js'></script>";
		
		echo "<body>";
			echo "<br>";
			echo "<h1>Project Assign</h1>";
			
			echo "<div style='width:800px;margin:auto;display:table;text-align:center;'>";
				$dataArray = array();
				$sqlRes = "
					select 
						resourcetype_name, resource_name, resource_id, resource_type 
					from
						resource join resourcetype on resource_type = resourcetype_id ";
				$sqlRes2 = " order by resourcetype_name, resource_name";

				$sqlDep = "
					select
						project_id, project_name, department_id, department_name
					from
						project join department on project_department = department_id ";
				$sqlDep2 = " order by department_name, project_name";
				
				if(!$isAdmin && $isPI === false){
					$dataArray[] = $_SESSION['user_id'];
					$sqlRes .= "join permissions on permissions_user = :0 and resource_id = permissions_resource".$sqlRes2;
					$sqlDep = "select project_id, project_name from project join user on project_department = user_dep where user_id = :0";
				}
				else{
					$sqlRes .= $sqlRes2;
					$sqlDepPart2 = "";
					if($isPI !== false){
						$inData = dbHelp::inDataFromArray($isPI);
						$sqlDepPart2 = "where project_department in ".$inData['inData'];
						$dataArray = $isPI;
					}
				}
				$sqlDep .= $sqlDepPart2;
				
				$prep = dbHelp::query($sqlRes, $dataArray);
				$resourceType = false;
				echo "<div style='float:right;display:table-cell;'>";
					echo "<select id='resourceList' multiple='multiple' style='width:350px;' size='10'>";
						while($row = dbHelp::fetchRowByName($prep)){
							if($row['resource_type'] != $resourceType){
								if($resourceType !== false){
									echo "</optgroup>";
								}
								echo "<optgroup label='".$row['resourcetype_name']."'>";
							}
							$resourceType = $row['resource_type'];
							echo "<option value='".$row['resource_id']."'>".$row['resource_name']."</option>";
						}
						if($resourceType !== false){
							echo "</optgroup>";
						}
					echo "</select>";
				echo "</div>";
				
				$prep = dbHelp::query($sqlDep, $dataArray);
				$projDepartment = false;
				echo "<div style='float:left;display:table-cell;'>";
					echo "<select id='projectList' style='width:350px;' size='10'>";
						while($row = dbHelp::fetchRowByName($prep)){
							if($row['department_id'] != $projDepartment){
								if($projDepartment !== false){
									echo "</optgroup>";
								}
								echo "<optgroup label='".$row['department_name']."'>";
							}
							$projDepartment = $row['department_id'];
							echo "<option value='".$row['project_id']."'>".$row['project_name']."</option>";
						}
						if($projDepartment !== false){
							echo "</optgroup>";
						}
					echo "</select>";
				echo "</div>";
				
				echo "<br>";
				echo "<br>";
				
				if($isAdmin || $isPI){
					echo "<input type='button' value='Assign projects' onclick='assignProjs();' />";
				}
				
			echo "</div>";

			echo "<br>";
			echo $backLink;
		echo "</body>";
	echo "</html>";
?>