<?php 
require_once("session.php");
$user_id = startSession();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<link href="css/main.css" rel="stylesheet" type="text/css">
<link href="css/autoSuggest.css" rel="stylesheet" type="text/css">
<link href="css/CalendarControl.css" rel="stylesheet" type="text/css">
<link href="css/tipTip.css" rel="stylesheet" type="text/css">
<link href="css/styles.css" rel="stylesheet" type="text/css">
<link href="css/jquery.alert.css" rel="stylesheet" type="text/css">
<link href="css/jquery.jnotify.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/jquery.init.js"></script>
<script type="text/javascript" src="js/jquery.tipTip.js"></script>
<script type="text/javascript" src="js/jquery.alert.js"></script>
<script type="text/javascript" src="js/jquery.jnotify.js"></script>
<script type="text/javascript" src="js/jquery.action.js"></script>
<script type="text/javascript" src="js/CalendarControl.js"></script>
<script type="text/javascript" src="js/filters.js"></script>
<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript" src="js/cloneFieldset.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/autoSuggest.js"></script>
<script type="text/javascript" src="requisitions/js/jquery.basket.js"></script>
<script type="text/javascript" src="animalhouse/js/jquery.bioterio.js"></script>
<script type="text/javascript">

function updQtt(oper, row){
	var value = Number($("#quantity_"+row).val());
	switch (oper){
	case "sum":
		value++;
		break;
	case "sub":
		if(value==1)return;
		value--;
		break;
	}
	$("#quantity_"+row).val(value);
}

$(document).ready(function(){
	$("#del").click(function(){
		$.action({
			action:"delete"
			});
	});

	$("#upd").click(function(){
		$.action({
			action:"update"
			});
	});

	$("#insert").click(function(){
		$.action({
			action:"insert"
			});
	});
	
});


</script>
<?php
/**
 * @author João Lagarto	/ Nuno Moreno
 * @copyright João Lagarto 2010
 * @version Datumo2.0
 * @license EUPL
 * @abstract Table manager. Script that displays table results
 */
error_reporting(1);

//browser detection
$browser = $_SERVER['HTTP_USER_AGENT'] . "\n\n";
$browser = strstr($browser, "Chrome");

//includes
require_once ("__dbConnect.php");
require_once ("dispClass.php");
require_once ("queryClass.php");
require_once ("genObjClass.php");
require_once ("resClass.php");
require_once ("searchClass.php");
require_once ("reportClass.php");
require_once ("mailClass.php");
require_once ("treeClass.php");
require_once ("configClass.php");
require_once "module.php";
require_once "functions.php";

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

//HTTP variables
if(isset($_GET['report']))	$report = 1;
if(isset($_GET['table']))	$table = $_GET['table']; 	//chosen table
if(isset($_GET['nrows']))	$nrows = $_GET['nrows']; 	//number of rows to be displayed per page
if(isset($_GET['no']))		$no = $_GET['no']; 		 	//number of elements from advanced filter
if(isset($_GET['action']))	$action = $_GET['action']; 	//database query
if(isset($_GET['order']))	$order = $_GET['order'];	//results' order
if(isset($_GET['search'])){	//which filter does it come from?
	$stype = $_GET['search'];	
} else  {
	$stype = "";
}
if(isset($_GET['colOrder'])){	//set the attribute chosen to be ordered
	$colOrder = $_GET['colOrder'];
	if($colOrder=='') $colOrder = $table."_id";
} else {
	$colOrder = $table."_id"; //set the first attribute to be ordered
}
if(isset($_GET['page'])) { //page to be shown
	$pageNum = $_GET['page'];	
} else {
	$pageNum = 1; //default page to be shown
}

//get information schema info
$display->tableHeaders($table);

//HACK to fill the advanced filter 
if($stype==2)	echo "<body onload=getSearchVars('$table')>";

//page title
echo "<title>Datumo: $table management</title>";

//other variables
$r=false;
$offset = ($pageNum - 1) * $nrows; //counting the offset 
$contact = "Do you want to report a bug? Please submit the form.";

