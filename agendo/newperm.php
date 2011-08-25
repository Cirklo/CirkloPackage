<?php
require_once("commonCode.php");
importJs();
?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/admin.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/checkfields.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript">

function checkpermission(){
    var CurForm = eval("document.permission");
    for(var i = 0; i < CurForm.length-1; i++){
        if(CurForm[i].value == ''){
            // alert("You need to enter all fields to submit request!");
            showMessage("You need to enter all fields to submit request!");
            return;
        }
    }
    CurForm.action = "ajaxform.php";
    CurForm.submit();
}

</script>
<?php

// require_once(".htconnect.php");
// require_once("__dbHelp.php");
require_once("errorHandler.php");

//call classes
$error = new errorHandler;

echo "<table><tr><td><font size=5px>Permission to use a resource</font></td></tr>";
echo "<tr><td><font size=2px>All fields are mandatory</font></td></tr></table>";
echo "<br>";
echo "<table border=0>";
echo "<form method=post name=permission>";
echo "<tr><td>User name</td><td><input type=text name='user_login' id='user_login'></td></tr>";
echo "<tr><td>Password</td><td><input type=password name='pwd' id='pwd'></td></tr>";
echo "<tr><td width=100px>Resource Type</td><td>";
echo "<select name=Type id=Type onChange=\"ajaxEquiDD(this,'Resource')\">";
$sql = "SELECT resourcetype_id, resourcetype_name FROM resourcetype";
echo "<option id=0>Select Resource...</option>";
$res = dbHelp::query($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
while($row = dbHelp::fetchRowByIndex($res)){
    echo "<option value='".$row[0]."'>".$row[1]."</option>";
}
echo "</select></td></tr>";
echo "<tr><td>Resource</td><td><select name=Resource id=Resource></select></td></tr>";
echo "<tr><td>Training</td><td><input type=checkbox name=assistance id=assistance></td></tr>";
echo "</form>";
echo "<tr><td><br></td></tr>";
echo "<tr><td colspan=2><input type=button value=Submit onclick=\"javascript:checkpermission();\"</td></tr>";
echo "</table>";

?>