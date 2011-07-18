<?php 
require_once "session.php";
$user_id=startSession();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/tipTip.css" rel="stylesheet" type="text/css">
<link href="css/redmond/jquery-ui-1.8.9.custom.css" rel="stylesheet" type="text/css">
<link href="js/src/css/ui.jqgrid.css" rel="stylesheet" type="text/css">
<link href="css/autoSuggest.css" rel="stylesheet" type="text/css">
<link href="css/CalendarControl.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/src/grid.loader.js"></script>
<script type="text/javascript" src="js/jquery.reports.js"></script>
<script type="text/javascript" src="js/CalendarControl.js"></script>
<script type="text/javascript" src="js/jquery.tipTip.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/jquery.print.js"></script>
<script type="text/javascript" src="js/autoSuggest.js"></script>

<script type="text/javascript">
$(document).ready(function(){
	$("input[lang=__fk]").focus(function(){
		$(this).simpleAutoComplete("autoSuggest.php?field="+this.id);
	});
});
</script>
<style>

div.grouping{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:12px; 
	line-height:20px; 
}


</style>

</head>
<?php 

require_once "__dbConnect.php";
require_once "resClass.php";
require_once "reportClass.php";
require_once "queryClass.php";

//call database class
$conn=new dbConnection();
$res=new restrictClass();
$report=new reportClass();
$qClass=new queryClass();
$database=$conn->getDatabase();//set database name

//url variables
if(isset($_GET['report']))	$report_id=$_GET['report'];

//get report information 
$report->reportInfo($report_id);
$reportName=$report->getReportName();
$reportDesc=$report->getReportDesc();
echo "<title>$reportName</title>";
echo "<div class=grouping>";
$query="SELECT reprop_attribute, reprop_mask FROM reprop WHERE reprop_report=$report_id";
$sql=$conn->query($query);
echo "Name: <b>$reportName</b><br>";
echo "Description: <b>$reportDesc</b><br><br>";
echo "Group results by:  ";
echo "<select id=grouping name=grouping>";
echo "<option value=clear>No grouping</option>";
for($i=0;$row=$sql->fetch();$i++){
	echo "<option value=$row[0]>$row[1]</option>";
}
echo "</select>";
//query the database for input parameters
$sql=$conn->query("SELECT param_field, param_name  FROM param WHERE param_report=$report_id");
if($sql->rowCount()>0) { //is there any input parameters for this report?
	echo "<br><br>";
	echo "<b>Input Parameters</b><br>";
	echo "<form name=paramForm>";
	echo "<table>";
	
	for($i=0;$row=$sql->fetch();$i++){	//loop through all results
		echo "<tr><td>$row[0]</td>";
		$ref=$qClass->prepareQuery(array($row[0],$conn->getDatabase(),'',''),3);	//check if there is a referenced table for this attribute
		
		if($ref[0]!=""){	//there's a referenced table
			$extra="lang=__fk";	//set it a 'foreign key'
			echo "<td><input type=text name=op_$row[0] id=op_$row[0] readonly value='=' size=1></td>";
		} else {
			//change search path to information schema
			$conn->dbInfo();
			$sql2=$conn->query("SELECT data_type FROM columns WHERE table_schema='".$conn->getDatabase()."' AND column_name='$row[0]'");
			$row2=$sql2->fetch();
			if($row2[0]=="datetime" or $row2[0]=="date"){//is this a datetime field
				$extra="onfocus=showCalendarControl(this) readonly=readonly";	
			} else {
				$extra="";
			}
			echo "<td>";
			//loop through all operator options
			echo "<select name=op_$row[0] id=op_$row[0]>";
			echo "<option value=0>=</option>";
			echo "<option value=1><></option>";
			echo "<option value=2>></option>";
			echo "<option value=3><</option>";
			echo "</select>";		
			echo "</td>";
		}
		echo "<td><input type=text name=$row[0] id=$row[0] $extra></td></tr>";
	}
	echo "</table>";
	echo "</form>";
	echo "<input type=button name=inputSubmit id=inputSubmit value='Submit input parameters' onclick=setParams('$report_id')>";
}
echo "</div>";
echo "<br>";
echo "<body onload=\$(document).createGrid({report_id:$report_id});>";
echo "<table id=list></table>";
echo "<div id=pager></div>"; 



?>
<form method="post" action="csvExport.php">
    <input type="hidden" name="csvBuffer" id="csvBuffer" value="" />
</form>