//Database queries
if($action){
	foreach($_POST as $key=>$value){
		$genObj->__set($key, $value);
	}

	switch ($action){
		case "delete":
			$genObj->delete($table);
			break;
		case "update":
			$genObj->update($table);
			break;
		case "insert":
			$genObj->insert($table);
			break;
	}
}
//check for action variable after applying a filter
if(isset($_GET['comeFromAction']) and $_GET['comeFromAction']!="false"){ //if exists call javascript with action notification
	$comeFromAction=$_GET['comeFromAction'];
	if($comeFromAction[strlen($comeFromAction)-1]=="e"){
		$comeFromAction.="d";
	} else {
		$comeFromAction.="ed";
	}
	echo "<script type='text/javascript'>";
	echo "$.jnotify('Record(s) $comeFromAction');";
	echo "</script>";
}

//recover variables from filter to construct query (it relies on 3 elements)
if(isset($_GET['filter'])){
	$filter=true;
	//exit();
} else {
	foreach($_POST as $key=>$value){
		if($action) break; //Chrome/Firefox?
		//echo $value;
		if($value!=""){ $display->__set($key, $value); }
		
	}
	$filter=false;
}

if(!isset($report)){
	if($stype==3){ //if it comes from quick search
		$display->qSearchQueryBuilder($table,$nrows);	
		$numRows = $nrows;
	} else {
		$display->queryBuilder($user_id, $table, $nrows, $filter, $offset,$order, $colOrder);
		//get the number of rows in the table
		$numRows = $display->maxRows($table, $filter, $user_id);
	}
}	
//exit();
//get the last page according to the number of rows displayed in the page
$maxPage = ceil($numRows/$nrows);
// print the link to access each page
$self = $_SERVER['PHP_SELF'];
// creating previous and next link
// plus the link to go straight to
// the first and last page

if ($pageNum > 1){
   $page  = $pageNum - 1;
   $prev  = " <a href=javascript:void(0) class=exp onclick=submit('$stype','$table',$nrows,'$order','$colOrder',$page)>[Prev]</a> ";//\"$self?table=$table&nrows=$nrows&order=$order&colOrder=$colOrder&page=$page\">[Prev]</a> ";
   $first = " <a href=javascript:void(0) class=exp onclick=submit('$stype','$table',$nrows,'$order','$colOrder',1)>[First Page]</a> "; //\"$self?table=$table&nrows=$nrows&order=$order&colOrder=$colOrder&page=1\">[First Page]</a> ";
} else {
   $prev  = '&nbsp;'; // we're on page one, don't print previous link
   $first = '&nbsp;'; // nor the first page link
}
	
if ($pageNum < $maxPage){
   $page = $pageNum + 1;
   $next = " <a href=javascript:void(0) class=exp onclick=submit('$stype','$table',$nrows,'$order','$colOrder',$page)>[Next]</a> ";
   $last = " <a href=javascript:void(0) class=exp onclick=submit('$stype','$table',$nrows,'$order','$colOrder',$maxPage)>[Last Page]</a> ";
} else {
   $next = '&nbsp;'; // we're on the last page, don't print next link
   $last = '&nbsp;'; // nor the last page link
   //set page variable as maxpage
   if($page==$maxPage){
   		$page=$maxPage;
   }
}

//display menus
$options=array("Options",strtoupper($table)." Management");
echo "<h2>Datumo Administration Area</h2>";
echo "<table border=0>";
$display->options($options);
echo "<tr>";
echo "<td valign=top>";
echo "<table border=0 align=left width=200px>";
echo "<tr><td><a href=admin.php>Return to main menu</a></td></tr>";
$display->userOptions(true,$user_id);
echo "<tr><td><a href=javascript:void(0) class=contact>Helpdesk</a>";
$display->contactForm();
echo "</td></tr>";
echo "<tr><td><hr></td></tr>";
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
$config->compat();
echo "</table>";
echo "</td>";

echo "<td valign=top>";
//display user's restrictions for this table
$perm->tablePermissions($table, $user_id);
//search for available modules for this table
echo "<table >";
echo "<tr>";
$module=new module($table);
echo "</tr>";
echo "</table>";

