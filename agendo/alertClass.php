<?php

require_once("alert/class.phpmailer.php");
// require_once("commonCode.php");

/**
  * @author Nuno Moreno
  * @copyright 2009-2010 Nuno Moreno
  * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
  * @version 1.0
  * @abstract Uses the phpmailer package for sending email warnings depending on several situations. Depending on the user configuration it cal also
  * generate a remote open. This remote computer is/should be running the python script alert/webserver.py which recieves the request and process
  * the info to gnokii for connecting to a GSM device and send an SMS
*/
class alert extends PHPMailer{
private $UserEmail;
private $UserFullName;
private $UserMobile;
private $Slots;
private $LastEntry;

private $User;
private $AlertType;

private $Resource;
private $ResourceResolution;
private $ResourceName;

private $RespName;
private $RespEmail;
private $RespMobile;
private $ResourceResp;
private $RespAlert;

/**
  * @method noreturn sets the sender email configuration
  * @
*/
function __construct($resource = false) {
	$sql = "SELECT configParams_name, configParams_value from configParams where configParams_name='host' or configParams_name='port' or configParams_name='password' or configParams_name='email' or configParams_name='smtpsecure' or configParams_name='smtpauth'";
	$res = dbHelp::query($sql);
	$configArray = array();
	for($i=0;$arr=dbHelp::fetchRowByIndex($res);$i++){
		$configArray[$arr[0]] = $arr[1];
	}
	
	$this->SMTPAuth   = $configArray['smtpauth'];
	$this->SMTPSecure = $configArray['smtpsecure'];
	$this->Port       = $configArray['port'];
	$this->Host       = $configArray['host'];
	$this->Username   = $configArray['email'];
	$this->Password   = $configArray['password'];
    $this->IsSMTP(); // telling the class to use SMTP
    $this->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
    $this->SetFrom($configArray['email'], "Calendar administration");
    $this->AddReplyTo($configArray['email'],"Calendar administration");   
  
    $this->Resource = $resource;
	if($resource !== false){
		$sql="select user_id,user_email,user_mobile, user_firstname,user_lastname,user_alert,resource_name,resource_resolution from ".dbHelp::getSchemaName().".user,resource where resource_resp=user_id and resource_id = :0";
		$res=dbHelp::query($sql, array($this->Resource));
		$arr=dbHelp::fetchRowByIndex($res);
		
		$this->ResourceResp=$arr[0];
		$this->RespEmail=$arr[1];
		$this->RespMobile=$arr[2];
		$this->RespName=$arr[3]." ".$arr[4];
		$this->RespAlert=$arr[5];
		$this->ResourceName=$arr[6];
		$this->ResourceResolution=$arr[7];
	}
}
/**
   * Sets user info: email and full name
   * @param integer $user_id

*/

function setUser($user_id){
    $sql="select user_firstname,user_lastname,user_email,user_mobile,user_alert from ".dbHelp::getSchemaName().".user where user_id = :0";
    $res=dbHelp::query($sql, array($user_id));
    $arruser=dbHelp::fetchRowByIndex($res);
    $this->UserFullName=$arruser[0]." ".$arruser[1];
    $this->UserEmail=$arruser[2];
    $this->UserMobile=$arruser[3];
    $this->AlertType=$arruser[4];
    $this->User=$user_id;
}

function setSlots($Slots){
    $this->Slots=$Slots;
}

function setEntry($entry){
    $this->LastEntry=$entry;
}

function getResourceResp(){
    return $this->ResourceResp;
}

/**
   * In the case of an entry it triggers one warning event.
   * @param integer entry
*/
function toWaitList($type){
	// only uses the first result out of a query that searches what are quite probably millions of lines, not particularly usefull
    $sql="select user_mobile,user_email,".dbHelp::getFromDate('entry_datetime','%d, %M %Y')." as d, ".dbHelp::getFromDate('entry_datetime','%H:%i')." as t,resource_name, user_alert from entry,".dbHelp::getSchemaName().".user,resource where entry_resource=resource_id and entry_user=user_id and entry_status=4 and (entry_datetime, entry_resource) in (select entry_datetime,entry_resource from entry where entry_id=". $this->LastEntry.") order by entry_id";
    $res=dbHelp::query($sql);
    $arrStatus=dbHelp::fetchRowByName($res);
        
    if(dbHelp::numberOfRows($res)>0){
        switch ($type) {
            case 'delete':
                $msg="You are booked for ".$this->ResourceName."  at ".$arrStatus['t']." on the ".$arrStatus['d'].". Please confirm on the website. ";
            break;
			
            case 'update':
                  $msg="Due to an entry update, your monitored entry was removed from ". $this->ResourceName . " at " . $arrStatus['t']  . " on the " .  $arrStatus['d'] . ". Check the calendar, there might be available spots.";
            break;
        }
        switch($arrStatus['user_alert']){
			case 2:
				try {
					$url="http://192.168.52.35:8888/send?phone=". $arrStatus['user_mobile'] . "&msg=" . str_replace(' ','%20',$msg);
					$handle = fopen($url, "r");
				} catch (HttpException $ex) {
					echo $ex;
				}
			break;
			
			case 1:
				$this->Subject="Calendar waiting list";
				$this->Body=$msg;
				$this->ClearReplyTos();	//clear replys before receiving any email
				$this->AddReplyTo($this->RespName, $this->RespEmail);
				$this->ClearAddresses();
				$this->AddAddress($arrStatus['user_email'], "");
				if(!$this->Send()) {
					echo "Mailer Error: " . $this->ErrorInfo;
				} 
				// else {
					//echo "Message sent!";
				// }
            break;
			
            case 0:
            break;
        }
    }
}
/**
   * Depending on the configuration resource managers can have a msg evertime there is an entry
   * @param string datetime with the format yyyymmddhhii
   * @param integer resource (resource id)
*/

function toAdmin($datetime,$extra,$type,$comment=''){
    if ($this->ResourceResp==$this->User) return;
    $extrainfo='';
    
    $year=substr($datetime,0,4);
    $month=substr($datetime,4,2);
    $day=substr($datetime,6,2);
    $hour=substr($datetime,8,2);
    $min=substr($datetime,10,2);
	
    if ($extra!=''){ // fields for new or update entry
        foreach ($extra as $key => $value) {
			$extrainfo.= $key. ":".$value ."\\n";
        }
        $extrainfo=substr($extrainfo,0,strlen($extrainfo)-2);
	}
	
$att = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Cirklo Agendo
BEGIN:VEVENT
UID:". $this->LastEntry . "@agendo
DTSTAMP:" . $year.$month.$day."T".$hour.$min."00"."
DTSTART:" . $year.$month.$day."T".$hour.$min."00"."
DTEND:" . $year.$month.$day."T". date("Hi",mktime($hour,$this->Slots*$this->ResourceResolution)) ."00
SUMMARY: ". $this->UserFullName . " " . $this->ResourceName . "
DESCRIPTION:" . $extrainfo . "
END:VEVENT
END:VCALENDAR";

    switch ($type) {
        case 'newentry':
			$this->AddStringAttachment($att,'agendo.ics');
            $msg="New entry on ". $this->ResourceName . " at ". $hour . ":". $min ." on the " .$year."-".$month."-".$day." from user ".$this->UserFullName.".";
            break;
			
        case 'comment':
            $msg="Comment added on ". $this->ResourceName . ": " . $comment;
            break;
			
        case 'assistance':
            $this->AddStringAttachment($att,'agendo.ics');
            $msg="Assistance requested for " . $this->ResourceName  . " at $hour:$min on the $year-$month-$day";
            break;
			
        case 'canceled assistance':
            $this->AddStringAttachment($att,'agendo.ics');
            $msg="Assistance canceled for " . $this->ResourceName  . " at $hour:$min on the $year-$month-$day";
            break;
			
        case 'update':
			// $sql="select xfields_label, xfieldsval_value from xfields, xfieldsval where xfieldsval_entry = ".$this->LastEntry." and xfieldsval_field = xfields_id";
			// $res=dbHelp::query($sql);
			// $fields = '';
			// while ($arr = dbHelp::fetchRowByIndex($res)){
				// $fields = " with field ".$arr[0]." = ".$arr[1].",";
			// }
			// if($fields != '')
				// $fields=substr($fields,0,strlen($fields)-1);

				$this->AddStringAttachment($att,'agendo.ics');
            // $msg="Update on ".$this->ResourceName." at ".$hour.":".$min." on "."$year-$month-$day from user ".$this->UserFullName.$fields.".";
            $msg="Update on ". $this->ResourceName . " at ". $hour . ":". $min ." on the " .$year."-".$month."-".$day." from user ".$this->UserFullName.".";
            break;
			
          case 'delete':
            $this->AddStringAttachment($att,'agendo.ics');
            $msg="Delete on ". $this->ResourceName  . " at ". $hour . ":". $min ." on " . "$year-$month-$day from user " . $this->UserFullName;
            break;
    }

    switch ($this->RespAlert) {
		case 2:
			try {
				$url="http://192.168.52.35:8888/send?phone=". $this->RespMobile . "&msg=" . str_replace(' ','%20',$msg);
					$handle = fopen($url, "r");
				} catch (HttpException $ex) {
					echo $ex;
				}
			break;
			
        case 1:
            $this->Subject=strtoupper($type)." on " . $this->ResourceName ;
			$this->ClearReplyTos();
            $this->AddReplyTo($this->UserEmail,$this->UserFullName);
			$this->ClearAddresses();
			$this->AddAddress($this->RespEmail, "");
			$mobileStr = str_replace("\\n", "\n", $extrainfo);
            $this->Body=$msg . "\nEmail: ". $this->UserEmail ."\nMobile: ".$this->UserMobile ."\n".$mobileStr;
            if(!$this->Send()){
                // echo "Mailer Error: " . $this->ErrorInfo;
               echo "Unable to send email: " . $this->ErrorInfo;
               // throw new Exception ("Unable to send email: " . $this->ErrorInfo);
            }
			// else {
                // echo "Message sent!";
            // }
            break;
			
		case 0:
            break;
        }
}

/**
   * Method for password recover. It generates a 8 character pwd alternating a consonant and a vowel and sends it for the user
   * @param integer user_id (user unique id)

*/
function recover($user_id, $passRenewalResp = null){
    // $sql="select user_email,user_mobile, concat(user_firstname,' ',user_lastname) name,user_alert from ".dbHelp::getSchemaName().".user where user_id=". $user_id;
    // $sql="select user_email,user_mobile,user_alert from ".dbHelp::getSchemaName().".user where user_login='". $user_id."'";
    $sql="select user_email,user_mobile,user_alert, user_login from ".dbHelp::getSchemaName().".user where user_login=:0";
    // $res=dbHelp::query($sql);
    $res=dbHelp::query($sql, array($user_id));
    $arr=dbHelp::fetchRowByName($res);
	$user_id = $arr['user_login'];
    $vowels="aeiyou";
    $consonants="bcdfghjklmnpqrstvwxz";
    $pwd='';
    for ($i = 0; $i < 8; $i++) {
        if ($i%2==0) {
            $pwd.=$consonants[rand(0,strlen($consonants)-1)];
        } else {
            $pwd.=$vowels[rand(0,strlen($vowels)-1)];
        }
    }
    // $sql="update user set user_passwd = password('$pwd') where user_id=". $user_id;
	$sql="update ".dbHelp::getSchemaName().".user set user_passwd = '".cryptPassword($pwd)."' where user_login='".$user_id."'";
	$res=dbHelp::query($sql) or die('Password not updated');
	if(dbHelp::numberOfRows($res) < 1){
		echo "User not found";
	}
	else{
		$msg = "The randomly generated password for ".$user_id." is now ".$pwd;
		if(isset($passRenewalResp)){
			$sql = "select user_firstname, user_lastname from ".dbHelp::getSchemaName().".user where user_id = :0";
			$prep = dbHelp::query($sql, array($passRenewalResp));
			$res = dbHelp::fetchRowByIndex($prep);
			$msg = "The random password ".$pwd." for ".$user_id." was generated by ".$res[0]." ".$res[1];
		}
		switch ($arr['user_alert']) {
			case 2:
				try {
					// $msg="The%20randomly%20generated%20password%20for%20".$user_id."%20is%20now%20".$pwd;
					$msg = rawurlencode($msg);
					$url="http://192.168.52.35:8888/send?phone=". $arr['user_mobile'] . "&msg=" . $msg;
					$handle = fopen($url, "r");
					echo "Password sent by Sms!";
				} catch (HttpException $ex) {
					echo $ex;
					return false;
				}
			break;
			case 1:
				$this->Subject="New password request";
				// $this->AddReplyTo($this->UserEmail,$this->UserFullName);
				// $this->Body="The randomly generated password for ".$user_id." is now ".$pwd;
				$this->Body = $msg;
				$this->ClearAddresses();
				$this->AddAddress($arr['user_email'], "");
				// echo success or error message.... sniff :'(
				if(!$this->Send()) {
					// password is updated anyway
					echo "Mailer Error: " . $this->ErrorInfo;
					return false;
				} else {
					echo "The password will soon arrive to your email address";
				}
			break;
			case 0:
			break;
		}
	}
// echo "Password updated";
	return true;
}

function nonconf(){
    
    // $sql="select user_email,user_mobile,user_alert,resource_name,(select user_email from ".dbHelp::getSchemaName().".user where user_id=resource_resp) as resp,(select user_alert from ".dbHelp::getSchemaName().".user where user_id=resource_resp) as resp_alert,entry_id,date_format(entry_datetime,'%d %M at %H:%i') as date from ".dbHelp::getSchemaName().".user,entry,resource where entry_status=2 and date_add(entry_datetime, interval resource_resolution*entry_slots+resource_confirmtol*resource_resolution+60 minute) between now() and date_add(now(),interval 60 minute) and entry_user=user_id and entry_resource=resource_id and resource_status<>4";
	// $sql="select user_email,user_mobile,user_alert,resource_name,(select user_email from ".dbHelp::getSchemaName().".user where user_id=resource_resp) as resp,(select user_alert from ".dbHelp::getSchemaName().".user where user_id=resource_resp) as resp_alert,entry_id,".dbHelp::getFromDate('entry_datetime','%d %M at %H:%i')." as date, entry_datetime from ".dbHelp::getSchemaName().".user,entry,resource where entry_status=2 and ".dbHelp::date_add('entry_datetime', 'resource_resolution*entry_slots+resource_confirmtol*resource_resolution+60','minute')." between now() and ".dbHelp::date_add('now()','60', 'minute')." and entry_user=user_id and resource_id=entry_resource and resource_status<>4";
    $sql = "
		select 
			user_email,
			user_mobile,
			user_alert,
			resource_name,
			entry_id,
			entry_datetime,
			entry_user,
			".dbHelp::getFromDate('entry_datetime','%d %M at %H:%i')." as date,
			(select user_email from ".dbHelp::getSchemaName().".user where user_id=resource_resp) as resp,
			(select user_alert from ".dbHelp::getSchemaName().".user where user_id=resource_resp) as resp_alert,
			resource_status
		from 
			".dbHelp::getSchemaName().".user
			, entry
			, resource 
		where 
			entry_status=2 and 
			entry_user=user_id and 
			entry_resource = resource_id and 
			resource_status<>4 and 
			".dbHelp::date_add('entry_datetime', 'resource_resolution*entry_slots+resource_confirmtol*resource_resolution+60','minute')." 
		between ".dbHelp::now()." and ".dbHelp::date_add(dbHelp::now(),'60', 'minute')
	;
	// ".dbHelp::date_add('entry_datetime', 'resource_resolution*entry_slots+resource_confirmtol*resource_resolution+60','minute')." between now() and ".dbHelp::date_add('now()','60', 'minute');
    $res=dbHelp::query($sql);
    while($arr=dbHelp::fetchRowByName($res)){
		$msg=date("Y-m-d H:i")." You did not confirm your entry on " . $arr['resource_name'] . " at ".$arr['entry_datetime'].". Please justify to ". $arr['resp'];
		switch($arr['user_alert']){
			case 2:
				try {
					$msg=str_replace(' ','%20',$msg);
					echo $msg;
					$url="http://192.168.52.35:8888/send?phone=". $arr['user_mobile'] . "&msg=" . $msg;
					$handle = fopen($url, "r");
				} catch (HttpException $ex) {
					echo $ex;
				}
			break;
			case 1:
				$this->Subject="No confirmation on ".$arr['date'];
				$this->ClearReplyTos();	//clear replys before sending the email
				$this->AddReplyTo("Resource Manager", $arr['resp']);
				$this->ClearAddresses();
				$this->AddAddress($arr['user_email'], "");

				$this->Body=$msg;
				echo $msg;
				if(!$this->Send()) {
					echo "Mailer Error: ".$this->ErrorInfo;
				} 
				// else {
					//echo "Message sent!";
				// }
			break;
			case 0:
			break;
		}
    }
    
} // end function

function fromAdmin($type,$extra=''){    
        if ($this->ResourceResp==$this->User) return;
        $extrainfo='';
        if ($extra!='') // fields for new or update entry
            foreach ($extra as $key => $value) {
            $extrainfo.= $key. ":".$value .";";
        }
        
        $sql="select user_id,user_email,".dbHelp::getFromDate('entry_datetime','%d, %M %Y')." as d, ".dbHelp::getFromDate('entry_datetime','%H:%i')." as t,user_mobile, user_alert, entry_comments from entry,".dbHelp::getSchemaName().".user,resource where entry_user=user_id and entry_id=". $this->LastEntry;
        $res=dbHelp::query($sql);
        $arr=dbHelp::fetchRowByName($res);
        //if ($arr['user_id']==$this->ResourceResp) exit;
        switch ($type) {
        case 'update':
            $msg="Entry on " . $this->ResourceName . " updated by resource administrator. New entry time " . $arr['t'] . " on the ". $arr['d'] . ". Visit calendar for further details";
            break;
        case 'confirm':
			if(isset($arr['entry_comments']))
				$msg="Entry on " . $this->ResourceName  . " at ". $arr['t'] . " on the ". $arr['d'] . " confirmed by administrator with the following comment:\n".$arr['entry_comments']."\n. Visit calendar for further details";
			else
				$msg="Entry on " . $this->ResourceName  . " at ". $arr['t'] . " on the ". $arr['d'] . " confirmed by administrator. Visit calendar for further details";
            break;    
        case 'delete':
            $msg="Your/some entry(ies) on  " . $this->ResourceName  . " was/were deleted by administrator";
            break;
        }
		
        switch ($arr['user_alert']) {
        case 2:
            try {
                $msg.="Do not reply to this SMS";
                $url="http://192.168.52.35:8888/send?phone=". $arr['user_mobile'] . "&msg=" .  $msg=str_replace(' ','%20',$msg);;
                $handle = fopen($url, "r");
            } catch (HttpException $ex) {
                echo $ex;
            }
        break;
        case 1:
            $this->Subject="Calendar administrator warning";
            // $msg.=". $extrainfo Do not reply to this email";
            $msg .= $extrainfo;
            $this->Body = $msg;
			$this->ClearReplyTos();	//clear replys before receiving any email
			$this->AddReplyTo($this->RespName, $this->RespEmail);
			$this->ClearAddresses();
			$this->AddAddress($arr['user_email'], "");
           if(!$this->Send()) {
                echo "Mailer Error: " . $this->ErrorInfo;
            } 
			// else {
                //echo "Message sent!";
            // }
            break;
            case 0:
            break;
        }
    
}//end function

function entriesReminder(){
	$sql = "select configParams_value from configParams where configParams_name like 'entryReminderHour'";
	$prep = dbHelp::query($sql);
	$row = dbHelp::fetchRowByIndex($prep);
	// Checks if its time to send the reminders (this should run every hour), midnight is 00
	if(isset($row) && $row[0] == date('H')){
	// if(isset($row) && $row[0] == 15){
		$daysToRemind = array(date("'Y-m-d'")); // will always remind on the day of the scheduled entries
		$sqlDays = "select configParams_value from configParams where configParams_name like 'entryReminderDaysBefore'";
		$prepDays = dbHelp::query($sqlDays);
		$rowDays = dbHelp::fetchRowByIndex($prepDays);
		// this is horrible, should be done in a way that didnt involve parsing a string
		$daysToRemindArray = explode(",", $rowDays[0]);
		foreach($daysToRemindArray as $day){
			$day = trim($day);
			$tempTime = strtotime($day." day", strtotime("now"));
			// Wont add the current day because its already included, change this later to be able to mail reminders not including the current day
			if($tempTime !== false && $day != '0'){
				$daysToRemind[] = date("'Y-m-d'", $tempTime);
			}
		}
				// AND date(entry_datetime)='".date('Y-m-d')."' 
				// AND date(entry_datetime)='2011-11-27' 
		$sql = "
			SELECT 
				user_email
				,user_login
				,resource_id
				,resource_name
				,resource_resolution
				,entry_datetime
				,entry_slots
			FROM 
				entry
				,user
				,resource
			WHERE
				entry_user=user_id 
				AND resource_id=entry_resource 
				AND date(entry_datetime) in (".implode(",", $daysToRemind).") 
				AND entry_status IN (1,2) 
			ORDER BY 
				user_login
				,resource_name
				,entry_datetime
		";

		$this->Subject="Entry Reminder";
		$this->ClearReplyTos();
		$this->AddReplyTo("support@cirklo.org");
		
		$tempEmail = "";
		$tempLogin = "";
		$tempResource = "";
		$prep = dbHelp::query($sql);
		while($row = dbHelp::fetchRowByName($prep)){
			// if($row['user_email'] != $tempEmail){
			if($row['user_login'] != $tempLogin){
				// if($tempEmail != ""){
				if($tempLogin != ""){
					$this->Body = $tempMsg;
					$this->ClearAddresses();
					$this->AddAddress($tempEmail, "");
					// send email
					if(!$this->Send()){
						echo "Email error: ".$this->ErrorInfo;
					}
				}
				$tempEmail = $row['user_email'];
				$tempLogin = $row['user_login'];
				// $tempMsg = "You have the following bookings for today (".convertDate($row['entry_datetime'], "d/m/Y")."):\n";
				$tempMsg = "You have the following bookings:\n";
				$tempResource = "";
			}
			if($row['resource_name'] != $tempResource){
				$tempResource = $row['resource_name'];
				$monday = date("Ymd", $this->getMondayFromDate(date('ymd')));
				$tempMsg .= "Resource '".$tempResource."'\n";
			}
			// needs static address for when its executed by a cronjob (loses the server info. request would have to be done by the client for it to work)
			$urlPath = " (https://agendo.cirklo.org/".substr($_SESSION['path'],3)."/weekview.php?resource=".$row['resource_id']."&date=".$monday.")";
			// $tempMsg .= "\tfrom ".convertDate($row['entry_datetime'], "H:i")." to ".date('H:i',(strtotime($row['entry_datetime']) + $row['entry_slots'] * $row['resource_resolution'] * 60))."\n";
			$tempMsg .= "\tOn ".convertDate($row['entry_datetime'], "d/m/Y")." from ".convertDate($row['entry_datetime'], "H:i")." to ".date('H:i',(strtotime($row['entry_datetime']) + $row['entry_slots'] * $row['resource_resolution'] * 60)).". User the link below for quick access:\n";
			$tempMsg .= "\t".$urlPath."\n";
		}
		
		// send email for the last person on the list
		if($tempEmail != ""){
			$this->Body=$tempMsg;
			$this->ClearAddresses();
			$this->AddAddress($tempEmail, "");
			if(!$this->Send()){
				echo "Email error: ".$this->ErrorInfo;
			}
		}
	}
}

function getMondayFromDate($date){
	$dateTime = strtotime($date);
	// int number corresponding to $date's  day of the week
	$weekDay = date('N', $dateTime);
	// gets $date's monday time
	$date = $dateTime - ($weekDay)*24*60*60;
	return $date;
}

} // end class
?>