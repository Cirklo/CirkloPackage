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
<script type="text/javascript" src="../js/jquery.cookie.js.js"></script>
<script type="text/javascript" src="../js/jquery.tipTip.js"></script>
<script type="text/javascript" src="../js/jquery.alert.js"></script>
<script type="text/javascript" src="js/jquery.bioterio.js"></script>
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
require_once ("__dbConnect.php");
require_once ("../dispClass.php");
require_once ("../queryClass.php");
require_once ("../genObjClass.php");
require_once ("../resClass.php");
require_once ("../reportClass.php");
require_once ("../mailClass.php");
require_once ("../treeClass.php");
require_once ("../configClass.php");
require_once ("../requisitions/requisitionsClass.php");

//call database class (handle connections)
$db = new dbConnection();
$engine = $db->getEngine();
$database=$db->getDatabase();
//call other classes
$display = new dispClass();
$genObj = new genObjClass();
$perm = new restrictClass();
$search = new searchClass();
$treeview = new treeClass();
$mail = new mailClass();
$config = new configClass();
$req = new reqClass();

//set local variables 
$arr = array();

//display menus
$options=array("Options","Basket Management");
echo "<table border=0>";
$display->options($options);
echo "<tr>";
echo "<td valign=top>";
echo "<table border=0 align=left width=200px>";
$display->userOptions(true,$user_id);
echo "<tr><td><a href=javascript:void(0) class=contact>Helpdesk</a>";
$display->contactForm();
echo "</td></tr>";
echo "<tr><td><hr></td></tr>";
echo "<tr><td><a href=/".$db->getFolder()."/admin.php title='Return to the administration area'>Return to main menu</a></td></tr>";
//echo "<tr><td><a href=excel.php?table=$table title='Export data to xls file'>Export data</a></td></tr>";
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
echo "<table>";
echo "<tr><td>";
echo "Animal requisition";
echo "</td></tr>";
echo "<tr><td>";
//form to create requisitions
echo "<form method=post name=animal_req style='border:1px dashed;padding:5px;background-color:#DDD;'>";
echo "<table>";
echo "<tr><td>Strain</td>";
$sql=$db->query("SELECT strain_id, strain_name FROM strain ORDER BY strain_name");
echo "<td><select name=strain id=strain>";
//loop through all results
for($i=0;$row=$sql->fetch();$i++){
	echo "<option id=$row[0]>$row[1]</option>";
}
echo "</select> *</td></tr>";
echo "<tr><td>Delivery room</td><td><input type=text name=delivery_room id=delivery_room lang=yes> *</td></tr>";
echo "<tr><td>Age range / D.O.B</td><td><input type=text name=age id=age lang=yes> *</td></tr>";
echo "<tr><td>Date needed</td><td><input type=text name=dateNeeded id=dateNeeded onfocus=showCalendarControl(this) readonly=readonly lang=yes> *</td></tr>";
echo "<tr><td colspan=2>Males <input type=text id=males name=males value=0 size=1 lang=yes>    Females <input type=text id=females name=females value=0 size=1 lang=yes></td></tr>";//    Either <input type=text id=either name=either value=0 size=1 lang=yes></td></tr>";
echo "<tr><td valign=top>Comments</td><td><textarea name=comment id=comment rows=10 cols=40></textarea></td></tr>";
echo "<tr><td>Contact / Email</td><td><input type=text name=contact id=contact lang=yes> *</td></tr>";
$sql=$db->query("SELECT account_id, account_number, account_project, account_budget FROM $database.account WHERE account_start<NOW() AND account_end>NOW() AND account_id<>0 AND account_dep IN (SELECT user_dep FROM $database.user WHERE user_id=$user_id) UNION SELECT account_id, account_number, account_project, account_budget FROM $database.account, $database.accountperm WHERE accountperm_account=account_id AND account_start<NOW() AND account_end>NOW() AND account_id<>0 AND accountperm_user=$user_id ORDER BY account_number");	
//echo $sql->queryString;
echo "<tr><td>Account</td><td>";
echo "<select name=account id=account>";
echo "<option id=0 selected>-----------------</option>";
for($i=0;$row=$sql->fetch();$i++){
	echo "<option id=$row[0] title='$row[2]'>$row[1]</option>";
}
echo "</select> *";
echo "</td></tr>";
echo "<tr><td><br></td></tr>";
echo "<tr><td><font color=#FF0000>* requested fields</font></td></tr>";
echo "<tr><td colspan=2><input type=reset value=Clear>   <input type=button name=animalSubmit id=animalSubmit value=Submit></td></tr>";
echo "</table>";
//end form
echo "</form>";
echo "</td></tr>";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "</table>";


?>