<?php

//script to be called through an ajax request

//includes
require_once "../session.php";
$user_id = startSession();
require_once "../__dbConnect.php";

if(isset($_GET['type'])){	
	$call=$_GET['type'];
	switch ($call){
		case 0:
			addToBasket($user_id);
			break;
		case 1:
			checkKey();
			break;
		case 2:
			accountDetails($user_id);
			break;
		case 3:
			submitBasket($user_id);
			break;
		case 4:
			getPerm($user_id);
			break;
		case 5:
			statePermission($user_id);
			break;
		case 6:
			changeState($user_id);
			break;
		case 7:
			userLevel($user_id);
			break;
		case 8:
			newVendor($user_id);
			break;
	}

}

/**
 * 
 * Method to dynamically add a product to the basket. 
 * If the product already exists it updates the quantity
 * 
 */

function addToBasket($user_id){
	require_once "requisitionsClass.php";
	
	//should send product id and quantity through an array
	//URL variables
	if(isset($_GET['table']))	$table=$_GET['table'];
	if(isset($_GET['item'])){
		$p=$_GET['item'];
		$item=$p[0];
		$quantity=$p[1];
	}
	
	//call database class
	$conn=new dbConnection();
	$conn->dbInfo(); 		//set search path to main database
	$req=new reqClass(); 	//call requisitions class
	
	//create a new basket if it doesn't exist
	$req->createBasket($user_id,"");
	
	//which product type is this??
	$sql = $conn->prepare("SELECT type_id, type_name FROM ".$conn->getDatabase().".type WHERE type_id IN (SELECT ".$table."_type FROM ".$conn->getDatabase().".$table WHERE ".$table."_id=$item)");
	try{
		$sql->execute();
		$row=$sql->fetch();
		$type=$row[0];
		$type_name=$row[1];
		//check if the product is already in the basket
		$sql=$conn->prepare("SELECT COUNT(*) FROM ".$conn->getDatabase().".request, ".$conn->getDatabase().".basket WHERE request_basket=basket_id AND basket_type IN (SELECT ".$table."_type FROM requisitions.$table WHERE ".$table."_id=$item) AND basket_state=0 AND basket_user IN (SELECT user_dep FROM ".$conn->getDatabase().".user WHERE user_id=$user_id) AND request_number=$item AND request_origin='$table'");
		//echo $sql->queryString;
		try{
			$sql->execute();
			$row=$sql->fetch();
			if($row[0]==0){ //this product is not on the basket
				$sql=$conn->prepare("INSERT INTO ".$conn->getDatabase().".request (request_basket, request_origin, request_number, request_quantity, request_price) SELECT (SELECT basket_id FROM ".$conn->getDatabase().".basket WHERE basket_state=0 AND basket_type=$type AND basket_user IN (SELECT user_dep FROM ".$conn->getDatabase().".user WHERE user_id=$user_id)), '$table', $item, $quantity, ".$table."_price FROM ".$conn->getDatabase().".$table WHERE ".$table."_id=$item");
				//echo $sql->queryString;
				$sql->execute();
			} else { //the product already is on the basket
				$sql=$conn->prepare("UPDATE ".$conn->getDatabase().".request SET request_quantity=request_quantity+$quantity WHERE request_number=$item AND request_basket IN (SELECT basket_id FROM ".$conn->getDatabase().".basket WHERE basket_state=0 AND basket_type=$type AND basket_user IN (SELECT user_dep FROM ".$conn->getDatabase().".user WHERE user_id=$user_id))");
				$sql->execute();
			}
			$nrows=basketRows($type,$user_id);
			echo "You have $nrows item(s) in your $type_name basket";
		} catch (Exception $e){
			//echo $sql->queryString;
			echo "Error: Item(s) not added to the basket!";
		}
		
	} catch (Exception $e){
		echo $e->getMessage();
	}
}
/**
 * Check matching key depending on the item type 
 */

