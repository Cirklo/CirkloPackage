<?php

require_once("session.php");
$user_id = startSession();
?>


<!doctype html>  
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>

<!-- BEGIN Meta tags -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<title>Datumo administration area</title>

<!-- BEGIN Navigation bar CSS - This is where the magic happens -->
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/autoSuggest.css">
<link rel="stylesheet" href="css/CalendarControl.css">
<link rel="stylesheet" href="css/tipTip.css">
<link rel="stylesheet" href="css/navbar.css">
<link rel="stylesheet" href="css/jquery.jnotify.css">
<!-- END Navigation bar CSS -->

<!-- BEGIN JavaScript -->
<script type="text/javascript" src="js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.14.custom.js"></script>
<script type="text/javascript" src="js/jquery.init.js"></script>
<script type="text/javascript" src="js/jquery.tipTip.js"></script>
<script type="text/javascript" src="js/jquery.jnotify.js"></script>
<script type="text/javascript" src="js/jquery.action.js"></script>
<script type="text/javascript" src="js/CalendarControl.js"></script>
<script type="text/javascript" src="js/filters.js"></script>
<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript" src="js/cloneFieldset.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="requisitions/js/jquery.basket.js"></script>
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
				$("#subject").val("");
				$("#mailMessage").val("");
				$("#mailList")[0].selectedIndex = -1;
			});
	}
}

</script>
<!-- END JavaScript -->
</head>
<body>


<?php

/** @author João Lagarto
 * @copyright João Lagarto 2010
 * @version Requisition System 2.0
 * @license EUPL
 * @abstract Script to handle baskets depending on the basket type
 */

//php includes
require_once "__dbConnect.php";
require_once "dispClass.php";
require_once "resClass.php";
require_once "mailClass.php";
require_once "configClass.php";
require_once "functions.php";
require_once "menu.php";
require_once "plotAux.php";

//php classes
$conn=new dbConnection();
$engine = $conn->getEngine();
$display = new dispClass();
$perm = new restrictClass();
$config = new configClass();
$menu= new menu($user_id);

//get user info
$perm->userInfo($user_id);
$login=$perm->getUserLogin();
$level=$perm->getUserLevel();

/******************************************************BEGIN OF HEADER******************************************************/
echo "<header>";
	echo "<h1>Datumo Administration Area: ".strtoupper($table)."</h1>";
	//navigation bar display
	header_nav($user_id);
echo "</header>";
/********************************************END OF HEADER / CONTENT GOES NEXT**********************************************/



//STARTING HTML LAYOUT
echo "<section id=section>";
echo "<div class=sidebar lang=exp>";
$config->checkPlugins($level);
$config->compat();
echo "</div>";

//echo "<div class=main lang=exp>";
echo "<div lang=exp style='border:0px solid;position:relative;float:left;padding-left:30px;margin-top:0px;width:500px;z-index:-1'>";
echo "<h3>Mailing tool</h3>";
//email subject
echo "<div lang=exp id=content style='position:relative;border:0px solid;'>";
echo "Subject<br><input type=text name=subject id=subject size=60 maxlength=100>";
echo "<input type=button name=sendMail id=sendMail value='Send email' onclick=sendMail()>";

echo "</div>";

echo "<div lang=exp style='position:relative;float:left;margin-top:20px;width:200px;border:0px solid;'>";
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
echo "<div lang=exp style='border:0px solid;position:relative;float:left;overflow:auto;margin-top:20px' id=mailDiv>";
echo "</div>";
echo "</div>";
echo "<div lang=exp  style='position:relative;float:right;padding-left:10px;margin-top:10px;margin-right:20px;'>";
echo "Message<br>";
echo "<textarea name=mailMessage id=mailMessage rows=10 cols=30></textarea>";
echo "</div>";

//send email button
echo "</div>";

echo "</section>";





/*********************************************************************************************/


?>
</body>
</html>
