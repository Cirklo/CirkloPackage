<?php
require_once "session.php";
$user_id = startSession();
//echo $_SESSION['path'];
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

<title>Insert form</title>

<!-- BEGIN Navigation bar CSS - This is where the magic happens -->
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/autoSuggest.css">
<link rel="stylesheet" href="css/CalendarControl.css">
<link rel="stylesheet" href="css/tipTip.css">
<link rel="stylesheet" href="css/jquery.jnotify.css">

<script type="text/javascript" src="js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.14.custom.js"></script>
<script type="text/javascript" src="js/jquery.init.js"></script>
<script type="text/javascript" src="js/jquery.tipTip.js"></script>
<script type="text/javascript" src="js/jquery.jnotify.js"></script>
<script type="text/javascript" src="js/jquery.action.js"></script>
<script type="text/javascript" src="js/CalendarControl.js"></script>
<script type="text/javascript">

var noInserts=1;
$(document).ready(function(){
	$("#insert").click(function(){
		$.action({
			action:"insert",
			ext:"insert.php"
		});
	});
});


</script> 
<!-- END CSS -->
</head>
<body>
<?php 

//PHP includes
require_once "__dbConnect.php";
require_once "dispClass.php";

//classes
$conn=new dbConnection();
$display=new dispClass();

//http variables
if(isset($_GET['table'])){	$objName=$_GET['table'];}

echo "<div style='text-align:justify; line-height:18px;'>";
echo "<h3>".strtoupper($objName)."</h3>";
echo "<hr>";

//get table settings
$display->tableHeaders($objName);
$header=$display->getHeader();
$FKtable=$display->getFKtable();
$fullheader=$display->getFullHeader();
$datatype=$display->getDatatype();
$comment=$display->getComment();

//change database to information schema
$conn->dbInfo();
$sql = $conn->prepare("SELECT column_name FROM columns WHERE table_schema='".$conn->getDatabase()."' AND table_name='$objName'");
echo "<table border=0>";
echo "<form name=table method=post>";
$sql->execute();
//print_r($_POST);
for($i=0;$row=$sql->fetch();$i++){ 
	echo "<tr><td>".$header[$i]."</td>";
	echo "<td><input";
	if($comment[$fullheader[$i]]=="pwd")
		echo " type=password ";
	else 
		echo " type=text ";
		
	echo "name=$row[0] id=$row[0]";
	//is it a foreign key?
	
	if($FKtable[$i]!='' and $FKtable[$i]!=$objName) {
		echo " class=fk lang=__fk "; //set this as a FK input
	} else {
		echo " class=reg ";
		if($datatype[$fullheader[$i]]=="date" or $datatype[$fullheader[$i]]=="datetime")
			echo " onfocus=showCalendarControl(this) readonly=readonly";
		}
		echo "style='border:1px solid #aaa' value=''>";
	echo "</td></tr>";
}
echo "<tr><td></td><td><input type=button name=insert id=insert value='Insert'></td></tr>";
echo "</form>";
echo "</table>";
echo "</div>";


?>