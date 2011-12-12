<?php

require_once("../.htconnect.php");
require_once("../../agendo/alert/class.phpmailer.php");

//initiate classes
$mail = new PHPMailer;
$conn= new dbConnection();

//http variables
$phone = $_GET['phone'];
$msg = $_GET['msg'];

//query to send mail
$query = "SELECT DISTINCT user_email FROM user WHERE user_mobile='$phone'";
$sql = $conn->query($query);
$row = $sql->fetch();
$address = $row[0];

$sql = $conn->query("SELECT configParams_name, configParams_value from configParams where configParams_name='host' or configParams_name='port' or configParams_name='password' or configParams_name='email' or configParams_name='smtpsecure' or configParams_name='smtpauth'");
$configArray = array();
for($i=0;$arr=$sql->fetch();$i++){
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
$mail->AddReplyTo($configArray['email'], "Calendar administration");   
// for($i=0;$arr=$sql->fetch();$i++){
	// $row[$i]=$arr[1];
// }

// $mail->IsSMTP(); // telling the class to use SMTP
// $mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
// $mail->SMTPAuth   = $row[5];
// $mail->SMTPSecure = $row[4];
// $mail->Port       = $row[1];
// $mail->Host       = $row[0];
// $mail->Username   = $row[3];
// $mail->Password   = $row[2];
// $mail->SetFrom($row[3], 'Calendar Admin');
// $mail->AddReplyTo($row[3],"Calendar Admin");

$pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
$body = "Alarm\n\n";
$body.= date('Y-m-d H:i:s',time())." ".$msg;
$body.= "\n\nPlease visit the monitoring page https://agendo.cirklo.org/".$pathOfIndex[sizeof($pathOfIndex)-2]."/ekrano/ for further details!";

$mail->Subject = "Calendar monitoring system";
$mail->Body=$body;

$mail->AddAddress($address, "");

if(!$mail->Send()) {
	echo "Mailer Error: " . $mail->ErrorInfo;
} else {
	//echo "Message sent!";
}

//fopen("http://192.168.52.35:8888/send?phone=". $phone . "&msg=" . $msg,'r');

?>
