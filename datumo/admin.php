<?php 
require_once("session.php");
$user_id = startSession();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Datumo Administration Area</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="css/main.css" rel="stylesheet" type="text/css">
<link href="css/autoSuggest.css" rel="stylesheet" type="text/css">
<link href="css/CalendarControl.css" rel="stylesheet" type="text/css">
<link href="css/styles.css" rel="stylesheet" type="text/css">
<link href="css/tipTip.css" rel="stylesheet" type="text/css">
<link href="css/jquery.jnotify.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.14.custom.js"></script>
<script type="text/javascript" src="js/jquery.init.js"></script>
<script type="text/javascript" src="js/jquery.tipTip.js"></script>
<script type="text/javascript" src="js/jquery.jnotify.js"></script>
<script type="text/javascript" src="js/CalendarControl.js"></script>
<script type="text/javascript" src="js/filters.js"></script>
<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript" src="js/autoSuggest.js"></script>
<script type="text/javascript" src="js/cloneFieldset.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type='text/javascript'>
</script>
<?php
/** @author João Lagarto
 * @copyright João Lagarto 2010
 * @version Datumo2.0
 * @license EUPL
 * @abstract Administration index. Script that shows available tables and reports
 */
error_reporting(1);
//includes
require_once ("__dbConnect.php");
require_once ("resClass.php");
require_once ("dispClass.php");
require_once ("searchClass.php");
require_once ("queryClass.php");
require_once ("reportClass.php");
require_once ("treeClass.php");
require_once ("mailClass.php");
require_once ("configClass.php");
require_once "functions.php";
require_once "pub.php";

//call database class (handle connections)
$db = new dbConnection(); 

//other classes
$admin = new restrictClass();
$display = new dispClass();
$search = new searchClass();
$report = new reportClass();
$treeview = new treeClass();
$mail = new mailClass();
$config = new configClass();
//$msg = new msgClass();

//set table types
$type = array(); 
$type[0] = "BASE TABLE";
$type[1] = "VIEW";  
//loop for all tables and views
$tables = array();
$table_type = array();
//get tables to which this user has access. Get masks as well
$tables = $admin->tableAccess($user_id);
//get tables type (BASE TABLES or VIEW)
$tableSettings=$display->tableview($tables[0]);
//set table type array
$table_type=$tableSettings[0];
//set table comments array
$table_description=$tableSettings[1];
//count number of tables and views
$table_type_count=array_count_values($table_type);
//get icon picture
$maskPic=$display->getMaskPic();

//main table
$options = array("Options","Tables","Views");
echo "<h2>Datumo Administration Area</h2>";
//display page options
echo "<table border=0 class=admin>";
$display->options($options);
echo "<tr>";

//table to display user options
echo "<td valign=top>";
echo "<table border=0 align=left width=200px>";
$display->userOptions(true,$user_id);
echo "<tr><td><a href=javascript:void(0) class=contact>Helpdesk</a>";
$display->contactForm();
echo "</td></tr>";
echo "<tr><td><hr></td></tr>";
//advanced search div
echo "<tr>";
echo "<td><a href=javascript:void(0)>Advanced Search</a>";		
echo "<div id='advsearch' class=sidebar style='position:absolute'>";
$search->advancedFilter($user_id,'');
echo "</div>";
echo "</td>";
echo "</tr>";
// reports
$display->reportOptions(true,$user_id);
//display treeview
echo "<tr><td><a href=javascript:void(0) title='Tree reports'>Treeview reports</a>";
echo "<div id='treeList' class=sidebar>";
$treeview->treeview_access($user_id);
echo "</div>";
echo "</td></tr>";
//external plugins
$config->checkPlugins();
//display compatibility and sponsor
$config->compat();
echo "</table>";
echo "</td>";
//loop through all table types
for($j=0;$j<sizeof($type);$j++){
	echo "<td valign=top>";
	echo "<table border=0 align=left>";
	echo "<tr><td>$title[$j]</td></tr>";
	//are there any table or View available?
	if(!isset($table_type_count[$type[$j]])) {
		echo "<tr><td width=250px>No entries available</td></tr>";
		//break;
	}
	for($i=0; $i<sizeof($tables[0]); $i++){
		//set table
		$objName=$tables[0][$i];
		//set table mask
		$tableMask=$tables[1][$i];
		//verify if there is any VIEW or TABLE to be displayed and proceed accordingly	
		if($table_type[$i]==$type[$j]){
			//display table or view name (or mask if it exists)
			echo "<tr><td>";
			//search for an associated mask
			echo "<input type=button name=$objName id=$objName value='$tableMask' class=callTables onclick=window.open('manager.php?table=$objName&nrows=20','_self') title='".$table_description[$i]."' style='background-image:url($maskPic[$i]);'>";	
			//disabling admin area search. It takes too long to build the search fields due to the entensive information schema queries
//			echo "<td><a href=javascript:void(0)>Search</a>";
//			//regular search div
//			echo "<div id='".$objName."_div' class=regular>";
//			$display->fields($objName,$i,'admin');
//			echo "</div>";
			echo "</td>";
			echo "<td>";
			//Is there any table with quick search queries?	
			if($search->qsearchFind($objName)){
				echo "<a href=javascript:void(0)>Quick search</a>";	
				//quick search div
				echo "<div id='quicksearch_".$objName."' class=regular>";
				echo "<table border=0>";
				echo "<form name=qsearch$i method=post>";
				echo "<tr><td><b>Search</b>&nbsp;&nbsp;<input type=text class=reg name=qsearch$objName id=qsearch$objName>&nbsp;&nbsp;<input type=image src=pics/magnifier.png onclick=qSubmit('".$objName."',$i)></td></tr>";
				echo "</form>";
				echo "<tr><td><b>Results to be displayed</b>&nbsp;&nbsp;<input type=text class=reg name=qsearchNrows_$i id=qsearchNrows_$i value=100 size=1></td></tr>";
				echo "</table>";
				echo "</div>";
			}
			echo "</td>";
			echo "</tr>";
		}
	}
	echo "</table>";
	echo "</td>";
}
//dynamic reports and correspondent input parameters
/** NO MORE DYNAMIC REPORTS**/
//echo "<td valign=top>";
//$arr = array();
//$arr = $report->dynamicReports($user_id);
//echo "</td>";
echo "</tr></table>";

//Do we have publicity in this page??
$pub=new pubHandler();
pageViews("");



?>


