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
<script type="text/javascript" src="js/ajax.js"></script>
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
require_once "configClass.php";
require_once "module.php";
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
	header_nav($user_id);
echo "</header>";
/********************************************END OF HEADER / CONTENT GOES NEXT**********************************************/



//STARTING HTML LAYOUT
echo "<section id=section>";
echo "<div class=sidebar lang=exp>";
$config->checkPlugins($level);
$config->compat();
echo "</div>";

echo "<div class=main lang=exp>";



$perm->userInfo($user_id);
if($perm->getUserLevel()==2){
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
echo "<label for=file>Image to upload (image name must not include spaces)</label><br>";
echo "<input id=file type=file name=file size=40>";
echo "<br><br>";
echo "<input id=submit type=submit name=submit value=Submit onclick=imageValidation()>";
echo "</form>";
echo "</td></tr>";
echo "</table>";

echo "</div>";
echo "</section>";
?>

</body>
</html>