echo "<table border=0>";
echo "<tr>";
if($perm->getUpdate()) {echo "<td><input type=button name=upd id=upd value=Update></td>";}
if($perm->getDelete()) {echo "<td><input type=button name=del id=del value=Delete></td>";}
if($perm->getUpdate() or $perm->getDelete()) $r=true;
//set order
//Regular filter
echo "<td><input type=button name=filter_$table id=filter_$table value=Search>";
//echo "<td><a href=javascript:void(0)>Search</a>";
echo "<div id='".$table."_div' class=regular>";
$display->fields($table,"",'manager',$order,$colOrder,$page);
echo "</div>";
echo "</td>";
//Advanced search
echo "<td><input type=button name=adv_$table id=adv_$table value='Advanced Search'>";
echo "<div id='advsearch' class=regular>";
$search->advancedFilter($user_id,$table);
echo "</div>";
echo "</td>";
echo "<td><input type=button name=legend id=legend value=Legend>";
echo "<div id=legendiv class=regular>";
$display->legend($table,$user_id);
echo "</div>";
echo "</td>";
//print page navigation
echo "<td>".$first.$prev." Showing page $pageNum of $maxPage pages ".$next.$last."</td>"; 
echo "<td><b>Jump to page</b> <input type=text size=1 name=newPage id=newPage value=$pageNum><input type=button id=jump value='Go' onclick=submit('$stype','$table',$nrows,'$order','$colOrder',$('#newPage').val())></td>";
echo "</tr>";
echo "</table>";
//display results
echo "<table class=main id=main>";
//are there results to display?
if($numRows>0){
	echo "<tr class=headers>";
	echo "<td colspan=2></td>";
	if($r) echo "<td style='text-align:center'><input type=checkbox id=cb_all name=cb_all onchange=checkall(this.id,$nrows)></td>";
	$display->headers(FALSE, $stype,$table,$nrows,$order,1); //call method to display table headers
	echo "</tr>";
} else {
	echo "<tr class=headers>";
	echo "<td colspan=3></td>";
	$display->headers(TRUE, $stype,$table,$nrows,$order,1); //call method to display table headers
	echo "</tr>";
	echo "No results to display";
}
//display main results
$display->results($table,$r); //call method to display query results

//search for permissions related with new entries in the table
if($perm->getInsert()) {$display->insert($table,$stype,$nrows,$order);}
echo "</table>";
echo "</td>";
echo "</tr>";
echo "</table>";

//hidden content
//echo "<div id=info name=info class=info onmouseover=\"javacript:document.getElementById('info').style.height='130px';\" onmouseout=\"javacript:document.getElementById('info').style.height='1px';\">"; 
echo "<div id=info name=info class=info>";
$display->information($table, $nrows);
echo "</div>";

echo "<input type='hidden' name='multiple' id='multiple' value=1>";

/**************************JAVASCRIPT*******************************/
if($stype==2){
	//Big fu(...) hack to fill the advanced filter after a search
	echo "<script type='text/javascript'>";
	echo "function getSearchVars(objName){";
	echo "document.getElementById('table').value=objName;";
	echo "ajaxEquiDD(document.getElementById('table'),'field');";
	$i=0; //counter
	$j=0; //another counter
	foreach($_POST as $key=>$value){
		$i++;$j++; //increment counters
		if($i==1) { //field
			echo "document.getElementById('$key').value='$value';";//validate this field to foreign key values
			echo "selOperator(document.getElementById('$key').id);";
			echo "var att='$value';";
		}
		if($i==2){
			echo "document.getElementById('$key').value='$value';";
			echo "if(document.getElementById('$key').value == 4) var fk=true;";
			echo "else var fk=false;";
		}
		if($i==3) {
			echo "if(fk==true){";
			//echo "alert('$value');";
			echo "url='ajaxGetAtt.php?id=$value&table='+objName+'&att='+att;";
			echo "var str = ajaxRequest(url);";
			echo "document.getElementById('$key').value=str;";
			echo "} else {";
			echo "document.getElementById('$key').value='$value';}";
			if($j<sizeof($_POST)) echo "checknew('sum', document.getElementById('clone'));";
			$i=0;
		}
	}
	echo "}";
	echo "</script>";
}

/**************************END OF JAVASCRIPT*******************************/
?>      