<?php
require_once("commonCode.php");

	if(isset($_POST['resources']) && isset($_POST['userLogins']) && (isAdmin($_SESSION['user_id']) || isResp($_SESSION['user_id']) !== false)){
		$json = new stdClass();
		if(!isAdmin($_SESSION['user_id']) && isResp($_SESSION['user_id']) === false){
			throw new Exception("You are not allowed to perform this action");
		}
		
		$resources = $_POST['resources'];
		$userLogins = $_POST['userLogins'];
		$training = $_POST['training'];
		$permLevel = $_POST['permLevel'];
		$sendMails = $_POST['sendMails'];
		
		$error = false;
		$msg = "Access given to users";
		try{
			// for($j = 0; $j < sizeOf($userLogins); $j++){
				// for($i = 0; $i < sizeOf($resources); $i++){
			$sqlMail = "select user_email, resource_name, permlevel_desc from ".dbHelp::getSchemaName().".user, resource, permlevel where user_id = :0 and resource_id = :1 and permlevel_id = :2";
			$replyToPerson = "";
			$replyToPersonMail = "";
			foreach($userLogins as $userId){
				foreach($resources as $resource){
					// If postgre will be used this will most likely not work... at all...
					$sql = "
						REPLACE INTO 
							permissions
						SET 
							permissions_user = :0,
							permissions_resource = :1,
							permissions_level = :2,
							permissions_training = :3
					";
					$prep = dbHelp::query($sql, array($userId, $resource, $permLevel, $training));
					if($sendMails == 'true'){
						$prepMail = dbHelp::query($sqlMail, array($userId, $resource, $permLevel));
						$resMail = dbHelp::fetchRowByIndex($prepMail);
						$subject = "Access changed for resource ".$resMail[1];
						$message = "You now have the access type \"".$resMail[2]."\"";
						$mailObj = getMailObject($subject, $resMail[0], $message, $replyToPerson, $replyToPersonMail);
						// throw exception ? show error message case it fails to send one email?
						sendMailObject($mailObj, false);
					}
				}
			}
		}
		catch(Exception $e){
			$error = true;
			$msg = "Error: ".$e->getMessage();
		}
		
		$json->isError = $error;
		$json->message = $msg;

		echo json_encode($json);
		exit;
	}

	$sql = "select user_id from ".dbHelp::getSchemaName().".user where user_level = 0 and user_id = :0";
	$prepAdmin = dbHelp::query($sql, array($_SESSION['user_id']));

	$sql = "select resource_id from resource where resource_resp = :0";
	$prepManager = dbHelp::query($sql, array($_SESSION['user_id']));

	$sqlPart1 = "select resource_id, resource_name from resource where resource_status not in (0, 2)";
	$sqlPart2 = "order by lower(resource_name)";
	if(dbHelp::numberOfRows($prepAdmin) > 0){ // Check if user is admin
		$prepResources = dbHelp::query($sqlPart1." ".$sqlPart2);
	}
	elseif(dbHelp::numberOfRows($prepManager) > 0){ // Else check if user is a resource manager
		$prepResources = dbHelp::query($sqlPart1." and resource_resp = :0 ".$sqlPart2, array($_SESSION['user_id']));
	}
	else{ // Else its not a special user and shouldnt see the massPassRenewal screen
		// echo "<script type='text/javascript'>window.location='../".$_SESSION['path']."';</script>";
		echo "<script type='text/javascript'>window.location='../datumo/';</script>";
	}
	
	htmlEncoding();
	importJs();
	echo "<link href='css/intro.css' rel='stylesheet' type='text/css' />";
	echo "<script type='text/javascript' src='js/givePermission.js'></script>";

	echo "<br>";
	echo "<h1 style='text-align:center;color:#F7C439;'>Resource Permission</h1>";
	
	$commonSelectStyle = "width:200px;";
	$commonSelectSize = 10;
	echo "<table id='main' style='margin:auto;width:600px;text-align:center;'>";
		echo "<tr>";
			echo "<td>";
				echo "<h3 style='color:white;'>Pick Users from:</h3>";
				$userQuery = "select user_id, user_firstname, user_lastname, user_login from ".dbHelp::getSchemaName().".user order by lower(user_firstname), lower(user_lastname), lower(user_login)";
				$prep = dbHelp::query($userQuery);
				echo "<select multiple='multiple' size='".$commonSelectSize."' id='fromSelect' style='".$commonSelectStyle."'>";
					while($row = dbHelp::fetchRowByIndex($prep)){
						echo "<option value='".$row[0]."'>".$row[1]." ".$row[2]." (".$row[3].")</option>";
					}
				echo "</select>";
			echo "</td>";
		
			$margin = 6;
			echo "<td>";
				echo "<div>";
					echo "<input type='button' value=' <<- ' title='Remove all' onclick='swapAll(\"toSelect\", \"fromSelect\");'/>";
					// echo "<img style='margin-right:".$margin."px;margin-bottom:".$margin."px;' src='".$_SESSION['path']."/pics/double_arrow_button_left.png' title='Remove all' onclick='swapAll(\"toSelect\", \"fromSelect\");'/>";
					echo "&nbsp";
					echo "<input type='button' value=' ->> ' title='Add all' onclick='swapAll(\"fromSelect\", \"toSelect\");'/>";
					// echo "<img style='margin-left:".$margin."px;margin-bottom:".$margin."px;' src='".$_SESSION['path']."/pics/double_arrow_button_right.png' title='Add all' onclick='swapAll(\"fromSelect\", \"toSelect\");'/>";
					echo "<br>";
					echo "<input type='button' value=' <- ' title='Remove selected' onclick='swapSelected(\"toSelect\", \"fromSelect\");'/>";
					// echo "<img style='margin-right:".$margin."px;' src='".$_SESSION['path']."/pics/single_arrow_button_left.png' title='Remove selected' onclick='swapSelected(\"toSelect\", \"fromSelect\");'/>";
					echo "&nbsp";
					echo "<input type='button' value=' -> ' title='Add selected' onclick='swapSelected(\"fromSelect\", \"toSelect\");'/>";
					// echo "<img style='margin-left:".$margin."px;' src='".$_SESSION['path']."/pics/single_arrow_button_right.png' title='Add selected' onclick='swapSelected(\"fromSelect\", \"toSelect\");'/>";
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
				// echo "<div style='float:left'>";
				echo "<div>";
					echo "<h3 style='color:white;'>Select resource(s):</h3>";
					echo "<select multiple='multiple' size='6' id='resourcesSelect' style='width:250px'>";
						while($row = dbHelp::fetchRowByIndex($prepResources)){
							echo "<option value='".$row[0]."'>".$row[1]."</option>";
						}
					echo "</select>";
				echo "</div>";

				echo "<div style='display:table;margin:auto;'>";
					echo "<div style='display:table-cell;'>";
						echo "<h3 style='color:white;'>Access level:</h3>";
						$sql = "select permlevel_id, permlevel_desc from permlevel";
						$prep = dbHelp::query($sql);
						echo "<select size='1' id='permLevelSelect' style='width:150px'>";
							while($row = dbHelp::fetchRowByIndex($prep)){
								echo "<option value='".$row[0]."'>".$row[1]." ".$row[2]."</option>";
							}
						echo "</select>";
					echo "</div>";
					
					echo "&nbsp";
					
					echo "<div style='display:table-cell;'>";
						echo "<h3 style='color:white;'>Training:</h3>";
						echo "<select size='1' id='trainingSelect' style='width:150px'>";
							echo "<option value='0'>No</option>";
							echo "<option value='1'>Yes</option>";
						echo "</select>";
					echo "</div>";
				echo "</div>";
				
				echo "<br>";
				
				echo "<label style='color:white;'>";
					echo "Email users";
					echo "<input type='checkbox' id='emailCheck' />";
				echo "</label>";
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
				// echo "<a style='color:#F7C439;' onmouseover=\"this.style.color='#FFFFFF'\" onmouseout=\"this.style.color='#F7C439'\" href='".$_SESSION['path']."/'>Back to Admin Area</a>";
				echo "<a style='color:#F7C439;' onmouseover=\"this.style.color='#FFFFFF'\" onmouseout=\"this.style.color='#F7C439'\" href='../datumo/'>Back to Admin Area</a>";
			echo "</td>";
		echo "</tr>";
	echo "</table>";
?>