<?php

require_once("session.php");
$user_id = startSession();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/main.css" rel="stylesheet" type="text/css">
<link href="css/autoSuggest.css" rel="stylesheet" type="text/css">
<link href="css/tipTip.css" rel="stylesheet" type="text/css">
<link href="css/styles.css" rel="stylesheet" type="text/css">
<link href="css/jquery.jnotify.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/jquery.init.js"></script>
<script type="text/javascript" src="js/jquery.tipTip.js"></script>
<script type="text/javascript" src="js/jquery.jnotify.js"></script>
<script type="text/javascript" src="js/filters.js"></script>
<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript">

function imageValidation(){
	if($("#resource").val()==0){
		alert("You must select one resource to proceed");
		return;
	}
	if($("#file").val()==""){
		alert("You must enter a valid path to proceed");
		return;
	}
	CurForm=eval("document.upload");
	CurForm.action="functions.php?type=0";
	CurForm.submit();
}


</script>


<?php

/** @author João Lagarto
 * @copyright João Lagarto 2010
 * @version Requisition System 2.0
 * @license EUPL
 * @abstract Script to handle baskets depending on the basket type
 */
error_reporting(1);

require_once ("__dbConnect.php");
require_once ("dispClass.php");
require_once ("treeClass.php");
require_once ("mailClass.php");
require_once ("configClass.php");
require_once "resClass.php";

//call database class (handle connections)
$conn = new dbConnection(); 

//other classes
$admin = new restrictClass();
$display = new dispClass();
$treeview = new treeClass();
$mail = new mailClass();
$config = new configClass();
$perm=new restrictClass();
//$msg = new msgClass();

//set variables
$contact = "Do you want to report a bug? Please submit the form.";

//set local variables 
$arr=array();

//main table
$options = array("Options","Resource image uploader");
echo "<h2>Datumo Administration Area</h2>";
//display page options
echo "<table border=0 class=admin >";
$display->options($options);
echo "<tr>";

//table to display user options
echo "<td valign=top>";
echo "<table border=0 align=left width=200px>";
echo "<tr><td><a href=admin.php>Return to main menu</a></td></tr>";
$display->userOptions(true,$user_id);
echo "<tr><td><a href=javascript:void(0) class=contact>Helpdesk</a>";
$display->contactForm();
echo "</td></tr>";
echo "<tr><td><hr></td></tr>";
// reports
$display->reportOptions(true,$user_id);
//display treeview
echo "<tr><td><a href=javascript:void(0) title='Tree reports'>Treeview reports</a>";
echo "<div id='treeList' class=sidebar>";
$treeview->treeview_access($user_id);
echo "</div>";
echo "</td></tr>";
$config->checkPlugins();
echo "</table>";
echo "</td>";
echo "<td valign=top>";
$perm->userInfo($user_id);
if($perm->getUserLevel()!=0){
	echo "<font color=#FF0000>You don't have permission to access this resource</font>";
	exit();
}
//display error if it exists
if(isset($_GET['error']))	echo "<font color=#FF0000>".$_GET['error']."</font>";
if(isset($_GET['success']))	echo "<font color=#00FF00>Image successfully uploaded</font>";
echo "<table>";
echo "<tr><td>";
echo "<form name=upload enctype=multipart/form-data method=post>";
//content goes here
//select all resource in the database
$query="SELECT resource_id, resource_name FROM resource";
$sql=$conn->query($query);
echo "Resource<br>";
echo "<select name=resource id=resource>";
echo "<option value=0 selected>Select a resource...</option>";
for($i=0;$row=$sql->fetch();$i++){
	echo "<option value=$row[0]>$row[1]</option>";
}
echo "</select>";
echo "<br><br>";
//upload options
$max_file_size=1000000;
echo "<input type=hidden name=MAX_FILE_SIZE value='$max_file_size'>";
echo "<label for=file>Image to upload </label><br>";
echo "<input id=file type=file name=file size=40>";
echo "<br><br>";
echo "<input id=submit type=submit name=submit value=Submit onclick=imageValidation()>";
echo "</form>";
echo "</td></tr>";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "</table>";


?>

