<?php
require_once "session.php";
$user_id=startSession();
require_once "__dbConnect.php";

//call database class
$conn=new dbConnection();
$database=$conn->getDatabase();

if(isset($_GET['export'])){	//is this calling for a gmail synchronization
	require_once "mailClass.php";
	require_once "resClass.php";
	
	$perm=new restrictClass();
	$mail=new mailClass();
	if(isset($_GET['events'])){
		$event=$_GET['events'];
		//print_r($event);
	}
	
	//get user info
	$perm->userInfo($user_id);
	$user_email=$perm->getUserEmail();
	
	//get entry id information
	$query="SELECT * FROM entry, resource WHERE resource_id=entry_resource AND entry_id=".$event["entry_id"];
	$sql=$conn->query($query);
	$row=$sql->fetch();
	
	//set entry datetime
	$datetime=$row["entry_datetime"];
	$empty=array(" ",":","-");	//set characters to remove
	$datetime=str_replace($empty, "", $datetime);	//build nice datetime dor the ics to read it
	
	$year=substr($datetime,0,4);
    $month=substr($datetime,4,2);
    $day=substr($datetime,6,2);
    $hour=substr($datetime,8,2);
    $min=substr($datetime,10,2);

    //set ics attachment
	$att = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Cirklo Agendo
BEGIN:VEVENT
UID:". $row[0] . "@agendo
DTSTAMP:" . $year.$month.$day."T".$hour.$min."00"."
DTSTART:" . $year.$month.$day."T".$hour.$min."00"."
DTEND:" . $year.$month.$day."T". date("Hi",mktime($hour,$row["entry_slots"]*$row["resource_resolution"])) ."00
SUMMARY: ". $event["title"] . "
DESCRIPTION:
END:VEVENT
END:VCALENDAR";
	
	//set mail variables
	$subject="[AGENDO] myCalendar sync";
	$msg="Import the attached file to your personal calendar";
	$to=$user_email;
	$from="";
	//send email
	echo $mail->sendMail($subject, $to, $from, $msg, $att);


} else {

	//initialize variables
	try{
		/*$query="SELECT task_id, action_name, task_description, task_date, user_login
	  	FROM task, action, user
	  	WHERE action_id=task_action
	  	AND task_user=user_id";*/
		if(isset($_GET['manager'])){
			//is this user a department manager??
			$sql=$conn->query("SELECT department_id, department_name FROM department WHERE department_manager=$user_id");
			if($sql->rowCount()>0){ //department manager
				//initialize variables to store department and user ids
				$deps=array();
				$users=array();
				for($i=0;$row=$sql->fetch();$i++){ //loop through all departments
					//get a list of users for each department
					$sql2=$conn->query("SELECT user_id FROM $database.user WHERE user_dep=$row[0]");
					for($j=0;$row2=$sql2->fetch();$j++){
						$users[]=$row2[0];
					}
				}
				$arr=implode(",",$users);
				$query="SELECT entry_id, resource_name, entry_datetime, DATE_ADD( entry_datetime, INTERVAL entry_slots * resource_resolution MINUTE ) AS entry_time, user_login, color_code
					FROM entry, resource, user, color
					WHERE entry_user=user_id
					AND entry_resource=resource_id
					AND resource_color=color_id
					AND entry_status IN (1,2)
					AND entry_user IN ($arr)";
				//echo $query;
			}
		} elseif (isset($_GET['regular'])) { //regular user
			$query="SELECT entry_id, resource_name, entry_datetime, DATE_ADD( entry_datetime, INTERVAL entry_slots * resource_resolution MINUTE ) AS entry_time, user_login, color_code
				FROM entry, resource, user, color
				WHERE entry_user=user_id
				AND entry_resource=resource_id
				AND resource_color=color_id
				AND entry_status IN (1,2)
				AND (entry_user=$user_id 
				OR resource_resp=$user_id)";
			if(isset($_GET['ids'])){
				$arr=$_GET['ids'];
				$query.=" AND resource_id IN ($arr)";
			}
		}
		
	  	$sql=$conn->query($query);
		//loop through all results
		for($i=0;$row=$sql->fetch();$i++) {
		   	//build message to be displayed
		   	$json[$i]["id"]=$i+1;
		   	$json[$i]["title"]=$row[1].": ".$row[4];
		   	$json[$i]["start"]=$row[2];//$row[3];
		   	$json[$i]["end"]=$row[3];//$row[3];
		   	$json[$i]["allDay"]=false;
		  	$json[$i]["color"]="#".$row[5];
		  	$json[$i]["entry_id"]=$row[0];
		   	//page to use for google calendar http://www.google.com/calendar
		}
	} catch(Exception $e){
	   	echo $e->getMessage();
	}
	
	//need to print to json format
	//print_r($ret);
	echo json_encode($json);
}


?>

