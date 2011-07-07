<?php

require_once("../Datumo2.0/.htconnect.php");
require_once("../alert/class.phpmailer.php");

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

$sql = $conn->query("SELECT mainconfig_host, mainconfig_port, mainconfig_password, mainconfig_email, mainconfig_SMTPSecure, mainconfig_SMTPAuth FROM mainconfig WHERE mainconfig_id = 1");
$row = $sql->fetch();
$mail->IsSMTP(); // telling the class to use SMTP
$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
$mail->SMTPAuth   = $row[5];                  // enable SMTP authentication
$mail->SMTPSecure = $row[4];                 // sets the prefix to the servier
$mail->Port       = $row[1];                   // set the SMTP port for the GMAIL server   
$mail->Host       = $row[0];      // sets GMAIL as the SMTP server
$mail->Username   = $row[3];  // GMAIL username
$mail->Password   = $row[2];            // GMAIL password
$mail->SetFrom($row[3], 'Calendar Admin');
$mail->AddReplyTo($row[3],"Calendar Admin");

$body = "Alarm\n\n";
$body.= date('Y-m-d H:i:s',time())." ".$msg;
$body.= "\n\nPlease visit the monitoring page http://calendar.igc.gulbenkian.pt/ekrano for further details!";

$mail->Subject = "Calendar monitoring system";
$mail->Body=$body;

$mail->AddAddress($address, "");
$mail->AddAddress("jlagarto@igc.gulbenkian.pt","");
if(!$mail->Send()) {
	echo "Mailer Error: " . $mail->ErrorInfo;
} else {
	//echo "Message sent!";
}

//fopen("http://192.168.52.35:8888/send?phone=". $phone . "&msg=" . $msg,'r');

?>
