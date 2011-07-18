<html>
<head>
<title>PHPMailer - SMTP (Gmail) basic test</title>
</head>
<body>

<?php


error_reporting(E_STRICT);

require_once('class.phpmailer.php');


$mail             = new PHPMailer();


$mail->IsSMTP(); // telling the class to use SMTP
$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
$mail->Username   = "nunomoreno@gmail.com";  // GMAIL username
$mail->Password   = "PaiNatal";            // GMAIL password

$mail->SetFrom('moreno@igc.gulbenkian.pt', 'Aqui é que');

$mail->AddReplyTo("moreno@igc.gulbenkian.pt","Nuno Morneo");

$mail->Subject    = "New reservation for confirmation"; 

$mail->Body="Olá. Consigo mandar mensagens pela google sem passar pelos servidores do IGC! Assim está bem!";

$address = "moreno@igc.gulbenkian.pt";
$mail->AddAddress($address, "Nuno Moreno");


if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  //echo "Message sent!";
}

?>

</body>
</html>
