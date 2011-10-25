<?php 
session_start();
require_once("../.htconnect.php");
//call database
$conn=new dbConnection();
if(isset($_GET['type'])){
	if(isset($_GET['resource']))	$resource_id=$_GET['resource'];
	if(isset($_GET['resource_ip']))	$ip=$_GET['resource_ip'];	
	
	if(setcookie('resource_ip',$ip,time()+157680000,'/')){
		$query="UPDATE resource SET resource_mac='$ip' WHERE resource_id='$resource_id'";
		$conn->query($query);
		echo "Cookie is baked!";
	} else {
		echo "Unable to make cookie!";
	}
	exit();
} 
?>

<script type="text/javascript" src="../../agendo/js/jquery-1.6.js"></script>
<script type="text/javascript">

function makecookie(){
	var resource = $("#resource").val();
	var ip = $("#ip").val();
	if(resource=="" || resource==0 || ip==""){
		alert("You must enter all the information to create the cookie");
		return;
	} else {
		//set url for ajax request
		url="cookie.php"; 
		//ajax request
		$.get(url,{
			type:0,
			resource:resource,
			resource_ip:ip},
			function(data){
				alert(data);
			});
	}
}

function clearField(){
	$("#resource").val("");
	$("#ip").val("");
}
</script>
<style>
body{
	margin:0;
	background: #164f55;
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px; 
	line-height:20px; 
    text-align: justify;
    color:#f7c439;
}

fieldset{
	padding:20px;	
	}

</style>

<?php 




//get all resource available for in situ confirmation
$query="SELECT DISTINCT resource_id, resource_name FROM resource WHERE resource_status=3";
echo "<fieldset>";
echo "<legend>Make cookie</legend>";
echo "Registered equipment with in situ user confirmation";
echo "<br>";
echo "<select name=resource id=resource>";
echo "<option value=0 selected>Select a resource...</option>";
//loop through all results
foreach($sql=$conn->query($query) as $row){
	echo "<option value=$row[0]>$row[1]</option>";
}
echo "</select>";
echo "<br><br>";
echo "Computer IP address";
echo "<br>";
//set text field for ip address
echo "<input type=text id=ip name=id>";
echo "<br><br>";
//create cookie
echo "<input type=button name=makecookie id=makecookie value='Make cookie' onclick=makecookie()>";
echo "<input type=reset name=clear id=clear value='Clear fields' onclick=clearField()>";
echo "</fieldset>";

?>