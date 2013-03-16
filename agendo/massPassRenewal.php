<?php
require_once("commonCode.php");

	if(isset($_POST['userId']) && isset($_POST['userLogins'])){
		$json = new stdClass();
		try{
			if(!isAdmin($_SESSION['user_id']) && isResp($_SESSION['user_id']) === false){
				throw new Exception("You are not allowed to perform this action");
			}
			
			require_once("alertClass.php");
			$alert = new alert();

			$passRequester = $_POST['userId'];
			$userLogins = $_POST['userLogins'];
			
			// Ignore echos
			ob_start();
			$error = false;
			$user = $userLogins[$i];
			for($i = 0; $i < sizeOf($userLogins); $i++){
				$error = !$alert->recover($userLogins[$i], $passRequester);
				if($error){
					break;
				}
			}
			ob_end_clean();
			// Stop ignoring echos

			$json->error = $error;
			if($error){
				$json->msg = "An error occurred while reseting the password for user: ".$user;
			}
			else{
				$json->msg = "Emails sent";
			}
		}
		catch(Exception $e){
			$json->error = true;
			$json->msg = "Error: ".$e->getMessage();
		}
		
		echo json_encode($json);
		exit;
	}

	importJs();
	echo "<link href='css/intro.css' rel='stylesheet' type='text/css' />";
	echo "<script type='text/javascript' src='js/massPassRenewal.js'></script>";

	echo "<br>";
	echo "<h1 style='text-align:center;color:#F7C439;'>Multiple Password Reset</h1>";
	echo "<br>";
	
	$sql = "select user_id from ".dbHelp::getSchemaName().".user where user_level = 0 and user_id = :0";
	$prepAdmin = dbHelp::query($sql, array($_SESSION['user_id']));

	$sql = "select resource_id from resource where resource_resp = :0";
	$prepManager = dbHelp::query($sql, array($_SESSION['user_id']));

	if(dbHelp::numberOfRows($prepAdmin) > 0){ // Check if user is admin
		$userQuery = "select user_login, user_firstname, user_lastname from ".dbHelp::getSchemaName().".user where user_id != :0 order by lower(user_firstname), lower(user_lastname)";
	}
	elseif(dbHelp::numberOfRows($prepManager) > 0){ // Else check if user is a resource manager
		$row = dbHelp::fetchRowByIndex($prepManager);
		$resList = $row[0];
		while($row = dbHelp::fetchRowByIndex($prepManager)){
			$resList .= ",".$row[0];
		}
		$userQuery = "select user_login, user_firstname, user_lastname from ".dbHelp::getSchemaName().".user , permissions where permissions_resource in (".$resList.") and permissions_user = user_id and user_id != :0 group by user_login order by lower(user_firstname), lower(user_lastname), lower(user_login)";
	}
	else{ // Else its not a special user and shouldnt see the massPassRenewal screen
		echo "<script type='text/javascript'>window.location='../".$_SESSION['path']."';</script>";
	}
	
	$commonSelectStyle = "width:200px;";
	$commonSelectSize = 10;
	echo "<table id='main' style='margin:auto;width:600px;text-align:center;'>";
		echo "<tr>";
			echo "<td>";
				echo "<h3 style='color:white;'>Pick Users from:</h3>";
				$prep = dbHelp::query($userQuery, array($_SESSION['user_id']));
				echo "<select multiple='multiple' size='".$commonSelectSize."' id='fromSelect' style='".$commonSelectStyle."'>";
					while($row = dbHelp::fetchRowByIndex($prep)){
						echo "<option value='".$row[0]."' title='".$row[1]." ".$row[2]."'>".$row[1]." ".$row[2]." (".$row[0].")</option>";
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
				echo "<h3 style='color:white;'>Send password to:</h3>";
				echo "<select multiple='multiple' size='".$commonSelectSize."' id='toSelect' style='".$commonSelectStyle."'>";
				echo "</select>";
			echo "</td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td colspan='3'>";
				echo "<br>";
				echo "<input type='button' value='Send password(s)' onclick='sendUserList(".$_SESSION['user_id'].");'/>";
			echo "</td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td colspan='3'>";
				echo "<br>";
				// echo "<a style='color:#F7C439;' onmouseover=\"this.style.color='#FFFFFF'\" onmouseout=\"this.style.color='#F7C439'\" href='".$_SESSION['path']."/'>Back to reservations</a>";
				echo "<a style='color:#F7C439;' onmouseover=\"this.style.color='#FFFFFF'\" onmouseout=\"this.style.color='#F7C439'\" href='../datumo/'>Back to Admin Area</a>";
			echo "</td>";
		echo "</tr>";
	echo "</table>";
?>