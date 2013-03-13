<?php
require_once("commonCode.php");

echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
echo "<link href='css/admin.css' rel='stylesheet' type='text/css'>";
echo "
<script>
	function back(){
		history.back();
	}
</script>
";

$specialFieldsArray = array(
	'Department' => 'department',
	'Resource' => 'resource',
	'Type' => 'resourcetype'
);

// $idArray = array();
$departmentId = "";
$getData = "?resource=".$_POST['Resource'];
if(
	isset($_POST['Department']) && $_POST['Department'] != '' && $_POST['Department'] != '0'
	&& isset($_POST['Resource']) && $_POST['Resource'] != '' && $_POST['Resource'] != '0'
){
	$getData = "?department=".$_POST['Department']."&resource=".$_POST['Resource'];
}

echo "<form method=post name=application action='confirm.php".$getData."'>";
	echo "<table><tr><td><font size=5px>Personal information</font></td></tr>";
	echo "<tr><td colspan=2>Please confirm the information below</td></tr></table>";

	echo "<br>";
	echo "<table border=0>";
		//get personal information from previous page form
		foreach ($_POST as $key => $value){
			if(isset($value) && $value != '0' && $value != ''){
				if($key == 'GEDepartment'){
					$key = 'Department';
				}
				elseif(isset($specialFieldsArray[$key])){
					// this is terrible, but it was either to work around the original code or redo everything, so terrible it stays
					// $idArray[$specialFieldsArray[$key]] = $value;
					$name = $specialFieldsArray[$key];
					$sql = "SELECT ".$name."_name FROM ".$name." WHERE ".$name."_id = :0";
					$res = dbHelp::query($sql, array($value)); //mysql_error().$sql); //$sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', '')
					$row = dbHelp::fetchRowByIndex($res);
					$value = $row[0];
				}
				
				echo "<tr><td>$key</td><td><input type=text class=reg name='$key' id='$key' value='$value' readonly=readonly size=35></td></tr>";
			}
		}
	echo "</table>";

	echo "<br>";
	
	// echo "<div>";
		// echo "<input type='button' value='Go back' onclick='back();' />";
		// echo "<input type='button' value='Confirm' onclick='confirm_data(\"".$idArray[$specialFieldsArray['Department']]."\", \"".$idArray[$specialFieldsArray['Resource']]."\");' />";
	// echo "</div>";
	echo "<table border=0>";
		echo "<tr><td><input type=button value='Go back' onclick='back();'></td><td align=right><input type=submit value=Confirm></td></tr>";
	echo "</table>";
echo "</form>";
?>