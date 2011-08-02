<?php
require_once "../session.php";
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
<script type="text/javascript" src="js/jquery.basket.js"></script>
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
require_once ("../__dbConnect.php");
require_once ("../dispClass.php");
require_once ("../queryClass.php");
require_once ("../genObjClass.php");
require_once ("../resClass.php");
require_once ("../reportClass.php");
require_once ("../mailClass.php");
require_once ("../treeClass.php");
require_once ("../configClass.php");
require_once ("requisitionsClass.php");

//call database class (handle connections)
$db = new dbConnection();
$engine = $db->getEngine();
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
echo "<tr><td><a href=admin.php title='Return to the administration area'>Return to main menu</a></td></tr>";
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
echo "Fill out the form to create a new vendor";
echo "</td></tr>";
echo "<tr><td>";
//form to create requisitions
echo "<form method=post name=newVendor style='border:1px dashed;padding:5px;background-color:#DDD;'>";
echo "<table>";
echo "<tr><td><font color=#FF0000>ALL fields required</font></td></tr>";
echo "<tr><td colspan=2><b>Supplier data</b></td></tr>";
echo "<tr><td>Name</td><td><input type=text name=vendor_name id=vendor_name lang=yes></td></tr>";
echo "<tr><td>Address</td><td><input type=text name=address id=address lang=yes></td></tr>";
echo "<tr><td>Street</td><td><input type=text name=street id=street lang=yes></td></tr>";
echo "<tr><td>Postal code</td><td><input type=text name=postal_code id=postal_code lang=yes></td></tr>";
echo "<tr><td>VAT reg. No.</td><td><input type=text name=vat id=vat lang=yes></td></tr>";
$sql=$db->query("SELECT country_id, country_name FROM country WHERE country_id<240");	
//echo $sql->queryString;
echo "<tr><td>Country</td><td>";
echo "<select name=country id=country>";
for($i=0;$row=$sql->fetch();$i++){
	echo "<option id=$row[0]>$row[1]</option>";
}
echo "</select>";
echo "</td></tr>";
echo "<tr><td colspan=2><b>Communication</b></td></tr>";
echo "<tr><td>Name</td><td><input type=text name=com_name id=com_name lang=yes></td></tr>";
echo "<tr><td>Phone</td><td><input type=text name=phone id=phone lang=yes></td></tr>";
echo "<tr><td>Fax</td><td><input type=text name=fax id=fax lang=yes></td></tr>";
echo "<tr><td>Email</td><td><input type=text name=email id=email lang=yes></td></tr>";
echo "<tr><td colspan=2><b>Payment data</b></td></tr>";
echo "<tr><td>NIB</td><td><input type=text name=nib id=nib lang=nat> *</td></tr>";
echo "<tr><td>IBAN</td><td><input type=text name=iban id=iban lang=int> **</td></tr>";
echo "<tr><td>SWIFT code</td><td><input type=text name=swift id=swift lang=int> **</td></tr>";
echo "<tr><td>ABA/routing</td><td><input type=text name=aba id=aba lang=int> **</td></tr>";
echo "<tr><td>Bank name</td><td><input type=text name=bank id=bank lang=yes></td></tr>";
echo "<tr><td>Street</td><td><input type=text name=bank_street id=bank_street lang=yes></td></tr>";
echo "<tr><td><br></td></tr>";
echo "<tr><td><font color=#FF0000>* PT supplier only</font></td></tr>";
echo "<tr><td><font color=#FF0000>** International suppliers only</font></td></tr>";
echo "<tr><td colspan=2><input type=reset value=Clear>   <input type=button name=vendorSubmit id=vendorSubmit value=Submit></td></tr>";
echo "</table>";
//end form
echo "</form>";
echo "</td></tr>";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "</table>";
?>