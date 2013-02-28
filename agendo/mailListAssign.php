<?php
	require_once("commonCode.php");

	if(isset($_POST['functionName'])){
		call_user_func($_POST['functionName']);
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
		echo "<link href='../agendo/css/mailListAssign.css' rel='stylesheet' type='text/css'>";
		echo "<script type='text/javascript' src='../agendo/js/mailListAssign.js'></script>";
		
		echo "<body>";
			echo "<br>";
			echo "<h1>Mailing list assign</h1>";
			echo "<br>";
			echo "<h3>Check each resource box to receive email notifications of deleted or updated entries on the corresponding resource</h3>";
			echo "<br>";
			// echo "<div id='container' style='width:300px;margin:auto;text-align:left;border: 1px solid white;height:250px;'>";
			echo "<div id='container'>";
				$dataArray = array();
				$sql = "
					select 
						permissions_sendmail, resource_id, resource_name 
					from
						permissions	join resource on resource_id = permissions_resource
					where
						permissions_user = :0
					order by
						resource_name
				";
				
				$prep = dbHelp::query($sql, array($_SESSION['user_id']));
				$resourceType = false;
				
				echo "<ul id='resourceList' style='width:100%;height: 100%;overflow:auto;list-style:none;margin:0px;padding:0px;'>";
					while($row = dbHelp::fetchRowByName($prep)){
						$checked = "";
						if($row['permissions_sendmail'] == 1){
							$checked = "checked";
						}
						echo "<li style='padding: 5px;'>";
							echo "<label>";
								echo "<input type='checkbox' value='".$row['resource_id']."' ".$checked." onclick='mailListCheck(this);'/>";
								echo $row['resource_name'];
							echo "</label>";
						echo "</li>";
					}
				echo "</ul>";
				
				// echo "<div class='clear'></div>";
			echo "</div>";

			echo "<br>";
			echo $backLink;
		echo "</body>";
	echo "</html>";
	
	
	// functions **************************************************
	function mailListCheck(){
		if(isset($_SESSION['user_id']) && isset($_POST['resource']) && isset($_POST['check'])){
			if($_POST['check'] == 'false'){
				$value = 0;
				$message = 'User removed from mailing list';
				$check = false;
			}
			else{
				$value = 1;
				$message = 'User inserted to mailing list';
				$check = true;
			}
			$sql = "update permissions set permissions_sendmail = ".$value." where permissions_user = :0 and permissions_resource = :1";
			$prep = dbHelp::query($sql, array($_SESSION['user_id'], $_POST['resource']));
			$json = new stdClass();
			$json->message = $message;
			$json->check = $value;
			echo json_encode($json);
		}
	}
	

?>