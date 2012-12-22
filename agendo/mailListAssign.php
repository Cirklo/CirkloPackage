<?php
	require_once("commonCode.php");

	$backLink = "
		<div style='margin:auto;width:200px;text-align:center;'>
			<a class='link' name='back' href='../datumo/'>Back to Admin Area</a>
		</div>
	";
	echo "<html>";
		importJs();
		echo "<script type='text/javascript' src='../agendo/js/mailListAssign.js'></script>";
		
		echo "<body>";
			echo "<br>";
			echo "<h1>Mailing list assign</h1>";
			
			echo "<div style='width:800px;margin:auto;display:table;text-align:center;'>";
				$dataArray = array();
				$sql = "
					select 
						permissions_sendmail, permissions_resource 
					from
						permissions
					where
						permissions_user = :0
				";
				
				$prep = dbHelp::query($sql, array($_SESSION['user_id']));
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