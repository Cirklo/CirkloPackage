<?php
	// session_start();
	// $pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
	// $_SESSION['path'] = "../../".$pathOfIndex[sizeof($pathOfIndex)-2];
	// require_once("../../agendo/commonCode.php");
	require_once("commonCode.php");
	
	if(isset($_GET['res']) && isset($_GET['resName']) && isset($_GET['mac'])){
		try{
			$sql = "update resource set resource_mac = '".$_GET['mac']."' where resource_id = '".$_GET['res']."'";
			$res = dbHelp::query($sql);
			echo "The macaddress(".$_GET['mac'].") was associated to ".$_GET['resName'].".";
		}
		catch(Exception $e){
			echo $sql."---".$e->getMessage();
		}
		exit;
	}
	
	importJs(".");
	$path = $_SESSION['path'];
	$color = '#1e4F54';
	echo "<body bgcolor='".$color."'>";
		// echo "<div style='width:100%;margin:auto;'>";
		echo "<div style='text-align:center'>";
			echo "<a href='".$path."' style='color:#F7C439'>Back to reservations</a>";
		echo "</div>";
		
		if(isset($_SESSION['user_id'])){
			$user = $_SESSION['user_id'];
				$sql = "select resource_id, resource_name from resource where resource_status = 3 and resource_resp = ".$user; // user confirmation
				$res = dbHelp::query($sql);
				$resourcesQuantity = dbHelp::numberOfRows($res);
				if($resourcesQuantity != 0){
					echo "<P ALIGN=center>";
					echo "<object type='application/x-java-applet' WIDTH='500' HEIGHT='200' id='zeeApplet'>";
						// echo "<param name='codebase' value = '../../agendo' />";
						echo "<param name='codebase' value = '.' />";
						echo "<param name='archive' value='macApp.jar'/>";
						echo "<param name='code' value='MacAddressApplet'/>";
						echo "<param name='scriptable' value='true'/>";

						echo "<param name='color' value='#1e4F54'/>";
						echo "<param name='url' value='https://localhost/Cirklo/admin/makeConfirmRes.php'/>";
						echo "<param name='action' value='associateRes'/>";
						echo "<param name='numberOfResources' value='".$resourcesQuantity."'/>";
						$i = 0;
						while($arr = dbHelp::fetchRowByIndex($res)){
							echo "<param name='resource".$i."Id' value='".$arr[0]."'/>";
							echo "<param name='resource".$i."Name' value='".$arr[1]."'/>";
							$i++;
						}
						echo "<P>The application was not recognized by the browser<P>";
					echo "</object>";
					echo "</P>";
				}
				else{
					
				}
		}
	echo "</body>";

	// echo "<script type='text/javascript'>
		// $(document).ready(function(){alert('treta');alert(zeeApplet.bla('https://agendo.cirklo.org/demo/teste.php','teste','utf8encodingMyAss'));});
	// </script>";
	
?>