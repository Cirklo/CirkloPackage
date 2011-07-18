<?php

/**
 * @author Joao Lagarto
 * @abstract Script to handle mail ajax requests
 */

require_once "session.php";
require_once "__dbConnect.php";
require_once "mailClass.php";

if(isset($_GET['type'])) $type = $_GET['type'];

switch ($type){
	case 1:
		$mail = new mailClass();
		//$refMail = "bugs@cirklo.org"; //where the mails go to (only bug reports)
		$name = $_POST['name'];
		$email = $_POST['email'];
		$target=$_POST['target'];
		$message = nl2br($_POST['message']);
		//get todays date
		$todayis = date("l, F j, Y, g:i a") ;
		//set a title for the message
		$subject = "datumo2.0 message";
		$body = "From $name, \n\n";
		$body.=strip_tags($message);
		//put your email address here
		$str=$mail->sendMail($subject, $target, $email, $body);
		echo $str;
		break;
}








?>
