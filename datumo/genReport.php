<?php 
require_once("session.php");
$user_id = startSession();
?>

<link href="css/main.css" rel="stylesheet" type="text/css">
<link href="css/autoSuggest.css" rel="stylesheet" type="text/css">
<link href="css/tipTip.css" rel="stylesheet" type="text/css">
<link href="css/reports.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/jquery.init.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js.js"></script>
<script type="text/javascript" src="js/jquery.tipTip.js"></script>
<script type="text/javascript" src="js/jquery.reports.js"></script>
<script type="text/javascript" src="js/filters.js"></script>
<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript" src="js/cloneFieldset.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/autosuggest.js"></script>
<script type="text/javascript">

$(document).ready(function(){
	
	$("input[type=checkbox], .tables").click(function(){
//		alert("flag");
		var counter=0;
		//loop through all checkboxes
		$("input[type=checkbox], .tables").each(function(){
			//check its value
			if($(this).attr("checked"))	counter++; //increment number of checked checkboxes
		});
		//are there at least 3 checked checkboxes
		if(counter>=3){
			$("input[type=checkbox], .tables").each(function(){
				//check its value
				if(!$(this).attr("checked")){
					this.disabled=true;	//disable all unchecked inputs if 3 tables were already selected
				}
			});
		} else {	//allow checkbox click
			$("input[type=checkbox], .tables").each(function(){
				//check its value
				if(!$(this).attr("checked")){
					this.disabled=false;	//able all unchecked inputs if 3 tables were already selected
				}
			});
		}
		//alert(counter);
	});
	

	
});


</script>
<?php
/**
 * @author João Lagarto	/ Nuno Moreno
 * @copyright João Lagarto 2010
 * @version Datumo2.0
 * @license EUPL
 * @abstract page to generate dynamic reports
 */
error_reporting(1);

//includes
require_once("__dbConnect.php");
require_once("resClass.php");
require_once("dispClass.php");
require_once("searchClass.php");
require_once("queryClass.php");
require_once("reportClass.php");

//call database class (handle connections)
$db=new dbConnection();
//call other classes
$perm=new restrictClass();

echo "<fieldset>";
echo "<legend>Welcome to Datumo Report Generator Tool</legend>";
//set search path to main database

$tables = $perm->tableAccess($user_id);
echo "<div class=tableSel>";
echo "Select tables (max. 3)";
echo "<div style='float:right;text-align:right;'>";
echo "<a href=javascript:void(0)>Help</a>&nbsp;&nbsp;";
echo "<a href=javascript:void(0) onclick=window.location.reload()>Reset</a>&nbsp;&nbsp;";
echo "<div id=tInfo style='display:none;position:relative;z-index:9999;border:1px solid;background:#DDD'></div>";
echo "</div>";
echo "<br>";
for($i=0;$i<sizeof($tables[0]);$i++){
	echo "<label style='cursor:pointer'><input type=checkbox id=".$tables[0][$i]." name=".$tables[0][$i]." class=tables> ".$tables[1][$i]."</label><br>";
}
echo "</div>";
echo "<div class=next>";
echo "<a href=javascript:void(0) id=nextFields>Next</a>";
echo "</div>";
echo "<hr>";
echo "<div id=fields></div>";
echo "<div id=clauses></div>";
echo "<div id=groupOrder></div>";
echo "<div id=inputParameters></div>";
echo "<div id=reportInfo></div>";
echo "</fieldset>";
?>