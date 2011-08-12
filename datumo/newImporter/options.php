<?php  
//PHP includes

require_once "../session.php";
$user_id=startSession();
require_once "../__dbConnect.php";

?>
<link href="css/importer.css" rel="stylesheet" type="text/css">
<link href="../css/jquery.jnotify.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="../js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/auxJS.js"></script>
<script type="text/javascript" src="../js/jquery.jnotify.js"></script>
<?php

//call classes
$conn=new dbConnection();

echo "<fieldset class=holderFieldset>";
echo "<legend>Import options</legend>";
echo "<form name=options method=post enctype='multipart/form-data'>";

echo "<table class=main border=0>";
//target table selection
echo "<tr valign=top>";
echo "<td width=200px><span class=title>Select the target table</span></td>";
$query="show tables";
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
echo "<option value=1>Delete all data</option>";
echo "<option value=2>Delete matching key related</option>";
echo "</select></td>";
echo "</table>";

echo "<br><br>";
//select the excel file
echo "<span class=title>Select file to import (.csv)</span><br>";
echo "<input type=file name=file id=file value='Choose file' size=40>";

echo "<br><br>";
echo "<input type=button id=startValidation name=startValidation value='Check import' onclick=goValidation()>";
echo "</form>";
echo "</fieldset>";


?>