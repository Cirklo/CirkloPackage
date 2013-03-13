<?php
require_once("commonCode.php");

echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
echo "<link href='css/admin.css' rel='stylesheet' type='text/css'>";

importJs();
$address = array();
$req = array();

$msg = "Reservation system new user request:\n\n";

$resource = $_GET['resource'];
$userMail = $_POST['Email'];
$firstName = $_POST['First_name'];
$lastName = $_POST['Last_name'];
$replyToPerson = $firstName." ".$lastName;

// Would only send an email to the person responsible for the equipment
$sql = "SELECT user_email from ".dbHelp::getSchemaName().".user, resource WHERE user_id = resource_resp AND resource_id = :0";
// Sends to all users with admin level (Bugworkers)
// $sql = "SELECT user_email from ".dbHelp::getSchemaName().".user WHERE user_level = 0";
$res = dbHelp::query($sql, array($resource));
$row = dbHelp::fetchRowByIndex($res);
$address = $row[0];
// while ($row = dbHelp::fetchRowByIndex($res)){
	// $mail->AddAddress($row[0], "");
// }
$department = null;
$isHtml = false;
$msg = "";
if(isset($_GET['department']) && $_GET['department'] != ''){
	$department = $_GET['department'];
	$mobile = $_POST['Mobile'];
	$phone = $_POST['Phone'];
	$extension = $_POST['Phone_extension'];
	$isHtml = true;
	
	$msg = "The user with the following data has requested to be added as well as getting permission to use the resource '".$_POST['Resource']."':";
	$msg .= "<br>";
	$msg .= "First name: ".$firstName;
	$msg .= "<br>";
	$msg .= "Last name: ".$lastName;
	$msg .= "<br>";
	$msg .= "Mail: ".$userMail;
	$msg .= "<br>";
	$msg .= "Phone number: ".$phone;
	$msg .= "<br>";
	$msg .= "Extension: ".$extension;
	$msg .= "<br>";
	$msg .= "Institute: ".$_POST['Institute'];
	$msg .= "<br>";
	$msg .= "Department: ".$_POST['Department'];
	$msg .= "<br>";
	$msg .= "Mobile: ".$mobile;
	$msg .= "<br>";
	$msg .= "Resource: ".$_POST['Resource'];
	$msg .= "<br>";
	$msg .= "<br>";
	$getLink = getProtocol()."://".$_SERVER['SERVER_NAME']."/agendo/application.php";
	$getLink .= "?path=".$_SESSION['path'];
	// $getLink .= "&db=".dbHelp::getSchemaName();
	$getLink .= "&first=".$firstName;
	$getLink .= "&last=".$lastName;
	$getLink .= "&mail=".$userMail;
	$getLink .= "&phone=".$phone;
	$getLink .= "&ext=".$extension;
	$getLink .= "&dep=".$department;
	$getLink .= "&mobile=".$mobile;
	$getLink .= "&res=".$resource;
	$getLink .= "&code=".generate_random_code();
	$msg .= "If everything is correct you may add the user by clicking this <a href='".htmlentities($getLink)."'>link</a>";
	$msg .= "<br>";
	$msg .= "You may mail the user by clicking this <a href='mailto:".$userMail."?Subject=New%20user'>link</a>";
}
else{
	//PERSONAL INFORMATION LOOP
	foreach($_POST as $key=>$value){
		$key = str_replace('_',' ',$key);
		$key = cleanValue($key);
		$value = cleanValue($value);
		$msg .= $key.": ".$value."\n";
	}
}

$mail = getMailObject("Calendar administration: new user", $address, $msg, $replyToPerson, $userMail);
$mail->isHtml($isHtml);

echo "<table border=0>";
// if(!$mail->Send()) {//mail error
if(sendMailObject($mail) !== true){ //mail error
	echo "<tr><td>Could not send email. Please check your network connection and try again!<br><br></td></tr>";
	echo "<tr><td><a href='application.php'>Return</a></td></tr>";
}
else{ //mail OK
	echo "<tr><td>Your request has been successfully sent! Please wait for admin confirmation.<br><br></td></tr>";
	echo "<tr><td>Thank you for register in agendo&reg; reservation system. As soon as your registration is complete you will receive a password through email or SMS.<br><br></td></tr>";
	echo "<tr><td>You can login at <a href='https://agendo.cirklo.org'>https://agendo.cirklo.org</a> to change your password and check your permissions.<br><br></td></tr>";
	echo "<tr><td>If you wish to use other equipment please go to the reservation system <a href='http://calendar.igc.gulbenkian.pt'>homepage</a> and submit the 'New permission' form.</td></tr>";
	echo "<tr><td>You can also check our feature videos to learn how the reservation system works and how to proceed in any situation.<br><br></td></tr>";
	echo "<tr><td>You can find further information on our website at <a href='www.cirklo.org'>www.cirklo.org</a>.</a></td></tr>";
	echo "<tr>";
	echo "<td align=left valign=top><a href='http://www.facebook.com/pages/Cirklo/152674671417637?ref=ts'><img src=".$_SESSION['path']."/pics/fb.png border=0></a></td>";
	echo "</tr>";
	echo "<tr><td><a href=# onclick='window.close()'>Exit</a></td></tr>";
}

echo "</table>";
?>