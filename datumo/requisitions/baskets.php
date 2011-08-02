<?php

require_once("../session.php");
$user_id = startSession();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../css/main.css" rel="stylesheet" type="text/css">
<link href="../css/autoSuggest.css" rel="stylesheet" type="text/css">
<link href="../css/CalendarControl.css" rel="stylesheet" type="text/css">
<link href="../css/tipTip.css" rel="stylesheet" type="text/css">
<link href="../css/styles.css" rel="stylesheet" type="text/css">
<link href="../css/jquery.alert.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="../js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="../js/jquery.init.js"></script>
<script type="text/javascript" src="../js/jquery.tipTip.js"></script>
<script type="text/javascript" src="../js/jquery.alert.js"></script>
<script type="text/javascript" src="../js/CalendarControl.js"></script>
<script type="text/javascript" src="../js/filters.js"></script>
<script type="text/javascript" src="../js/functions.js"></script>
<script type="text/javascript" src="../js/cloneFieldset.js"></script>
<script type="text/javascript" src="../js/ajax.js"></script>

<?php

/** @author João Lagarto
 * @copyright João Lagarto 2010
 * @version Requisition System 2.0
 * @license EUPL
 * @abstract Script to handle baskets depending on the basket type
 */
error_reporting(1);

//includes
//require_once "../__dbConnect.php";
require_once "../dispClass.php";
require_once "requisitionsClass.php";
require_once "requisitions.php";

$display=new dispClass();
$req=new reqClass();

//set local variables 
$arr=array();

//display menus
$options=array("Options","Basket Management");
echo "<table border=0>";
$display->options($options);
echo "<tr>";
echo "<td valign=top>";
//display the left navigation menu
displayMenu($user_id);
echo "</td>";
echo "<td valign=top>";
echo "<table>";
echo "<tr><td>";
$req->createBasket($user_id);
echo "Select the basket type:";
$arr = $req->getType();
for($i=0;$i<sizeof($arr);$i++){
	echo "<ul><a href=javascript:void(0) onclick=gridDisplay.location.href='grid.php?type=$arr[$i]'>$arr[$i]</a></ul>";
}
echo "</td></tr>";
echo "<tr><td>";
echo "<iframe name=gridDisplay id=gridDisplay class=gridDisp>";
echo "</iframe>";		
echo "</td></tr>";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "</table>";


?>