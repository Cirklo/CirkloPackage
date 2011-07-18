<?php 
require_once "../session.php";
$user_id=startSession();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Basket display</title>
<link href="../css/tipTip.css" rel="stylesheet" type="text/css">
<link href="../css/requisitions.css" rel="stylesheet" type="text/css">
<link href="../css/redmond/jquery-ui-1.8.9.custom.css" rel="stylesheet" type="text/css">
<link href="../js/src/css/ui.jqgrid.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="../js/src/grid.loader.js"></script>
<script type="text/javascript" src="js/jquery.basket.js"></script>
<script type="text/javascript" src="../js/jquery.tipTip.js"></script>
<script type="text/javascript" src="../js/ajax.js"></script>
<script type="text/javascript" src="../js/jquery.print.js"></script>
<script type="text/javascript">
//initialize tiptip plugin
$(document).ready(function(){
	$("*").tipTip(); //tiptip initialization
});
</script>
</head>
<?php 

require_once "../__dbConnect.php";
require_once "../resClass.php";

if(isset($_GET['state'])){	$state=$_GET['state'];}

echo "<body onload=\$(document).createSubGrid({state:'$state'});>";
echo "<table id=list_0></table>";
echo "<div id=pager_0></div>"; 
echo "<br><br>";
echo "<table id=list></table>";
echo "<div id=pager></div>"; 
/*
//call database class
$conn=new dbConnection();
$res=new restrictClass();
$conn->dbConn();
$database=$conn->getDatabase();//set database name
*/
//div to select account
echo "</body>";
echo "</html>";
?>
<form method="post" action="csvExport.php">
    <input type="hidden" name="csvBuffer" id="csvBuffer" value="" />
</form>