function checkKey(){
	//call database class
	$conn=new dbConnection();
	$database=$conn->getDatabase();
	
	//get url variables
	if(isset($_GET['stype']))	$type=$_GET['stype'];
	//query the database for its matching key (if any)
	$sql=$conn->prepare("SELECT type_grouping FROM $database.type WHERE type_name='$type'");
	$sql->execute();
	$row=$sql->fetch();
	echo $row[0];
	

}

/**
 * Counts the number of rows that exist in an active basket.
 * Maybe this method can be used with other basket states
 */

function basketRows($type,$user_id){
	//call database class
	$conn=new dbConnection();
	$sql=$conn->prepare("SELECT COUNT(*) FROM ".$conn->getDatabase().".request WHERE request_basket IN (SELECT basket_id FROM ".$conn->getDatabase().".basket WHERE basket_state=0 AND basket_user IN (SELECT user_dep FROM ".$conn->getDatabase().".user WHERE user_id=$user_id) and basket_type=$type)");
	$sql->execute();
	$row=$sql->fetch();
	//echo $sql->queryString;
	return $row[0];
}

/**
 * Ajax response
 * Writes account details in a div
 */

function accountDetails($user_id){
	//call database class
	$conn=new dbConnection();
	$database=$conn->getDatabase();
	//URL variables
	if(isset($_GET['id']))	$account=$_GET['id'];
	
	$sql=$conn->prepare("SELECT account_project, account_budget FROM $database.account WHERE account_number='$account'");
	$sql->execute();
	$row=$sql->fetch();
	echo "<b>Project</b> $row[0]<br>";
	echo "<b>Remaining budget</b>* (EUR) $row[1]<br>";
	echo "*This is an indicative value";
}

/**
 * Method to handle basket submission
 * Need to update account budget when some basket is submitted
 */

