<?php
require_once("commonCode.php");
?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/admin.css" rel="stylesheet" type="text/css">
<script type="text/javascript">

function back(){
    history.back();
}
</script>
<?php

// require_once(".htconnect.php");
// require_once("__dbHelp.php");
// require_once("errorHandler.php");
// $error = new errorHandler;

$specialFieldsArray = array(
	'Department' => 'department',
	'Resource' => 'resource',
	'Type' => 'resourcetype'
);

echo "<form method=post name=application action='confirm.php'>";
echo "<table><tr><td><font size=5px>Personal information</font></td></tr>";
echo "<tr><td colspan=2>Please confirm the information below</td></tr></table>";
echo "<br>";
echo "<table border=0>";
//get personal information from previous page form
foreach ($_POST as $key => $value){
	// $key = cleanValue($key);
	// $value = cleanValue($value);
	if(isset($value) && $value != '0' && $value != ''){
		if($key == 'GEDepartment'){
			$key = 'Department';
			// send email
		} //do nothing
		// else{
			// $key = str_replace('_', ' ',$key);
			// if($key == 'Department' or $key == 'Resource' or $key == 'Type'){
		elseif(isset($specialFieldsArray[$key])){
			$name = $specialFieldsArray[$key];
			// $sql = "SELECT ".strtolower($key)."_name FROM ".strtolower($key)." WHERE ".strtolower($key)."_id = $value";
			$sql = "SELECT ".$name."_name FROM ".$name." WHERE ".$name."_id = :0";
			$res = dbHelp::query($sql, array($value)); //mysql_error().$sql); //$sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', '')
			$row = dbHelp::fetchRowByIndex($res);
			$value = $row[0];
		}
		
		echo "<tr><td>$key</td><td><input type=text class=reg name='$key' id='$key' value='$value' readonly=readonly size=35></td></tr>";
	}
    // }
}
echo "</table>";

/*
 echo "<table><tr><td colspan=2>You asked permission to use the following resources:</td></tr></table>";
echo "<table border=0>";
//get resource information from previous page form
foreach ($_POST as $key=>$value){
    if(substr($key, 0, 2) == '__'){
        $key = str_replace('__','',$key);
        $key = str_replace('_', ' ',$key);
        echo "<tr><td><input class=reg type=text name='__$key' id='__$key' value='$key' readonly=readonly size=35></td><td></td></tr>";  
    }
}
echo "</table>";*/
echo "<br>";
echo "<table border=0>";
echo "<tr><td><input type=button value='Go back' onclick=\"javascript:back();\"></td><td align=right><input type=submit value=Confirm></td></tr>";
echo "</table>";
echo "</form>";
?>