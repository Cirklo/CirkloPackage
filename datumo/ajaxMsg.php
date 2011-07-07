<?php

/**
 * @author João Lagarto	/ Nuno Moreno
 * @abstract Ajax handler request -> get second attribute from referenced table
 */

require_once("__dbConnect.php");

//variables
$header = array();

//call classes to handle database connection and to build queries for different engines
$conn = new dbConnection();
$engine = $conn->getEngine();
$database = $conn->getDatabase();

if(isset($_GET['user'])){ 		$user_id = $_GET['user'];}
if(isset($_GET['to'])){ 		$userto = $_GET['to'];}
if(isset($_GET['subject'])){ 	$subject = $_GET['subject'];}
if(isset($_GET['msg'])){ 		$msg = $_GET['msg'];}

if($user_id!=$userto){
	//figure out what is the destination user_id
	$sql = $conn->prepare("SELECT user_id FROM $database.user WHERE user_login='$userto'");
	$sql->execute();
	$row = $sql->fetch();
	$userto = $row[0];
}

//query to insert the message in the database
$sql = $conn->prepare("INSERT INTO message (message_title, message_text, message_date, message_from, message_to) VALUES ('$subject', '$msg', NOW(), $user_id, $userto)");
$sql->execute();
/*try{
	
	echo "Message successfully sent!";
} catch (Exception $e) {
	echo "Could not send message! Please contact the administrator for details!";
}*/

?>