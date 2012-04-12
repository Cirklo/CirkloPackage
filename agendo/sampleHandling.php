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
		
			switch($_POST['action']){
				case "sampleInsertHtml": // Regular user view
					$html = sampleInsertHtml($_POST['resource'], $_POST['userLogin'], $_POST['userPass']);
					$json->html = $html;
				break;
				case "insertSample":
					if(isset($_POST['sampleName'])){
						sampleInsert($_POST['resource'], $_POST['sampleName']);
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
		
		echo "<div id='sampleInterfaceDiv' style='display:none;background:white;width:340px;margin:auto;position:absolute;border:2px solid black;padding:20px;'>";
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
	function sampleInsertHtml($resource, $userLogin, $userPass){
		if(isset($_SESSION['user_id'])){
			$userId = $_SESSION['user_id'];
		}
		elseif(isset($userLogin) && isset($userPass)){
			$sql = "select user_id from ".dbHelp::getSchemaName().".user where user_login = :0 and user_passwd = :1";
			$prep = dbHelp::query($sql, array($userLogin, cryptPassword($userPass)));
			if(dbHelp::numberOfRows($prep) > 0){
				$row = dbHelp::fetchRowByIndex($prep);
				$userId = $row[0];
			}
			else{
				throw new Exception('Wrong user or password');
			}
		}
		else{
			throw new Exception('User not specified');
		}
		
		if(!hasPermission($userId, $resource) && isResp($userId, $resource) !== false){
			throw new Exception('User is not allowed to add samples');
		}
		
		$html = "";
		$sql = "select resource_name from resource where resource_id = :0";
		$prep = dbHelp::query($sql, array($resource));
		$row = dbHelp::fetchRowByIndex($prep);
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
	
	function sampleInsert(){
		
	}
?>