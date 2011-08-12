<?php 
require_once "../session.php";
$user_id=startSession();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Basket display</title>
<link href="../css/tipTip.css" rel="stylesheet" type="text/css">
<link href="css/requisitions.css" rel="stylesheet" type="text/css">
<link href="../css/redmond/jquery-ui-1.8.9.custom.css" rel="stylesheet" type="text/css">
<link href="../js/src/css/ui.jqgrid.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="../js/src/grid.loader.js"></script>
<script type="text/javascript" src="js/jquery.basket.js"></script>
<script type="text/javascript" src="../js/jquery.tipTip.js"></script>
<script type="text/javascript" src="../js/ajax.js"></script>
<script type="text/javascript" src="../js/jquery.print.js"></script>

<script type="text/javascript">
//initialize tiptip plugin
$(document).ready(function(){
	$("*").tipTip(); //tiptip initialization
});
</script>
</head>
<?php 

require_once "../__dbConnect.php";
require_once "../resClass.php";

if(isset($_GET['type'])){	$type=$_GET['type'];}

//call database class
$conn=new dbConnection();
$res=new restrictClass();
$database=$conn->getDatabase();//set database name

echo "<body onload=\$(document).createGrid({display:'$type'});>";
echo "<table id=list></table>";
echo "<div id=pager></div>"; 
echo "<br>";

//get user info
$res->userInfo($user_id);
if($res->getUserLevel()!=2){
	echo "<div id=accountContainer class=account>";
	echo "<table>";
	echo "<tr><td>Contact</td><td><input type=text name=contact id=contact></td></tr>";
	echo "<tr><td colspan=2>Internal comments</td></tr>";
	echo "<tr><td colspan=2><textarea name=iComments id=iComments rows=5 cols=45></textarea></td></tr>";
	echo "<tr><td colspan=2>Select an account to proceed</td></tr>";
	if($res->getUserLevel()==0){ //Is this an administrator?
		$sql=$conn->prepare("SELECT account_id, account_number, account_project, account_budget FROM $database.account WHERE account_start<NOW() AND account_end>NOW() AND account_id<>0 ORDER BY account_number");
			} else { //is this a manager?
		$sql=$conn->prepare("SELECT account_id, account_number, account_project, account_budget FROM $database.account WHERE account_start<NOW() AND account_end>NOW() AND account_id<>0 AND account_dep IN (SELECT user_dep FROM $database.user WHERE user_id=$user_id) UNION SELECT account_id, account_number, account_project, account_budget FROM $database.account, $database.accountperm WHERE accountperm_account=account_id AND account_start<NOW() AND account_end>NOW() AND account_id<>0 AND accountperm_user=$user_id ORDER BY account_number");
	}	
	$sql->execute();
//	echo $sql->queryString;
	echo "<tr><td>";
	echo "<select name=accountList id=accountList>";
	echo "<option id=0 selected>-----------------</option>";
	for($i=0;$row=$sql->fetch();$i++){
		echo "<option id=$row[0] title='$row[2]'>$row[1]</option>";
	}
	echo "</select>";
	echo "</td>";
	//button to submit order
	echo "<td><input type=button id=submit value='Submit basket' name=$type></td>";
	echo "</tr>";
	echo "</table>";
	echo "<br>";
	echo "<div id=accountDetails></div>";
	echo "</div>";
	
}


//div to select account
echo "</body>";
echo "</html>";
?>
<form method="post" action="csvExport.php">
    <input type="hidden" name="csvBuffer" id="csvBuffer" value="" />
</form>
