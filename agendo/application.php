<?php
require_once("commonCode.php");
?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/admin.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/checkfields.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>

<script type="text/javascript">
function getValue(id,target){
    var val = document.getElementById(id).value;
    if(val == '0'){
        document.getElementById('GEDepartment').removeAttribute('readonly');
        document.getElementById(target).removeAttribute('readonly');

    } else {
        document.getElementById('GEDepartment').setAttribute('readonly','readonly');
        document.getElementById('GEDepartment').value = '';
        document.getElementById(target).setAttribute('readonly','readonly');
    }
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    url="ajaxdpt.php?val=" + val;
    xmlhttp.open("GET", url, false);
    xmlhttp.send(null);
			    
    var str = xmlhttp.responseText;
    document.getElementById(target).value = str;
}

function checktrain(id){
    var str = id.substring(0, id.length-6);
    document.getElementById(str).checked = document.getElementById(id).checked;
}

</script>

<?php
// require_once(".htconnect.php");
// require_once("__dbHelp.php");
require_once("errorHandler.php");

$error = new errorHandler;

echo "<form method=post name=application>";
echo "<table><tr><td><font size=5px>Personal information</font></td></tr>";
echo "<tr><td><font size=2px>All fields are mandatory</font></td></tr></table>";
echo "<table border=0>";
echo "<tr><td colspan=2><br></td></tr>";
echo "<tr><td width=100px>First name</td><td><input type=text name='First name' id='First name'></td></tr>";
echo "<tr><td>Last name</td><td><input type=text name='Last name' id='Last name'></td></tr>";
echo "<tr><td>Department</td><td><select name='Department' id='Department' onchange=\"javascript:getValue(this.id,'Institute');\">";
echo "<option value='0'>--- Select / Other ---</option>";
$sql = "SELECT department_id, department_name FROM department ORDER BY department_name";
$res = dbHelp::mysql_query2($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
while($row = dbHelp::mysql_fetch_row2($res)){
    echo "<option value='".$row[0]."'>".$row[1]."</option>";
}
echo "</select>";
echo "\tOther <input type=text name='GEDepartment' id='GEDepartment' value=''>";
echo "</td></tr>";
echo "<tr><td>Institute</td><td><input type=text    name=Institute id=Institute size=35></td></tr>";
echo "<tr><td>Work phone</td><td><input type=text name=Phone id=Phone></td></tr>";
echo "<tr><td>Phone extension</td><td><input type=text name='Phone extension' id='Phone extension'></td></tr>";
echo "<tr><td>Mobile</td><td><input type=text name='Mobile' id='Mobile'></td></tr>";
echo "<tr><td>Email</td><td><input type=text name='Email' id='Email'></td></tr>";
echo "<tr><td><br></td><td></td></tr>";
echo "</table>";

echo "<table><tr><td><font size=5px>Select the resource you want to use</font></td></tr>";
echo "<tr><td><font size=2px>If you don't know how to use the equipment ask for assistance</font></td></tr></table>";
echo "<table border=0>";
echo "<tr><td colspan=2><br></td></tr>";
echo "<tr><td width=100px>Resource Type</td><td>";
echo "<select name=Type id=Type onChange=\"ajaxEquiDD(this,'Resource')\">";
$sql = "SELECT resourcetype_id, resourcetype_name FROM resourcetype";
echo "<option id=0>Select Resource...</option>";
$res = dbHelp::mysql_query2($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
while($row = dbHelp::mysql_fetch_row2($res)){
    echo "<option value='".$row[0]."'>".$row[1]."</option>";
}
echo "</select></td></tr>";

echo "<tr><td>Resource</td><td><select name=Resource id=Resource></select></td></tr>";
echo "<tr><td><br></td><td></td></tr>";
echo "</table>";
/*
echo "<table><tr><td><font size=5px>Select the resources you would like to use</font></td></tr>";
echo "<tr><td><font size=2px>You have to select at least one resource</font></td></tr></table>";
echo "<table border=0 cellspacing='10'>";
echo "<tr><td colspan=4><br></td></tr>";
echo "<tr><td colspan=2><br></td><td align=center></td><td align=center>Require training?</td></tr>";

$sql = "SELECT type_id, type_name FROM type";
$res = dbHelp::mysql_query2($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
while ($row = dbHelp::mysql_fetch_array2($res)){
    $newsql = "SELECT resource_name, resource_id FROM resource, type WHERE resource_type = type_id AND resource_type = ".$row[0];
    $newres = dbHelp::mysql_query2($newsql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $newsql, '', ''));
    if(dbHelp::mysql_numrows2($newres) == 0) {} //do nothing
    else{
        echo "<tr><td colspan=4><strong><font size=2px>".$row[1]."</font></strong></td></tr>";
        while($line = dbHelp::mysql_fetch_array2($newres)){
            echo "<tr><td></td><td>".$line[0]."</td><td align=center><input type=checkbox name='__".$line[0]."' id='__".$line[0]."' ></td><td align=center><input type=checkbox name='__".$line[0]."_train' id='__".$line[0]."_train' onchange=\"javascript:checktrain(this.id);\"></td></tr>";
        }
    }
}
echo "<tr><td colspan=3><br></td></tr>";*/
echo "<table border=0>";
echo "<tr><td><input type=button value=Submit onclick=\"javascript:validate_form();\"></td></tr>";
echo "</table>";
echo "</form>";

?>