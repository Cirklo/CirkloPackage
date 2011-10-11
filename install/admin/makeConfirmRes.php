<?php
	session_start();
	$pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
	$path = $pathOfIndex[sizeof($pathOfIndex)-2];
	$_SESSION['path'] = "../../".$path;
	require_once("../../agendo/commonCode.php");
	
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
	$color = '#1e4F54';
	importJs();
	echo "<body bgcolor='".$color."'>";
		$sql = "select resource_id, resource_name from resource where resource_status = 3"; // user confirmation
		$res = dbHelp::query($sql);
		$resourcesQuantity = dbHelp::numberOfRows($res);
	
		echo "<P ALIGN=center>";
		echo "<object type='application/x-java-applet' WIDTH='500' HEIGHT='200' id='zeeApplet'>";
			echo "<param name='codebase' value = '../../agendo' />";
			echo "<param name='archive' value='macApp.jar'/>";
			echo "<param name='code' value='MacAddressApplet'/>";
			echo "<param name='scriptable' value='true'/>";

			echo "<param name='color' value='#1e4F54'/>";
			echo "<param name='url' value='https://agendo.cirklo.org/".$path."/admin/makeConfirmRes.php'/>";
			echo "<param name='action' value='associateRes'/>";
			echo "<param name='numberOfResources' value='".$resourcesQuantity."'/>";
			$i = 0;
			while($arr = dbHelp::fetchRowByIndex($res)){
				echo "<param name='resource".$i."Id' value='".$arr[0]."'/>";
				echo "<param name='resource".$i."Name' value='".$arr[1]."'/>";
				$i++;
			}
			echo "<P>I dont know what an applet is<P>";
		echo "</object>";
		echo "</P>";
	
	echo "</body>";

	// echo "<script type='text/javascript'>
		// $(document).ready(function(){alert('treta');alert(zeeApplet.bla('https://agendo.cirklo.org/demo/teste.php','teste','utf8encodingMyAss'));});
	// </script>";
	
?>