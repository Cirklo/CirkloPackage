<?php
	require_once("commonCode.php");
	
	$isPI = isPI($_SESSION['user_id']);
	$isAdmin = isAdmin($_SESSION['user_id']);
	initSession();
	
	if($isPI === false && $isAdmin === false){
		throw new Exception("You are not allowed to make changes on this page");
	}
	
	if(isAjax()){
		if($_POST['activeMatrix']){
			$activeMatrix = json_decode($_POST['activeMatrix'], true);
			$sql = "update proj_dep_assoc set proj_dep_assoc_active = :0 where proj_dep_assoc_department = :1 and proj_dep_assoc_project = :2";
			foreach($activeMatrix as $department=>$projectArray){
				foreach($projectArray as $project=>$value){
					dbHelp::query($sql, array((int)$value, $department, $project));
				}
			}
		}

		if($_POST['defaultArray']){
			$defaultArray = $_POST['defaultArray'];
			$sql = "update department set department_default = :0 where department_id = :1";
			foreach($defaultArray as $department=>$project){
				dbHelp::query($sql, array($project, $department));
			}
		}
		
		$json->message = "Changes were saved";
		echo json_encode($json);
		exit;
	}
	
	$backLink = "
		<div style='margin:auto;width:200px;text-align:center;'>
			<a class='link' name='back' href='../datumo/'>Back to Admin Area</a>
		</div>
	";
	
	echo "<html>";
		importJs();
		echo "<link href='../agendo/css/base.css' rel='stylesheet' type='text/css'>";
		echo "<script type='text/javascript' src='../agendo/js/projAssign.js'></script>";
		echo "<link href='../agendo/css/projAssign.css' rel='stylesheet' type='text/css'>";
		
		echo "<body>";
			echo "<br>";
			echo "<h1>Project Settings</h1>";
			echo "<br>";
			
			echo "<div id='container' style='margin:auto;display:table;'>";
				$dataArray = array();
				$sqlDep1 = "
					select
						department_id, department_name, project_id, project_name, proj_dep_assoc_active, department_default
					from
						department
						join proj_dep_assoc on department_id = proj_dep_assoc_department
						join project on project_id = proj_dep_assoc_project
				";
				$sqlDep2 = "order by department_name, project_name";
				if($isAdmin === false){
					$sqlDep1 .= "department_manager = :0 ";
					$dataArray[] = $_SESSION['user_id']; 
				}
				$sqlDep1 .= $sqlDep2;
				
				$prep = dbHelp::query($sqlDep1, $dataArray);
				$previousDepartment = "";
				// get the first row
				$row = dbHelp::fetchRowByName($prep);
				echo "<table id='header'>";
					echo "<tr>";
						echo "<td>";
							echo "<label>";
								echo "Department name";
							echo "</label>";
						echo "</td>";

						echo "<td class='defaultColumn'>";
							echo "<label>";
								echo "Default";
							echo "</label>";
						echo "</td>";

						echo "<td class='activeColumn'>";
							echo "<label>";
								echo "Active";
							echo "</label>";
						echo "</td>";
					echo "</tr>";
				echo "</table>";
				
				while($row){
					if($previousDepartment != $row['department_id']){
						echo "<table id='dep-".$row['department_id']."' class='department' >";
							echo "<tr>";
								echo "<td>";
									echo "<label>";
										echo $row['department_name'];
									echo "</label>";
								echo "</td>";
								
								echo "<td colspan='2' class='all'>";
									echo "<label>";
										echo "All ";
										echo "<input class='checkBox' type='checkbox' onchange='selectAllFromDep(".$row['department_id'].", this.checked);'/>";
									echo "</label>";
								echo "</td>";
							echo "</tr>";
							
								// first project
								echo projElement($row['project_name'], $row['department_id'], $row['project_id'], $row['proj_dep_assoc_active'], $row['department_default']);
								// rest of them
								$previousDepartment = $row['department_id'];
								while(($row = dbHelp::fetchRowByName($prep)) && $previousDepartment == $row['department_id']){
									echo projElement($row['project_name'], $row['department_id'], $row['project_id'], $row['proj_dep_assoc_active'], $row['department_default']);
								}
						echo "</table>";
					}
				}
				
			echo "</div>";

			echo "<br>";

			echo "<div style='margin:auto;width:200px;text-align:center;'>";
				echo "<input type='button' title='Saves all the projects active state and their default states' value='Save changes' onclick='saveData();' />";
			echo "</div>";

			echo "<br>";
			echo "<br>";
			
			echo $backLink;
		echo "</body>";
	echo "</html>";
	
	function projElement($name, $dep_id, $proj_id, $projIsActive, $projIsDefault){
		$default = "";
		if($projIsDefault && $projIsDefault == $proj_id){
			$default = "checked='checked'";
		}
		
		$active = "";
		if($projIsActive == 1){
			$active = "checked='checked'";
		}

		echo  "<tr class='listElement'>";
			echo "<td style='text-align:left;'>";
				echo "<label>";
					echo $name;
				echo "</label>";
			echo "</td>";

			echo "<td class='defaultColumn'>";
				echo "<label>";
					echo "<input id='".$dep_id."-".$proj_id."-radio' name='".$dep_id."' value='' type='radio' onclick='changeDefault(".$dep_id.", ".$proj_id.");' ".$default." />";
				echo "</label>";
			echo "</td>";
				
			echo "<td class='activeColumn'>";
				echo "<label>";
				echo "<input id='".$dep_id."-".$proj_id."-check' type='checkbox' value='".$proj_id."' onchange='sendCheckedToArray(".$dep_id.", this.value, this.checked);'/>";
				echo "</label>";
			echo "</td>";
		echo "</tr>";
	}
?>