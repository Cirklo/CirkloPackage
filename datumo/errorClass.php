<?php

require_once ("mailClass.php");

class errorClass{
	private $mail;
	private $error_report_email;
	
	public function __construct(){
		$this->mail = new mailClass();	
		//guy who will receive the error report
		$this->error_report_email="jlagarto@igc.gulbenkian.pt";	
	}

/**
 * method to display the error in a div
 * @param string $sql
 * @param string $objName
 * @param string $error PDO exception
 * @param string $msg
 */

	public function errorDisplay($sql,$objName,$error,$msg="Could not execute query. <b>If the problem persists please contact the administrator! <a href=admin.php>Return to main menu</a></b>"){
		echo "<div id=error class=error>$msg</div>";
		$this->sendReport($sql, $error, $objName);
		exit();
	}
	
/**
 * method to report the error through email
 * @param string $sql
 * @param string $objName 
 * @param string $error PDO exception
*/
	
	public function sendReport($sql,$error,$objName){
		//guy who will receive the error report
		$to = $this->error_report_email;
		//report subject
		$subject = "Datumo 2.0 Error Report";
		$msg="Error Report\n\n";
		$msg.="Error occurred at ".date("D, d M Y H:i:s")."\n";
		$msg.="Query: $sql\n";
		$msg.="PDO: $error";
		//generate message to report the error
		$this->mail->sendMail($subject, $to, $from, $msg);	
		
	}
	
	
	
}

?>