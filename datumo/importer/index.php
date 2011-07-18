<script type="text/javascript">

function start(){
	document.body.style.cursor = "wait";
	var CurForm = eval("document.import");
	var table = document.getElementById("tables").value;
	var field = document.getElementById("Fields").value;
	field = document.getElementById("Fields").options[field].text;
	var matchcol = document.getElementById("Matching").value;
	matchcol = document.getElementById("Matching").options[matchcol].text;
	var match = document.getElementById("match").value;
	CurForm.action = "checkImport.php?table=" + table + "&field=" + field + "&matchcol=" + matchcol + "&match=" + match;
	CurForm.submit();
	CurForm.subbutton.enabled=false;
	document.body.style.cursor = "default";
}

function ajaxEquiDD(objTagOri,objNameDest) {
	document.body.style.cursor = "default";
	var xmlhttp,url;
	objTagDest=document.getElementById(objNameDest);
	if(objNameDest == 'Matching'){
		objTagOri = document.getElementById("tables");
	}
	while (objTagDest.firstChild) {objTagDest.removeChild(objTagDest.firstChild);}

	    if (window.XMLHttpRequest){ 
	        xmlhttp=new XMLHttpRequest();
	    } else {
	        alert("Your browser does not support XMLHTTP!");
	        exit;
	    }
	    optionItem = document.createElement('option');
	    objTagDest.appendChild(optionItem);
	    optionItem.value='';
	    optionItem.appendChild(document.createTextNode('Select column...'));
	    
	    //alert(Page + objTagOri.value);
	    xmlhttp.open("GET","ajaxtable.php?table=" +objTagOri.value,false);
	    xmlhttp.send(null);
	    
	    var str=xmlhttp.responseText;
	    var a=new Array();
	    var b=new Array();
	   // alert(str);
	    a=str.split("<name>");
	    for (i=1;i<a.length;i++) {
		    optionItem = document.createElement('option');
		    b=a[i].split("<value>");
		    optionItem.value=b[1];
		    optionItem.appendChild(document.createTextNode(b[0]));
		    objTagDest.appendChild(optionItem);
	    }
	}
</script>


<?php



require_once("__dbConnect.php");
mysql_select_db("information_schema");

$db = database(1);

echo "<form name=import method=post enctype='multipart/form-data'>";
$sql = "SELECT TABLE_NAME FROM TABLES WHERE TABLE_SCHEMA='$db' AND TABLE_TYPE='BASE TABLE'";
$res = mysql_query($sql) or die (mysql_error());
echo "Select table to update&nbsp;";
echo "<select name=tables id=tables onChange=\"ajaxEquiDD(this,'Fields')\">";
echo "<option id=0>Select a table...</option>";
while($row = mysql_fetch_array($res)){
	echo "<option id='".$row[0]."'>".$row[0]."</option>";
}
echo "</select>";
echo "<br><br>";
echo "Select unique key&nbsp;";
echo "<select name=Fields id=Fields onChange=\"ajaxEquiDD(this,'Matching')\"></select>";
echo "<br><br>";
echo "Select matching key&nbsp;";
echo "<select name=Matching id=Matching></select>&nbsp;&nbsp;<input type=text id=match name=match>";
echo "<br><br>";
echo "<input type=file name=file id=file value='Choose file'>";
echo "<input type=submit id=subbutton value='Check import' onclick=\"javascript:start();\">";
echo "</form>";
echo "<br>";
echo "<table border=0 cellspacing='10'><tr><td valign=top>";
echo "<table border=0 align=left>";
echo "<tr><td colspan=2><font size=4spx>How to proceed</font></td></tr>";
echo "<tr><td width=25px>1.</td><td><b>Table to update</b>: product</td></tr>";
echo "<tr><td width=25px>2.</td><td><b>Unique key</b>: manufacturer_cat_no</td></tr>";
echo "<tr><td width=25px>3.</td><td><b>Matching key</b>: vendor</td></tr>";
echo "</table>";
echo "</td><td valign=top>";
echo "<table border=0 align=left>";
echo "<tr><td colspan=2><font size=4spx>Rules</font></td></tr>";
echo "<tr><td width=25px>1.</td><td>You <b>must</b> have only one <b>vendor</b> per file. Otherwise you will not be able to check the file.</td></tr>";
echo "<tr><td width=25px>2.</td><td>File <b>must</b> contain headers with a specific format. Click here to view the correct file headers.</td></tr>";
echo "<tr><td width=25px>3.</td><td>Some fields have specific datatypes (Integer, decimal, strings, etc...).</tr>";
echo "<tr><td width=25px>4.</td><td>Avoid empty cells or special characters such as ',\",~,ç, among others.</tr>";
echo "</table>";
echo "</td><td valign=top>";
echo "<table border=0 align=left>";
echo "<tr><td colspan=2><font size=4spx>Headers</font></td></tr>";
echo "<tr><td width=25px>1.</td><td>Manufacturer</td></tr>";
echo "<tr><td width=25px>2.</td><td>Manufacturer_cat_no</td></tr>";
echo "<tr><td width=25px>3.</td><td>Vendor</td></tr>";
echo "<tr><td width=25px>4.</td><td>Vendor_cat_no</td></tr>";
echo "<tr><td width=25px>5.</td><td>Description</td></tr>";
echo "<tr><td width=25px>6.</td><td>Price</td></tr>";
echo "<tr><td width=25px>7.</td><td>Quantity</td></tr>";
echo "<tr><td width=25px>8.</td><td>Units</td></tr>";
echo "<tr><td width=25px>9.</td><td>Category</td></tr>";
echo "<tr><td width=25px>10.</td><td>Unitprice</td></tr>";
echo "<tr><td width=25px>11.</td><td>Listprice</td></tr>";
echo "<tr><td width=25px>12.</td><td>VAT</td></tr>";
echo "<tr><td width=25px>13.</td><td>Special_conditions</td></tr>";
echo "<tr><td width=25px>14.</td><td>Delivery_time</td></tr>";
echo "<tr><td width=25px>15.</td><td>Link</td></tr>";
echo "<tr><td width=25px>16.</td><td>Obs</td></tr>";
echo "</table>";
echo "</td></tr>";
echo "</table>";


?>