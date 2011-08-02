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

function listSelect(){
	var url="functions.php";
	$.get(url,{
		type:1,
		list:$("#mailList").val()},
		function (data){
			$("#mailDiv").html(data);
		});
}

function sendMail(){
	if($("#mailList").val()==0){
		$.jnotify("You must select a valid target list");
		return;
	}
	if($("#subject").val()=="" || $("#mailMessage").val()==""){
		$.jnotify("You must enter all fields to send the email");
		return;
	}
	if(!$("#mailSelector").val() && $("#mailList").val()!="all"){
		$.jnotify("You must select a recipient list");
		return;
	}
	var resp=confirm("Send email?");
	if (resp){
		document.body.style.cursor = 'wait';
		var url="functions.php";
		$.get(url,{
			type:2,
			list:$("#mailList").val(),
			subject:$("#subject").val(),
			message:$("#mailMessage").val(),
			recipient:$("#mailSelector").val()},
			function (data){
				$.jnotify(data);
				document.body.style.cursor = 'default';
			});
	}
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
$options = array("Options");
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
echo "</tr>";
echo "</table>";

/********************************************************************************************/
echo "<div lang=exp style='border:0px solid;position:relative;float:left;padding-left:30px;margin-top:0px;width:500px'>";
echo "<h3>Mailing tool</h3>";
//email subject
echo "<div lang=exp id=content style='position:relative;border:0px solid;'>";
echo "Subject<br><input type=text name=subject id=subject size=60 maxlength=100>";
echo "<input type=button name=sendMail id=sendMail value='Send email' onclick=sendMail()>";

echo "</div>";

echo "<div lang=exp style='position:relative;float:left;margin-top:20px;width:200px;border:0px solid'>";
//to who are we going to send the email
echo "To: ";
//which tables are available for mailing?
echo "<select name=mailList id=mailList onchange=listSelect()>";
echo "<option value=0>Select a list...</option>";
echo "<option value=all>All users</option>";
echo "<option value=department>Department</option>";
echo "<option value=resource>Resource users</option>";
echo "<option value=resourcetype>Resource type</option>";
echo "</select>";
//div to insert new selector
echo "<div lang=exp style='border:0px solid;position:relative;float:left;;overflow:auto;margin-top:20px' id=mailDiv>";
echo "</div>";
echo "</div>";
echo "<divlang=exp  style='position:relative;float:right;padding-left:10px;margin-top:10px;margin-right:20px;'>";
echo "Message<br>";
echo "<textarea name=mailMessage id=mailMessage rows=10 cols=30></textarea>";
echo "</div>";

//send email button
echo "</div>";







/*********************************************************************************************/


?>

