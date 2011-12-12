<?php
require_once("commonCode.php");
?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/admin.css" rel="stylesheet" type="text/css">

<?php

// require_once(".htconnect.php");
// require_once("__dbHelp.php");
require_once("errorHandler.php");
require_once("alert/class.phpmailer.php");

$error = new errorHandler;
$mail = new PHPMailer;
$address = array();
$req = array();

$msg = "Reservation system new user request:\n\n";

//PERSONAL INFORMATION LOOP
foreach($_POST as $key=>$value){
    $key = str_replace('_',' ',$key);
	$key = cleanValue($key);
	$value = cleanValue($value);
    $msg .= $key.": ".$value."\n";
    if($key == 'Resource') $resource = $value;
}

// $sql = "SELECT mainconfig_host, mainconfig_port, mainconfig_password, mainconfig_email, mainconfig_smtpsecure, mainconfig_smtpauth FROM mainconfig WHERE mainconfig_id = 1";
// $res = dbHelp::query($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
// $row = dbHelp::fetchRowByIndex($res);
$sql = "SELECT configParams_name, configParams_value from configParams where configParams_name='host' or configParams_name='port' or configParams_name='password' or configParams_name='email' or configParams_name='smtpsecure' or configParams_name='smtpauth'";
$res = dbHelp::query($sql);
$configArray = array();
for($i=0;$arr=dbHelp::fetchRowByIndex($res);$i++){
	$configArray[$arr[0]] = $arr[1];
}
$mail->SMTPAuth   = $configArray['smtpauth'];
$mail->SMTPSecure = $configArray['smtpsecure'];
$mail->Port       = $configArray['port'];
$mail->Host       = $configArray['host'];
$mail->Username   = $configArray['email'];
$mail->Password   = $configArray['password'];
$mail->IsSMTP(); // telling the class to use SMTP
$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
$mail->SetFrom($configArray['email'], "Calendar administration");
$mail->AddReplyTo($configArray['email'],"Calendar administration");   
// for($i=0; $arr = dbHelp::fetchRowByIndex($res); $i++){
	// $row[$i] = $arr[1];
// }

// $mail->IsSMTP(); // telling the class to use SMTP
// $mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
// $mail->SMTPAuth   = $row[5];                  // enable SMTP authentication
// $mail->SMTPSecure = $row[4];                 // sets the prefix to the servier
// $mail->Port       = $row[1];                   // set the SMTP port for the GMAIL server   
// $mail->Host       = $row[0];      // sets GMAIL as the SMTP server
// $mail->Username   = $row[3];  // GMAIL username
// $mail->Password   = $row[2];            // GMAIL password
// $mail->SetFrom($row[3], "Calendar Admin");
// $mail->AddReplyTo($row[3],"Calendar Admin");

// Would only send an email to the person responsible for the equipment
$sql = "SELECT user_email from ".dbHelp::getSchemaName().".user, resource WHERE user_id = resource_resp AND resource_name LIKE '$resource'";

// Sends to all users with admin level (Bugworkers)
// $sql = "SELECT user_email from ".dbHelp::getSchemaName().".user WHERE user_level = 0";
$res = dbHelp::query($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
// Used when there was just one responsible
// $row = dbHelp::fetchRowByIndex($res);
	$mail->Subject = "Calendar administration: new user";
	$mail->Body = $msg;
	while ($row = dbHelp::fetchRowByIndex($res)){
		$mail->AddAddress($row[0], "");
	}

	echo "<table border=0>";
	if(!$mail->Send()) {//mail error
		echo "<tr><td>Could not send email. Please check your network connection and try again!<br><br></td></tr>";
		echo "<tr><td><a href='application.php'>Return</a></td></tr>";
	} else { //mail OK
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