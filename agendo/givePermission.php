<?php
require_once("commonCode.php");

	if(isset($_POST['resources']) && isset($_POST['userLogins'])){
		$resources = $_POST['resources'];
		$userLogins = $_POST['userLogins'];
		$training = $_POST['training'];
		$permLevel = $_POST['permLevel'];
		
		$error = false;
		$msg = "Access given to users";
		try{
			for($j = 0; $j < sizeOf($userLogins); $j++){
				for($i = 0; $i < sizeOf($resources); $i++){
					// If postgre will be used this will most likely not work... at all...
					$sql = "REPLACE 
							INTO 
								permissions
							SET 
								permissions_user = :0,
								permissions_resource = :1,
								permissions_level = :2,
								permissions_training = :3";
					$prep = dbHelp::query($sql, array($userLogins[$j], $resources[$i], $permLevel, $training));
				}
			}
		}
		catch(Exception $e){
			$error = true;
			$msg = "Error: ".$e->getMessage();
		}
		
		$json->error = $error;
		$json->msg = $msg;

		echo json_encode($json);
		exit;
	}

	$sql = "select user_id from ".dbHelp::getSchemaName().".user where user_level = 0 and user_id = :0";
	$prepAdmin = dbHelp::query($sql, array($_SESSION['user_id']));

	$sql = "select resource_id from resource where resource_resp = :0";
	$prepManager = dbHelp::query($sql, array($_SESSION['user_id']));

	if(dbHelp::numberOfRows($prepAdmin) > 0){ // Check if user is admin
		$sql = "select resource_id, resource_name from resource";
		$prepResources = dbHelp::query($sql);
	}
	elseif(dbHelp::numberOfRows($prepManager) > 0){ // Else check if user is a resource manager
		$sql = "select resource_id, resource_name from resource where resource_resp = :0";
		$prepResources = dbHelp::query($sql, array($_SESSION['user_id']));
	}
	else{ // Else its not a special user and shouldnt see the massPassRenewal screen
		echo "<script type='text/javascript'>window.location='../".$_SESSION['path']."';</script>";
	}
	
	importJs();
	echo "<link href='css/intro.css' rel='stylesheet' type='text/css' />";
	echo "<script type='text/javascript' src='js/givePermission.js'></script>";

	echo "<br>";
	echo "<h1 style='text-align:center;color:#F7C439;'>Resource Access</h1>";
	
	$commonSelectStyle = "width:150px;";
	$commonSelectSize = 10;
	echo "<table id='main' style='margin:auto;width:500px;text-align:center;'>";
		echo "<tr>";
			echo "<td>";
				echo "<h3 style='color:white;'>Pick Users from:</h3>";
				$userQuery = "select user_id, user_firstname, user_lastname from ".dbHelp::getSchemaName().".user where user_id != :0";
				$prep = dbHelp::query($userQuery, array($_SESSION['user_id']));
				echo "<select multiple='multiple' size='".$commonSelectSize."' id='fromSelect' style='".$commonSelectStyle."'>";
					while($row = dbHelp::fetchRowByIndex($prep)){
						echo "<option value='".$row[0]."'>".$row[1]." ".$row[2]."</option>";
					}
				echo "</select>";
			echo "</td>";
		
			echo "<td>";
				echo "<div>";
					echo "<input type='button' value=' <<- ' title='Remove all' onclick='swapAll(\"toSelect\", \"fromSelect\");'/>";
					echo "&nbsp";
					echo "<input type='button' value=' ->> ' title='Add all' onclick='swapAll(\"fromSelect\", \"toSelect\");'/>";
					echo "<br>";
					echo "<input type='button' value=' <- ' title='Remove selected' onclick='swapSelected(\"toSelect\", \"fromSelect\");'/>";
					echo "&nbsp";
					echo "<input type='button' value=' -> ' title='Add selected' onclick='swapSelected(\"fromSelect\", \"toSelect\");'/>";
				echo "</div>";
			echo "</td>";
		
			echo "<td>";
				echo "<h3 style='color:white;'>Give access to:</h3>";
				echo "<select multiple='multiple' size='".$commonSelectSize."' id='toSelect' style='".$commonSelectStyle."'>";
				echo "</select>";
			echo "</td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td colspan='3'>";
				echo "<div style='float:left'>";
					echo "<h3 style='color:white;'>Select resource(s):</h3>";
					echo "<select multiple='multiple' size='6' id='resourcesSelect' style='width:250px'>";
						while($row = dbHelp::fetchRowByIndex($prepResources)){
							echo "<option value='".$row[0]."'>".$row[1]."</option>";
						}
					echo "</select>";
				echo "</div>";

				echo "<div style='float:right'>";
					echo "<h3 style='color:white;'>Access level:</h3>";
					$sql = "select permlevel_id, permlevel_desc from permlevel";
					$prep = dbHelp::query($sql);
					echo "<select size='1' id='permLevelSelect' style='width:150px'>";
						while($row = dbHelp::fetchRowByIndex($prep)){
							echo "<option value='".$row[0]."'>".$row[1]." ".$row[2]."</option>";
						}
					echo "</select>";

					echo "<h3 style='color:white;'>Training:</h3>";
					echo "<select size='1' id='trainingSelect' style='width:150px'>";
						echo "<option value='0'>No</option>";
						echo "<option value='1'>Yes</option>";
					echo "</select>";

				echo "</div>";
			echo "</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td colspan='3'>";
				echo "<br>";
				echo "<input type='button' value='Give access' onclick='sendUserAndResourceList();'/>";
			echo "</td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td colspan='3'>";
				echo "<br>";
				echo "<a style='color:white;' onmouseover=\"this.style.color='#F7C439'\" onmouseout=\"this.style.color='#FFFFFF'\" href='".$_SESSION['path']."/'>Back to reservations</a>";
			echo "</td>";
		echo "</tr>";
	echo "</table>";
?>