function submitBasket($user_id){
	require_once "requisitionsClass.php";
	require_once "../mailClass.php";
	//call database class
	$conn=new dbConnection();
	$database=$conn->getDatabase();
	//other classes
	$req=new reqClass();
	$mail=new mailClass();
	$dpt=array();
	
	//URL variables
	if(isset($_GET['val']))			$arr=$_GET['val'];
	if(isset($_GET['stype']))		$type=$_GET['stype'];
	if(isset($_GET['account']))		$account=$_GET['account'];
	if(isset($_GET['ammount']))		$total=$_GET['ammount'];
	if(isset($_GET['iComments']))	$iComments=$_GET['iComments'];
	if(isset($_GET['contact']))		$contact=$_GET['contact'];
	//if(isset($_GET['department']))	$department_name=$_GET['department'];
	
	$clause="";//initialize variable to build clause
	foreach($arr as $request_id){
		//which department is this from????
		$sql=$conn->query("SELECT department_id, department_name FROM department,request,basket WHERE basket_user=department_id AND request_basket=basket_id AND request_id=$request_id");
		$row=$sql->fetch();
		$dpt[]=$row[0];
		$clause.= " $request_id,";
	}
	//check if this request is allowed or not
	if(sizeof(array_count_values($dpt))==1){ //all request from the same department
		$department_id=$dpt[0];
	} else {
		echo "You cannot order items from different departments.";
		exit();
	}
	//to be used in query down below
	$clause=substr($clause,0,strlen($clause)-1);
	
	//call function to check if there is money left in the account
	$valid_account=checkBudget($account, $total);
	if(!$valid_account){
		echo "Not enough money on this account to proceed with the request!";
		exit();
	}
	//get current basket_id
	$basket_id=$req->actBasket($type, $department_id);
	
	//update contact
	$query = "UPDATE request SET request_contact='$contact' WHERE request_contact='' AND request_basket=$basket_id";
	$conn->query($query);
	
	//update this basket	
	$sql=$conn->prepare("UPDATE basket SET basket_state=1, basket_intObs='$iComments', basket_account=(SELECT account_id FROM $database.account WHERE account_number='$account'), basket_submit_date=NOW() WHERE basket_id=$basket_id");
	try{
		$sql->execute();
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	//Create new basket
	$req->createBasket($user_id, $department_id);
	//get nwe basket id (cannot use lastInsertId method as it returns 0)
	$newBasket=$req->actBasket($type, $department_id);
	//build clause to create new basket

	//add the remaining request to the recently created basket
	$sql=$conn->prepare("UPDATE request SET request_basket=$newBasket WHERE request_id NOT IN ($clause) AND request_basket=$basket_id");
	try{
		$sql->execute();
		
		echo "Basket successfully submitted!";
		$subject="Requisition System: New Basket";
		$msg="Basket submitted at ".date("Y-M-d H:i:s")."\n\n";
		$msg.="Basket ID: $basket_id\n\n";
		$msg.="Check the basket list for more information.\n\n";
		$msg.="This is an automatic message. DO NOT REPLY";
		$to="abretanha@igc.gulbenkian.pt";
		$from="info@cirklo.org";
		$mail->sendMail($subject, $to, $from, $msg);
		
	} catch(Exception $e){
		echo "Basket not submitted. Please contact the administrator for details";
	}
	//echo $sql->queryString;
}

function getPerm($user_id){
	//get this user information
	$res=new restrictClass();
	$res->userInfo($user_id);
	echo $res->getUserLevel();

}

function statePermission($user_id){
	//local includes
	require_once "requisitionsClass.php";
	
	//call classes
	$conn=new dbConnection();
	$req=new reqClass();
	
	//URL variables
	if(isset($_GET['state']))		$state=$_GET['state'];
	
	//variable initialization
	$s=array();
	$output=null;
	
	//get current active basket states
	$arr=$req->activeStates();
	//print_r($arr);
	//loop through all states and display the next one
	$key=array_search($state, $arr);
	//set the array to the correct position
	foreach($arr as $row){
		if($arr[$key]==$row)break;
		next($arr);	
	}

	$s[]=next($arr);
	//if the next state is REJECTED or STANDBY we must also display the acceptance button
	if($s[0]=="Rejected" or $s[0]=="Standby"){
		$s[]=next($arr);
		if($s[1]=="Standby")
			$s[]=next($arr);
	}
	//print_r($s);
	//print_r($arr);
	//check if this user has permission to modify the basket state
	foreach($s as $row){
		if(userStatePermission($user_id, $row)) $output.=$row.",";		
	}
	$output=substr($output,0,strlen($output)-1);
	echo $output;
}

function userStatePermission($user_id, $state){
	//call classes
	$conn=new dbConnection();
	$database=$conn->getDatabase();
	$sql=$conn->prepare("SELECT state_name FROM $database.state, $database.statepermission WHERE state_id=statepermission_state AND statepermission_user=$user_id AND state_name='$state' LIMIT 1");
	$sql->execute();
	//echo $sql->queryString;
	if($sql->rowCount()==1) return true;
	else return false;
	
}

function changeState($user_id){
	//call classes
	$conn=new dbConnection();
	$database=$conn->getDatabase();
	
	//URL variables
	if(isset($_GET['newstate']))	$newstate=$_GET['newstate'];
	if(isset($_GET['basket']))		$basket_id=$_GET['basket'];
	if(isset($_GET['req']))			$reqNumber=$_GET['req'];
	
	$sql=$conn->prepare("UPDATE basket SET basket_state=(SELECT state_id FROM $database.state WHERE state_name='$newstate') WHERE basket_id=$basket_id");
	try{
		$sql->execute();
		//update basket action dates
		switch($newstate){
			case "Received": //update delivery_date
				$attr="basket_delivery_date";
				break;
			case "Approved":	//update order_date
				//$attr="basket_order_date";
				updateStock($basket_id);
				break;
			case "Ordered": 	//update order_date
				$sql = $conn->prepare("UPDATE basket SET basket_sap=$reqNumber WHERE basket_id=$basket_id");
				$sql->execute();
				$attr="basket_order_date";
				//calculate budget and update it
				accountPrepare($basket_id);
				break;
		}
		if($newstate!="Rejected" and $newstate!="Approved"){ //no date attributed to rejection
			$sql=$conn->prepare("UPDATE basket SET $attr=NOW() WHERE basket_id=$basket_id");
			$sql->execute();
		}
	} catch (Exception $e){
		echo $e->getMessage();
		echo "Could not execute this operation";
	}


}

function userLevel($user_id){
	require_once "../resClass.php";
	//call class
	$admin=new restrictClass();
	$admin->userInfo($user_id);
	echo $admin->getUserLevel();
}

function accountPrepare($basket_id){
	//call classes
	$conn=new dbConnection();
	$database=$conn->getDatabase();
	
	//get total basket value
	$budget=null; //set initial budget to 0. Variable initialization
	//without VAT
	$sql=$conn->prepare("SELECT request_origin, request_number, sum(request_price*request_quantity*(1-request_discount/100)) as total FROM request WHERE request_basket=$basket_id GROUP BY request_origin");
	$sql->execute();
	for($i=0;$row=$sql->fetch();$i++){
		$sql=$conn->prepare("SELECT vat_value FROM $database.$row[0], $database.vat WHERE $row[0]_vat=vat_id AND $row[0]_id=$row[1]");
		$sql->execute();
		$res = $sql->fetch();
		//VAT included
		$budget+=$row[2]*(1+($res[0]/100));
	}
	
	//update account budget
	updateAccount($budget,$basket_id);
	//update account budget
	//$sql=$conn->prepare("UPDATE account SET account_budget=account_budget-$budget WHERE account_id IN (SELECT basket_account FROM $database.basket WHERE basket_id=$basket_id");
}

function updateAccount($budget,$basket_id){
	//call classes
	$conn=new dbConnection();
	$database=$conn->getDatabase(); //write current database to a local variable
	//update account budget
	$sql=$conn->prepare("UPDATE account SET account_budget=account_budget-$budget WHERE account_id IN (SELECT basket_account FROM $database.basket WHERE basket_id=$basket_id)");
	$sql->execute();
}

function checkBudget($account, $total){
	//call classes
	$conn=new dbConnection();
	$database=$conn->getDatabase(); //write current database to a local variable
	//update account budget
	$sql=$conn->prepare("SELECT account_budget FROM account WHERE account_id=$account");
	$sql->execute();
	$row=$sql->fetch();
	if($row[0]<$total){
		return true;
	} else {
		return false;
	}
}

/**
 * Specific method to update product stock from internal products 
 * target table must have target_stock attribute
 * 
 */

function updateStock($basket){
	//call database class
	$conn=new dbConnection();
	//query for basket type
	$sql=$conn->prepare("SELECT basket_type FROM basket WHERE basket_id=$basket");
	$sql->execute();
	$res=$sql->fetch();
	//Which basket type is this?
	//add something if stock goes below 0
	if($res[0]==2) { //Internal basket
		//query for all requests from this basket
		$sql = $conn->prepare("SELECT request_id, request_number, request_origin, request_quantity FROM request WHERE request_basket=$basket");
		$sql->execute();
		//loop through all results
		for($i=0;$row=$sql->fetch();$i++){
			//update stock from origin table
			$sql_upd=$conn->prepare("UPDATE $row[2] SET ".$row[2]."_stock=".$row[2]."_stock-$row[3] WHERE ".$row[2]."_id=$row[1]");
			//echo $sql->queryString;
			$sql_upd->execute();
		}
	} else {
		return;
	}
	
	
}

function newVendor($user_id){
	//list of includes
	require_once "../mailClass.php";
	require_once "../resClass.php";
	//call classes
	$mail=new mailClass();
	$conn=new dbConnection();
	$res=new restrictClass();
	
	//write to database
	$query="INSERT INTO newvendor VALUES ('',";
	foreach($_POST as $row){
		$query.=" '$row',";
	}
	$query.="$user_id, NOW())";
	$conn->beginTransaction();
	$sql=$conn->query($query);
	$conn->commit();
	//get last inserted id		
	$id=$conn->lastInsertId();
	
	//get user information
	$res->userInfo($user_id);
	$from=$res->getUserEmail();
	$subject="Requisition system: New vendor request";
	$to=array(
		"vsantos@igc.gulbenkian.pt",
		"jgusmao@igc.gulbenkian.pt");
	$msg="New vendor request #$id from".$res->getUserLogin()."\n\n";
	//Go through all variables one by one
	$msg.="\nSUPPLIER DATA\n";
	$msg.="Vendor Name: ".$_POST['vendor_name']."\n";
	$msg.="Address: ".$_POST['address']."\n";
	$msg.="Street: ".$_POST['street']."\n";
	$msg.="Postal Code: ".$_POST['postal_code']."\n";
	$msg.="Vat reg. No.: ".$_POST['vat']."\n";
	$msg.="Country: ".$_POST['country']."\n";
	$msg.="\nCOMMUNICATION\n";
	$msg.="Name: ".$_POST['com_name']."\n";
	$msg.="Phone: ".$_POST['phone']."\n";
	$msg.="Fax: ".$_POST['fax']."\n";
	$msg.="Email: ".$_POST['email']."\n";
	$msg.="\nPAYMENT DATA\n";
	$msg.="NIB (PT suppliers): ".$_POST['nib']."\n";
	$msg.="IBAN (International suppliers): ".$_POST['iban']."\n";
	$msg.="SWIFT Code (International suppliers): ".$_POST['swift']."\n";
	$msg.="ABA/Routing (International suppliers): ".$_POST['aba']."\n";
	$msg.="Bank: ".$_POST['bank']."\n";
	$msg.="Bank street: ".$_POST['bank_street']."\n";
	$msg.="\n\nThis is an automatic email. DO NOT REPLY";
	
	try{
		$mail->sendMail($subject, $to, $from, $msg);
	} catch(Exception $e){
		echo $e->getMessage();
	}
	echo "<meta HTTP-EQUIV='REFRESH' content='0; url=../admin.php'>";
}


function displayMenu($user_id){
	require_once ("../dispClass.php");
	require_once ("../queryClass.php");
	require_once ("../genObjClass.php");
	require_once ("../resClass.php");
	require_once ("../reportClass.php");
	require_once ("../mailClass.php");
	require_once ("../treeClass.php");
	require_once ("../configClass.php");
	
	//call database class (handle connections)
	$db = new dbConnection();
	$engine = $db->getEngine();
	//call other classes
	$display = new dispClass();
	$genObj = new genObjClass();
	$perm = new restrictClass();
	$search = new searchClass();
	$treeview = new treeClass();
	$mail = new mailClass();
	$config = new configClass();

	echo "<table border=0 align=left width=200px>";
	echo "<tr><td><a href=".$db->getFolder()."/admin.php title='Return to the administration area'>Return to main menu</a></td></tr>";
	$display->userOptions(true,$user_id);
	echo "<tr><td><a href=javascript:void(0) class=contact>Helpdesk</a>";
	$display->contactForm();
	echo "</td></tr>";
	echo "<tr><td><hr></td></tr>";
	$display->reportOptions(true,$user_id);
	//display treeview
	echo "<tr><td><a href=javascript:void(0) title='Tree reports'>Treeview reports</a>";
	echo "<div id='treeList' class=sidebar>";
	$treeview->treeview_access($user_id);
	echo "</div>";
	echo "</td></tr>";
	$config->checkPlugins();
	echo "</table>";
}


?>