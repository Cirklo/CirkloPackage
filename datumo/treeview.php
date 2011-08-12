<?php 
require_once("session.php");
$user_id = startSession();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<link href="css/main.css" rel="stylesheet" type="text/css">
<link href="css/autoSuggest.css" rel="stylesheet" type="text/css">
<link href="css/tipTip.css" rel="stylesheet" type="text/css">
<link href="css/jquery.treeview.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/jquery.tipTip.js"></script>
<script type="text/javascript" src="js/jquery.treeview.js"></script>

<script type="text/javascript" src="js/treeview.js"></script>
<script type="text/javascript" src="js/filters.js"></script>
<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript" src="js/cloneFieldset.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$("*").tipTip(); //tiptip initialization
	$("#browser").treeview({
		toggle: function() {
			console.log("%s was toggled.", $(this).find(">span").text());
		}
	});

	/**
	 * AutoSuggest Plugin
	 * 
	 * Input must have lang=__fk in order to work correctly
	 */
	
	$("input[lang=__fk]").focus(function(){
		$(this).simpleAutoComplete("autoSuggest.php?field="+this.id);
	});
	
	
});
</script>

<?php

require_once "__dbConnect.php";
require_once "resClass.php";
require_once "dispClass.php";
require_once "searchClass.php";
require_once "queryClass.php";
require_once "treeClass.php";
require_once "queryClass.php";


//http variables
if(isset($_GET['tree'])) {	$tree = $_GET['tree'];}

//call database class (handle connections)
$db = new dbConnection();
//other classes
$treeview = new treeClass($tree);
$display = new dispClass();

//get this tree information
$treeview->treeDesc($tree);
//get user permission for this view
$treeview->treeRestrictions($tree, $user_id);

//Is there any action?
if(isset($_GET['action'])){
	$action=$_GET['action'];
	switch($action){
		case "delete":
			$treeview->delete($tree);
			break;
		case "update":
			$treeview->update($tree);
			break;
		case "add":
			break;
	}
}

//display menus
$options=array("Treeview ".$treeview->getTreeviewName(),"Report Details");

echo "<table border=0>";
$display->options($options);
echo "<tr>";

echo "<td width=300px valign=top>";
//call class to dynamically generate the requested treeview
$treeview->genTreeView($tree);
echo "</td>";
echo "<td valign=top>";
echo "<div id=details class=detailsTree>No items selected</div>";

echo "</td>";
echo "</tr>";
echo "</table>";
?>