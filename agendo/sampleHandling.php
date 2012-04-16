<?php
	require_once("commonCode.php");
	
	// ******************
	// Gets or Posts here
	try{
		// This flag controls if the error data is sent to the client on page generation or sent to JS by reply
		$dontEchoThis = isAjax();


		// ***************************
		// Requests made by the client
		if(isset($_POST['action'])){
			if(!isset($_POST['resource'])){
				throw new Exception('Resource not specified');
			}
			
			$userId = getUserId($_POST['userLogin'], $_POST['userPass']);
			// Need this comparison in case $userId = 0.... Thats php for you...
			if($userId === false){
				throw new Exception('Wrong username or password');
			}
		
			if(!hasPermission($userId, $resource) && isResp($userId, $resource) !== false){ // dont allow resp to add samples? allow resp to add samples in someones name?
				throw new Exception('User is not allowed to add samples');
			}
		
			switch($_POST['action']){
				case "sampleInsertHtml": // Regular user view
					$html = sampleInsertHtml($userId, $_POST['resource']);
					$json->html = $html;
				break;
				case "sampleInsert":
					if(isset($_POST['sampleName'])){
						$json->message = sampleInsert($_POST['sampleName'], $userId, $_POST['resource']);
					}
				break;
			}
			$json->success = true;
			echo json_encode($json);
			exit;
		}
		
		// *************************
		// Html generation goes here
		echo "<link href='../agendo/css/sample.css' rel='stylesheet' type='text/css' />";
		echo "<script type='text/javascript' src='../agendo/js/sample.js'></script>";
		
		echo "<div id='sampleInterfaceDiv'>";
		echo "</div>";
	}
	catch(Exception $e){
		if($dontEchoThis){
			$json->success = false;
			$json->message = "Error: ".$e->getMessage();
			echo json_encode($json);
		}
		else{
			showMsg($e->getMessage(), true, true);
		}
	}
	

	// *********
	// Functions
	function sampleInsertHtml($userId, $resource){
		$html = "";
		$sql = "select resource_name from resource where resource_id = :0";
		$prep = dbHelp::query($sql, array($resource));
		$row = dbHelp::fetchRowByIndex($prep);
		
		$html .= "<a onclick='closeSampleInsertDiv();' onmouseover='this.style.cursor=\"pointer\"' style='position:absolute;top:0px;right:5px;color:#bb3322;font-size:16px;'>";
			$html .= "x";
		$html .= "</a>";
		
		$html .= "<label style='float:left;'>";
			$html .= "New sample for ".$row[0].": ";
		$html .= "</label>";
		
		$html .= "<br>";
		
		$html .= "<input type='text' id='sampleName' style='float:left;width:200px;'/>";
		$html .= "
			<input type='button' 
				id='insertSampleButton' 
				value='Insert Sample' 
				onclick='sampleInsert(".$resource.")' 
				style='float:right;width:120px;' 
				title='Inserts the new sample'
			/>
		";
	
		$html .= "<br>";
		$html .= "<br>";
		
		$html .= "<label style='float:left;'>";
			$html .= "Submitted samples not yet used: ";
		$html .= "</label>";

		$html .= "<br>";
		
		$stateColorArray = array(1 => 'green', 2 => 'red'); // change this to a setting on the css?
		$html .= "<select id='submittedSamples' size='10' style='width:200px;float:left;'>";
		$sql = "select sample_id, sample_name, sample_state from sample where sample_resource = :0 and sample_user = :1 and sample_state in (1, 2)";
		$prep = dbHelp::query($sql, array($resource, $user));
		while($row = dbHelp::fetchRowByIndex($prep)){
			// switch($row[2]){
				// case 1: // Available
					$html .= "<option style='background-color:".$stateColorArray[$row[2]].";' value='".$row[0]."'>".$row[1]."</option>";
				// break;
				// case 2: // In use
					// $html .= "<option >";
				// break;
			// }
		}
		$html .= "</select>";
		$html .= "
			<input type='button' 
				id='removeSampleButton' 
				value='Remove Sample' 
				onclick='alert(\"Hey, i work!!\");' 
				style='float:right;width:120px;' 
				title='Removes the selected sample(s)'
			/>
		";
		
		return $html;
	}
	
	function sampleInsert($sampleName, $userId, $resource){
		$sql = "insert into sample values(null, :0, :1, 1, :2)";
		$prep = dbHelp::query($sql, array($sampleName, $userId, $resource));
		return "Sample inserted";
	}
	
	
	// Returns a user id either from the session var or from the login and password text fields
	function getUserId($userLogin, $userPass){
		if(isset($_SESSION['user_id'])){
			return $_SESSION['user_id'];
		}
		
		$sql = "select user_id from ".dbHelp::getSchemaName().".user where user_login = :0 and user_passwd = :1";
		$prep = dbHelp::query($sql, array($userLogin, cryptPassword($userPass)));
		if(isset($userLogin) && isset($userPass) && dbHelp::numberOfRows($prep) > 0){
			$row = dbHelp::fetchRowByIndex($prep);
			return $row[0];
		}
		
		return false;
	}
?>