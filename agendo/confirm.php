<?php
require_once("commonCode.php");

echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
echo "<link href='css/admin.css' rel='stylesheet' type='text/css'>";

importJs();
// require_once(".htconnect.php");
// require_once("__dbHelp.php");
// require_once("errorHandler.php");
// require_once("alert/class.phpmailer.php");

// $error = new errorHandler;
// $mail = new PHPMailer;
$address = array();
$req = array();

$msg = "Reservation system new user request:\n\n";

$resource = $_GET['resource'];
$userMail = $_POST['Email'];
$firstName = $_POST['First name'];
$lastName = $_POST['Last name'];
$replyToPerson = $firstName." ".$lastName;
// $sql = "SELECT configParams_name, configParams_value from configParams where configParams_name='host' or configParams_name='port' or configParams_name='password' or configParams_name='email' or configParams_name='smtpsecure' or configParams_name='smtpauth'";
// $res = dbHelp::query($sql);
// $configArray = array();
// for($i=0;$arr=dbHelp::fetchRowByIndex($res);$i++){
	// $configArray[$arr[0]] = $arr[1];
// }

// $mail->SMTPAuth   = $configArray['smtpauth'];
// $mail->SMTPSecure = $configArray['smtpsecure'];
// $mail->Port       = $configArray['port'];
// $mail->Host       = $configArray['host'];
// $mail->Username   = $configArray['email'];
// $mail->Password   = $configArray['password'];
// $mail->IsSMTP(); // telling the class to use SMTP
// $mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
// $mail->SetFrom($configArray['email'], "Calendar administration");

// Would only send an email to the person responsible for the equipment
// $sql = "SELECT user_email from ".dbHelp::getSchemaName().".user, resource WHERE user_id = resource_resp AND resource_name LIKE :0";
$sql = "SELECT user_email from ".dbHelp::getSchemaName().".user, resource WHERE user_id = resource_resp AND resource_id = :0";
// Sends to all users with admin level (Bugworkers)
// $sql = "SELECT user_email from ".dbHelp::getSchemaName().".user WHERE user_level = 0";
// $mail->ClearReplyTos();	//clear replys before receiving any email
$res = dbHelp::query($sql, array($resource));
$row = dbHelp::fetchRowByIndex($res);
$address = $row[0];
// while ($row = dbHelp::fetchRowByIndex($res)){
	// $mail->AddAddress($row[0], "");
// }
// $mail->Subject = "Calendar administration: new user";
// $mail->Body = $msg;
// $mail->AddReplyTo($userMail, "User");
$department = null;
$isHtml = false;
$msg = "";
if(isset($_GET['department']) && $_GET['department'] != ''){
	$department = $_GET['department'];
	$mobile = $_POST['Mobile'];
	$phone = $_POST['Phone'];
	$extension = $_POST['Phone extension'];
	$isHtml = true;
	
	$msg = "Create user by clicking the link:";
	$msg .= "<br>";
	$getLink = getProtocol()."://".$_SERVER['SERVER_NAME']."/agendo/application.php?path=".$_SESSION['path']."&db=".dbHelp::getSchemaName()."&first=".$firstName."&last=".$lastName."&mail=".$userMail."&phone=".$phone."&ext=".$extension."&department=".$department."&mobile=".$mobile."";
	
	$msg .= "<a href='".htmlentities($getLink)."'>Add user</a>";
	$msg .= "<br>";
	$msg .= "You may mail the user by clicking below";
	$msg .= "<br>";
	$msg .= "<a href='mailto:".$userMail."?Subject=New%20user'>Mail user</a>";
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

// wtf_array(array("Calendar administration: new user", $address, $replyToPerson, $userMail));
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