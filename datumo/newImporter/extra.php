<?php

//PHP includes
require_once "../session.php";
$user_id=startSession();
require_once "../__dbConnect.php";

//PHP classes
$conn=new dbConnection();

if(isset($_GET['ajax'])){
	if(isset($_GET['vendor']))	$vendor_name=$_GET['vendor'];
	$query="SELECT 1 FROM vendor WHERE vendor_name='$vendor_name'";
	$sql=$conn->query($query);
	if($sql->rowCount()){
		echo "<li><b>This is a registered vendor</b></li>";
		$query="SELECT DISTINCT vendor_id FROM vendor, product WHERE product_vendor=vendor_id AND vendor_name='$vendor_name'";
		$sql=$conn->query($query);
		echo "<br>";
		if($sql->rowCount()==1){
			echo "<li><span style='color:#00FF00'>There are products registered for this vendor</span></li>";
		} else {
			echo "<li><span style='color:#FF0000'>No products registered</span></li>";
		}
	} else {
		echo "<li><b>This is an unregistered vendor</b></li>";
		echo "<br>";
		echo "<li>If you choose to import a file from this vendor, a new entry will be created in the database</li>";
	}
	
	exit;
}

?>

<!doctype html>  
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>

<!-- BEGIN Meta tags -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<!-- BEGIN Navigation bar CSS - This is where the magic happens -->
<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/autoSuggest.css">
<!-- END CSS -->

<!-- BEGIN javascript -->
<script type="text/javascript" src="../js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="../js/jquery-ui-1.8.14.custom.js"></script>
<script type="text/javascript">

$(document).ready(function(){
	$("input[lang=__fk]").focus(function(){
		$(this).autocomplete({
			source:"../autoSuggest.php?field="+this.id,
			minLength:3,
			dataType:"json"
		});
	});

	$("#validate").click(function(){
		$.get("extra.php?ajax",{
			vendor:$("#product_vendor").val()
		},
		function(data){
			$("#extraInfo").html(data);
		});
	});
});



</script>
<!-- END javascript -->

</head>
<body>

<?php 

//get url variables
if(isset($_GET['objName']))	$objName=$_GET['objName'];

if(isset($_GET['fields'])){	//check for table headers
	echo "<h3>$objName</h3>";
	echo "<hr>";
	//set search path to information schema
	$conn->dbInfo();
	$query="SELECT column_name FROM columns WHERE table_schema='".$conn->getDatabase()."' AND table_name='$objName'";
	//display all table headers
	foreach ($conn->query($query) as $row){
		//format string
		$data=substr($row[0],strlen($objName)+1,strlen($row[0])-strlen($objName)-1);
		echo "<li>$data</li>";
		
	}
}

if(isset($_GET['check'])){
	echo "Type the vendor's name";
	echo "<input type=text name=product_vendor id=product_vendor lang=__fk class=fk size=40>";	
	echo "<input type=button name=validate id=validate value='Validate vendor'>";
	echo "<hr>";
	echo "<div id=extraInfo>";
	echo "</div>";
	
}





?>

</body>
</html>