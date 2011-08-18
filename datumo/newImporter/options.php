<?php  
//PHP includes
require_once "../session.php";
$user_id=startSession();
require_once "../__dbConnect.php";
require_once "../resClass.php";

?>
<link href="css/importer.css" rel="stylesheet" type="text/css">
<link href="../css/jquery.jnotify.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="../js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/auxJS.js"></script>
<script type="text/javascript" src="../js/jquery.jnotify.js"></script>
<script type="text/javascript">

$(document).ready(function(){
	/**
	 * in this script goes the code for each link found in the TIPS and RULES list
	 * These links have very specific targets
	 */

	$("a.fields").click(function(){
		objName=$("#targetTable").val();
		if(objName==0){
			$.jnotify("Target table not selected",true);
			return;
		}
		window.open('extra.php?fields&objName='+objName,'_blank','width=250px,height=300px,scrollbars=yes,menubar=no');
	});
	
	$("a.check").click(function(){
		objName=$("#targetTable").val();
		if(objName!="product"){
			$.jnotify("Target table must be PRODUCT in order to check for vendors",true);
			return;
		}
		window.open('extra.php?check&objName='+objName,'_blank','width=250px,height=200px,scrollbars=yes,menubar=no');
	});
});

</script>
<?php

//call classes
$conn=new dbConnection();
$perm=new restrictClass();

//get user level
$perm->userInfo($user_id);
$level=$perm->getUserLevel();
$db="tables_in_".$conn->getDatabase();
switch ($level){
	case 0: //admin
		//display some tables
		$query="Show tables WHERE $db IN ('account','department','institute','manufacturer','product','user','vendor')";
		break;
	case 1: //manager
	case 2:	//Regular user
		//do not allow
		$msg="You are not allowed to access this page";
		echo "<h1 style='text-align:center;margin-top:40px;'>$msg</h1>";
		exit;
		break;
	case 3: //External user
		//allow only to import products
		$query="Show tables WHERE $db='product'";
		break;
	default:
		$query="Show Tables";
}

echo "<fieldset class=holderFieldset>";
echo "<legend>Import options</legend>";

//display import rules
echo "<div class=rules>";
echo "<h3>Tips and rules</h3>";
echo "<ol>";
echo "<li>The first row of the .csv file must contain headers that will identify each column. 
<b><a href=javascript:void(0) class=fields>Click here to view the list of available headers for the chosen table</a></b></li>";
echo "<li>If importing a product list make sure you have a column with the name of the vendor. This value <b>CANNOT BE NULL</b>. 
The name of the vendor must match any value stored in our database. <b><a href=javascript:void(0) class=check>Click here 
for a name checking</a></b>. If you have products in our database use this tool to check the correct name of your company.</li>";
echo "<li>Avoid using unusual characters in your file. They won't be rejected but data integrity may be lost during the import.</li>";
echo "<li>If you wish to import an Economato list you must add a column in your file named <b>Type</b>. Values throughout this
column must be <b>Economato</b> for products placed at Economato or <b>External</b> for external products.</li>";
echo "<li>Think carefully before choosing any delete option. If you have any doubts please choose the first option 
(<b>Do not delete<b>) or <a href=mailto:info@cirklo.org>contact our administrator for advice</a>.</li>";
echo "</ol>";
echo "</div>";

echo "<form name=options method=post enctype='multipart/form-data' style='float:left'>";

echo "<table class=main border=0>";
//target table selection
echo "<tr valign=top>";
echo "<td width=200px><span class=title>Select the target table</span></td>";
$sql=$conn->query($query);
echo "<td align=right><select name=targetTable id=targetTable onChange=ajaxEquiDD(this,'targetUnique') style='width:150px'>";
echo "<option value=0 selected>select table...</option>";
//loop through all database tables
for($i=0;$row=$sql->fetch();$i++){
	echo "<option value=$row[0]>$row[0]</option>";	
}
echo "</select></td>";
echo "</tr>";

//Unique key selection
echo "<tr valign=top>";
echo "<td><span class=title>Select an unique key</span><br>";
echo "<span class=desc>The <b>unique key</b> is a field value that is not repeated throughout all rows of the file</span>";
echo "</td>";
echo "<td align=right><select name=targetUnique id=targetUnique style='width:150px'>";
echo "<option value=0 selected>select field...</option>";
echo "</select></td>";
echo "</tr>";

//matching key checking
echo "<tr valign=top>";
echo "<td><span class=title>Use matching key</span><br>";
echo "<span class=desc>The <b>matching key</b> is a field value that remains unchanged through all rows of your file</span>";
echo "</td>";
echo "<td align=right><input type=checkbox name=cbMatching id=cbMatching onclick=setMatching('cbMatching','targetMatching','targetTable')>
&nbsp;&nbsp;";
echo "<select name=targetMatching id=targetMatching disabled style='width:150px'>";
echo "<option value=0 selected>select field...</option>";
echo "</select></td>";
echo "</tr>";
echo "<tr>";
echo "<td><span class=title>Delete current data</span></td>";
echo "<td><select name=dataErase id=dataErase>";
echo "<option value=0 selected>Do not delete</option>";
if($level==0)	echo "<option value=1>Delete all table data</option>";	//allow this option only for administration
echo "<option value=2>Delete matching key related</option>";
echo "</select></td>";
echo "</tr>";
echo "</table>";

echo "<br><br>";
//echo "<div style='float:left'>";
//select the excel file
echo "<span class=title>Select file to import (.csv)</span><br>";
echo "<input type=file name=file id=file value='Choose file' size=40>";

echo "<br><br>";
echo "<input type=button id=startValidation name=startValidation value='Check import' onclick=goValidation()>";
//echo "</div>";
echo "</form>";



echo "</fieldset>";


?>