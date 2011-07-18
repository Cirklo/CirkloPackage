<?php
    // $res_id=$_GET['resourceStatus'];
	
	// remove cookie
	// setcookie ("customInterface", "", time() - 3600);
	// echo "cookie removed";
	
    // 5 year cookie
	// needs the room value to be added here
	// sql = "select resinterface_room from resinterface";
	// make interface to add room and php interface file
    // setcookie('customInterface', 1, time() + 157680000,'/');
	// echo "Cookie is set";
	
	
session_start();
$pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
$_SESSION['path'] = "../../".$pathOfIndex[sizeof($pathOfIndex)-2];
require_once("../../agendo/commonCode.php");
echo "<script type='text/javascript' src='../../agendo/js/jquery-1.5.2.min.js'></script>";
echo "<script type='text/javascript' src='../../agendo/js/commonCode.js'></script>";
echo "<link type='text/css' href='../../agendo/css/jquery.jnotify.css' rel='stylesheet' media='all' />";
echo "<script type='text/javascript' src='../../agendo/js/jquery.jnotify.js'></script>";
echo "<link href='../../agendo/css/tipTip.css' rel='stylesheet' type='text/css'>";
echo "<script type='text/javascript' src='../../agendo/js/jquery.tipTip.js'></script>";
echo "<link href='tablet.css' rel='stylesheet' type='text/css'>";
echo "<script type='text/javascript' src='tablet.js'></script>";


	echo "<div style='text-align:center;margin:auto;padding:70px;'>";
	$cookieName = 'customInterface';
	try{
			$sql = "select * from interfacerooms";
			$res = dbHelp::mysql_query2($sql);
			if(dbHelp::mysql_numrows2($res) > 0){
					$id = 'roomsList';
					echo "<select id='".$id."'>";
						echo "<option selected value=''>Please select a room</option>";
						while($arr=dbHelp::mysql_fetch_row2($res)){
							echo "<option value=".$arr[0].">".$arr[1]."</option>";
						}
					echo "</select>";
					echo "<input type='button' value='MakeCookie' onclick=\"setCookie('".$cookieName."','".$id."')\" />";
			}
			else{
				throw new Exception("No available rooms to pick from.");
			}
	}
	catch(Exception $e){
		showMsg($e->getMessage());
	}
	echo "<input type='button' value='RemoveCookie' onclick=\"removeCookie('".$cookieName."')\" />";
	echo "<div>";
?>