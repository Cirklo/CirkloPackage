<?php 
require_once "../session.php";
$user_id=startSession();

if(isset($_GET['auth'])){
	require_once "ldap.php";
	//require_once "requisitions.php";
	$ldap=new ldapIGCBooking();
	// If LDAP access is active connect and bind to server.
	$ldap_auth=false;
	//get url variables from user authentication
	$username=$_GET['username'];
	$pass=$_GET['pass'];
	if($ldap->connect()){
		$ldap_auth=$ldap->bind($username,$pass);	
	}
	//if the authentication is correct
	//update basket contact/update basket received date
	if($ldap_auth){
		$basket_id=$_GET['basket_id'];
		$query="UPDATE basket SET basket_obs=CONCAT('$username','\n',basket_obs),basket_delivery_date=NOW() WHERE basket_id=$basket_id";
		try{
			//$sql=$conn->query($query);
			echo "Basket successfully delivered";
		} catch (Exception $e) {
			echo "Unable to perform action";
		}
	}else{
		echo "Wrong authentication. Please do it again";
	}
	exit();
}

?>

<link href="css/portable.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/jquery.basket.js"></script>

</script>
<?php

require_once "__dbConnect.php";

//call database connection class
$conn=new dbConnection();

$database=$conn->getDatabase();
//GET the list of ordered baskets
$state="Ordered";
$query = "SELECT DISTINCT basket_id, department_name, basket_sap, vendor_name, basket_obs, type_name FROM basket, type, department, account, vendor, request, product WHERE vendor_id=product_vendor AND product_id=request_number AND request_origin='product' AND request_basket=basket_id AND basket_type=type_id AND basket_user=department_id AND basket_account=account_id AND basket_state IN (SELECT state_id FROM $database.state WHERE state_name='$state')";	
$query.= "UNION SELECT DISTINCT basket_id, department_name, basket_sap, vendor_name, basket_obs, type_name FROM basket, type, department, account, vendor, request, myproduct WHERE vendor_id=myproduct_vendor AND myproduct_id=request_number AND request_origin='myproduct' AND request_basket=basket_id AND basket_type=type_id AND basket_user=department_id AND basket_account=account_id AND basket_state IN (SELECT state_id FROM $database.state WHERE state_name='$state')";	
//echo $query;
$sql=$conn->query($query);

echo "<table border=0 class=main>";
//write headers
echo "<tr>";
echo "<th>ID</th>";
echo "<th>Department</th>";
echo "<th>SAP number</th>";
echo "<th>Vendor</th>";
echo "<th>Observations</th>";
echo "<th>Type</th>";
echo "<th></th>";
echo "</tr>";
//loop through all results
while($row=$sql->fetch()){
	echo "<tr>";
	//go through all columns of the query
	echo "<td><a href=javascript:void(0)>$row[0]</a></td>";
	for($j=1;$j<$sql->columnCount();$j++){
		echo "<td>$row[$j]</td>";
	}
	echo "<td><input type=button id=receive_$row[0] name=receive_$row[0] value=Receive class=btn onclick=updateBasket('$row[0]')></td>";
	echo "</tr>";
}
echo "</table>";
echo "<div id=igc_user class=igc_user>";
echo "Basket number <input type=text id=basket_number name=basket_number size=1 readonly><br>";
echo "IGC Username:<br>";
echo "<input type=text name=igc_email id=igc_email size=29>";
echo "<br><br>";
echo "IGC Password:<br>";
echo "<input type=password id=igc_pass name=igc_pass><br><br>";
echo "<input type=button class=btn id=validate name=validate value=Validate onclick=validate()>";
echo "&nbsp;&nbsp;&nbsp;<a href=javascript:void(0) onclick=$('#igc_user').css('display','none')>Close</a>";
echo "</div>";

?>