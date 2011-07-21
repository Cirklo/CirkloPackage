<?php

require_once "mail/class.phpmailer.php";
require_once "__dbConnect.php";

class mailClass extends PHPMailer{
	private $pdo;
	
	public function __construct(){
		$this->pdo = new dbConnection();
		//set search path to main database
		$this->pdo->dbConn();
		
		$sql = "SELECT configParams_name, configParams_value from configParams where configParams_name='host' or configParams_name='port' or configParams_name='password' or configParams_name='email' or configParams_name='smtpsecure' or configParams_name='smtpauth'";
		$sql = $this->pdo->query($sql);
		for($i=0;$arr=$sql->fetch();$i++){
			$row[$i]=$arr[1];
		}
		$this->IsSMTP(); // telling the class to use SMTP
        $this->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
        $this->SMTPAuth   = $row[5];                  // enable SMTP authentication
        $this->SMTPSecure = $row[4];                 // sets the prefix to the servier
        $this->Port       = $row[1];                   // set the SMTP port for the GMAIL server   
        $this->Host       = $row[0];      		// sets GMAIL as the SMTP server
        $this->Username   = $row[3];  			// GMAIL username
        $this->Password   = $row[2];            // GMAIL password
	}
	
	/**
 * Method to send emails
 * @param unknown_type $contact
 */
	
	public function sendMail($subject, $to, $from, $msg){
		$this->CharSet="UTF-8";
		$this->SetFrom($from, $from);
        $this->AddReplyTo($from,$from);
		$this->Subject = $subject;
        $this->Body = $msg;
        if(sizeof($to)==1){
        	$this->AddAddress($to);
        } else {
	        foreach($to as $target){
	        	$this->AddAddress($target, "");
	        }
        } 
		if(!$this->Send()) {
            //mail error
            return "Could not send mail!";
        } else {
            //mail OK
        	return "Mail successfully sent!";   
        }
	}
	
	public function mailingList($subject, $to, $from, $msg){
		$delay=1; 				//delay between emails, in seconds
		$noAddressesPerTurn=15;	//number of addresses per email
		$j=0;					//control counter
		$noAddresses=sizeof($to);
		echo $noAddresses."<br>";
		$noEmails=ceil($noAddresses/$noAddressesPerTurn);
		echo $noEmails."<br>";
		for($i=1;$i<=$noEmails;$i++){
			$this->CharSet="UTF-8";
			$this->SetFrom($from, $from);
	        $this->AddReplyTo($from,$from);
			$this->Subject=$subject;
	        $this->Body=$msg;
	        //loop through email addresses
	       	while($j!=($noAddressesPerTurn*$i)){
	       		echo $to[$j]."<br>";
	       		$this->AddBCC($to[$j]);
	       		$j++;	//increment counter
	       	}
//			if(!$this->Send()) {
//	            //mail error
//	            $bool=false;
//	        } else {
//	            //mail OK
//	        	$bool=true;
//	        }
			$this->ClearAddresses();	//clear addresses for the next loop
			$this->ClearBCCs();
			$this->ClearReplyTos();
			sleep($delay);				//sleep after sending emails
		}
	}
}


?>