<?php

if(isset($_GET['type']) and !isset($_GET['sidx'])){
	$type=$_GET['type'];
	switch($type){
		case 0:
			uploadImage();
			break;
		case 1:
			mailingList();
			break;
		case 2:
			sendMail();
			break;
		case 3:
			checkTable();
			break;
	}
}

function header_nav($user_id){
	require_once "dispClass.php";
	$display = new dispClass();
	require_once "menu.php";
	$menu= new menu($user_id);
	require_once "resClass.php";
	$perm = new restrictClass();
	
	//get user info
	$perm->userInfo($user_id);
	$login=$perm->getUserLogin();
	$level=$perm->getUserLevel();
	
	
	echo "<nav class=navigation>";
		echo "<ul class=dropdown id=menu>";
			echo "<li><a href=index.php>Home</a>";
			echo "<li><a>Reports</a>";
				echo "<ul class=dropdown>"; 
					echo "<li><a href=../agendo/hoursUsage.php>Usage report</a></li>";
					$plots=$menu->getPlots();
					echo "<li class='rtarrow'><a>Plots</a>";
						echo "<ul>";
						//loop through all plots
						foreach($plots as $key=>$value){
							echo "<li><a href=javascript:void(0) onclick=window.open('plot.php?plot_id=$key','_blank','width=820px,height=550px,menubar=yes')>$value</a></li>";
						}
						echo "</ul>";
					echo "</li>";
					//display export to Excel option if the current table is a view
					if(isset($table) and ($display->checkTableType($table) or $level==0))
						echo "<li><a href=excel.php?table=$table title='Export data to xls file'>Export to Excel</a></li>";
				echo "</ul>";
			echo "</li>";
			echo "<li><a>Tools</a>";
				echo "<ul class=dropdown>";
					echo "<li title='Set resource for local confirmation'><a href=../agendo/makeConfirmRes.php>In site confirmation</a></li>";
					echo "<li title='Give resource permissions to user'><a href=../agendo/givePermission.php>Resource permission</a></li>";
					echo "<li title='Send a message to other Agendo user'><a href=mailing.php>Send message</a></li>";
					echo "<li><a href=resupload.php>Resource image upload</a></li>";
					echo "<li title='Generate random passwords for multiple users'><a href=../agendo/massPassRenewal.php>Password generator</a></li>";
				echo "</ul>"; 
			echo "</li>";
			echo "<li><a>Help</a>";
				echo "<ul class=dropdown>";
					echo "<li><a href=http://www.cirklo.org/datumo_help.php target=_blank>Help</a></li>";
					echo "<li><a href=http://www.youtube.com/user/agendocirklo target=_blank>Tutorials</a></li>";
				echo "</ul>"; 
			echo "</li>";
			echo "<li><a href=javascript:void(0) onclick=window.open('helpdesk.php','_blank','height=400px,width=365px,resizable=no,menubar=no')>Helpdesk</a>";
			echo "<li><a>About</a>";
				echo "<ul class=dropdown>";
					echo "<li><a href=http://www.cirklo.org/datumo.php target=_blank>Datumo</a></li>";
					echo "<li><a href=http://www.cirklo.org target=_blank>Cirklo</a></li>";
				echo "</ul>"; 
			echo "</li>";
			//log in and out information
			echo "<li class=login>You are logged as $login! ";
			echo "<a href=session.php?logout style='color:#f7c439;text-decoration:underline;'>Sign out</a></li>";
			//External links
			echo "<li class=external>";
			echo "<a href='http://www.facebook.com/pages/Cirklo/152674671417637' target=_blank><img src=pics/fb.png width=30px border=0 title='Visit our Facebook page'>";
			echo "&nbsp;&nbsp;";
			echo "<a href='http://www.youtube.com/user/agendocirklo' target=_blank><img src=pics/youtube.png width=30px border=0 title='Feature videos'></a>";
			echo "</li>";
		echo "</ul>";
		
	echo "</nav>";
	
	
	
	
	
}

/**
 * Method to distinguish between odd and even numbers
 */

function is_odd($number) {
   	return $number & 1; // 0 = even, 1 = odd
}

/**
 * 
 * Method to split a string, add elements to each one of the resulting strings and glue them together
 * @param unknown_type $mainQuery
 * @param unknown_type $glue
 * @param unknown_type $appendQuery
 */



function splitString($mainQuery, $glue, $appendQuery){
	try{
		$query=explode($glue,$mainQuery);
		for($i=0;$i<sizeof($query);$i++){
			$query[$i].=$appendQuery;
		}
		$query=implode(" $glue ", $query);
	} catch(Exception $e){ //if it is a single query
		$query=$mainQuery.$resquery;
	}
	return $query;
}


function uploadImage(){
	require_once "session.php";
	startSession();
	require_once "__dbConnect.php";
	
	$conn=new dbConnection();
	
	//posted variables
	if(isset($_POST['resource'])){	
		$resource_id=$_POST['resource'];
		if($resource_id==0)	throwError($error=5);
	}
	
	/**UPLOAD OPTIONS**/
	//initialize error variable
	$error=0; //if variable is set to zero there's no error
	
	//maximum file size
	$maxSize=1000000;
	
	//folder where the file will be located
	$target_path=$_SESSION['path']."/pics/";
	
	//get file extension
	$filename = stripslashes($_FILES['file']['name']);
	$imgExtension=getExtension($filename);
	$imgExtension=strtolower($imgExtension);
	
	//file extension validation
	//jpg, png and gifs allowed
	if ($imgExtension!="jpg"
	and $imgExtension!="jpeg"
	and $imgExtension!="png"
	and $imgExtension!="gif"
	and $imgExtension!="JPG"
	and $imgExtension!="JPEG"
	and $imgExtension!="PNG"
	and $imgExtension!="GIF"){
		throwError($error=1);exit;
	}
	
	//filename length validation
	if(strlen($filename>30)){
		throwError($error=3);
		exit;
	}
	
	//get file size
	$size=filesize($_FILES['file']['tmp_name']);
	if($size>$maxSize){
		throwError($error=2);	
		exit;
	} 
	
	// Add the original filename to our target path.  
	$target_path = $target_path.$filename; 
	try{
		//safety query -> delete all pictures that are associated with this resource
		$query="DELETE FROM pics WHERE pics_resource=$resource_id";
		$conn->query($query);
		
		$query2="INSERT INTO pics VALUES ('',$resource_id,'$filename')";
		$conn->query($query2);
  		
  		//upload file to server
		if(!move_uploaded_file($_FILES['file']['tmp_name'], $target_path))
			throwError($error=4);
		
		//redirect page 
		echo "<script type='text/javascript'>";
		echo "window.location='resupload.php?success';";
		echo "</script>";
	} catch(Exception $e){
	   	throwError($error=4);
	}
}

function throwError($error){
	$err=array(
		"1"=>"File extension not allowed (Allowed Extensions: .jpg, .gif, .png)",
		"2"=>"Image size limit exceeded (1 Mb)",
		"3"=>"Image name length exceeded (30 chars)",
		"4"=>"There was an error uploading the file, please try again",
		"5"=>"Please select a valid resource");
	echo "<script type='text/javascript'>";
	echo "window.location='resupload.php?error=$err[$error]';";
	echo "</script>";
}

function getExtension($str) {
	$i = strrpos($str,".");
    if (!$i) { return ""; }
    $l = strlen($str) - $i;
    $ext = substr($str,$i+1,$l);
    return $ext;
}

function mailingList(){
	//PHP includes
	require_once "session.php";
	startSession();
	require_once "__dbConnect.php";

	//call class
	$conn=new dbConnection();
	
	if(isset($_GET['list']))	$list=$_GET['list'];
	switch ($list){
		case "all":	//all users from the database
			echo "This email will be sent to all registered users";
			break;
		case "department":	//select department
			$query="SELECT department_id, department_name FROM department ORDER BY department_name";
			$sql=$conn->query($query);
			echo "<select multiple size=9 style='width:200px' name=mailSelector id=mailSelector>";
			//loop through all deps
			for($i=0;$row=$sql->fetch();$i++){
				echo "<option value=$row[0]>$row[1]</option>";
			}
			echo "</select>";
			break;
		case "resource":	//select resource
			$query="SELECT resource_id, resource_name FROM resource ORDER BY resource_name";
			$sql=$conn->query($query);
			echo "<select multiple size=9 style='width:200px' name=mailSelector id=mailSelector>";
			//loop through all deps
			for($i=0;$row=$sql->fetch();$i++){
				echo "<option value=$row[0]>$row[1]</option>";
			}
			echo "</select>";
			break;
		case "resourcetype":			//select resourceType
			$query="SELECT resourcetype_id, resourcetype_name FROM resourcetype ORDER BY resourcetype_name";
			$sql=$conn->query($query);
			echo "<select multiple size=9 style='width:200px' name=mailSelector id=mailSelector>";
			//loop through all deps
			for($i=0;$row=$sql->fetch();$i++){
				echo "<option value=$row[0]>$row[1]</option>";
			}
			echo "</select>";
			break;
	}
}

function sendMail(){
	//PHP includes
	require_once "session.php";
	$user_id=startSession();
	require_once "__dbConnect.php";
	require_once "mailClass.php";
	require_once "resClass.php";
	
	//call classes
	$conn=new dbConnection();
	$mail=new mailClass();
	$res=new restrictClass();
	
	if(isset($_GET['list']))	$list=$_GET['list'];
	if(isset($_GET['recipient']))	$recipient=$_GET['recipient'];
	if(isset($_GET['subject'])){
		$subject="[AGENDO] ";	//initialize subject
		$subject.=$_GET['subject'];
	}
	if(isset($_GET['message']))	$message=$_GET['message'];
	

	//get user info
	$res->userInfo($user_id);
	$from=$res->getUserEmail();	//current user email
	
	switch($list){
		case "all":	//all users from the database
			//get all users from the database
			$query="SELECT DISTINCT user_email FROM user";
			$sql=$conn->query($query);
			//loop through all query results
			for($i=0;$row=$sql->fetch();$i++){
				$address[]=$row[0];	//store email into an array
			}
			break;
		case "department":
			//loop through all selected departments
			foreach($recipient as $department_id){
				$query="SELECT DISTINCT user_email FROM user WHERE user_dep=$department_id";
				$sql=$conn->query($query);
				//loop through all query results
				for($i=0;$row=$sql->fetch();$i++){
					$address[]=$row[0];	//store email into an array
				}
			}
			break;
		case "resource":	
			//loop through all selected resources
			foreach($recipient as $resource_id){
				$query="SELECT DISTINCT user_email
					FROM user, permissions 
					WHERE permissions_user=user_id 
					AND permissions_resource=$resource_id UNION 
					SELECT user_email FROM user WHERE user_id 
					IN (SELECT resource_resp FROM resource WHERE resource_id=$resource_id)";
				$sql=$conn->query($query);
				//loop through all query results
				for($i=0;$row=$sql->fetch();$i++){
					$address[]=$row[0];
				}
			}	
			break;
		case "resourcetype":
			//loop through all selected resource type
			foreach($recipient as $resourcetype_id){
				$query="SELECT DISTINCT resource_id FROM resource WHERE resource_type=$resourcetype_id";
				$sql=$conn->query($query);
				//loop through all query results
				for($i=0;$row=$sql->fetch();$i++){
					
					$query_="SELECT DISTINCT user_email 
					FROM user, permissions 
					WHERE permissions_user=user_id 
					AND permissions_resource=$row[0] UNION 
					SELECT user_email FROM user WHERE user_id 
					IN (SELECT resource_resp FROM resource WHERE resource_id=$row[0])";
					$sql_=$conn->query($query_);
					for($j=0;$row_=$sql_->fetch();$j++){
						$address[]=$row[0];
					}
				}
			}	
			break;
	}
	
	//send output
	$output=$mail->mailingList($subject,$address,$from,$message);
	echo $output;
}


function checkTable(){
	//PHP includes
	require_once "session.php";
	$user_id=startSession();
	require_once "__dbConnect.php";
	require_once "queryClass.php";
	require_once "resClass.php";
	
	//call classes
	$conn=new dbConnection();	
	$q=new queryClass();
	$perm=new restrictClass();
	
	//url variables
	if(isset($_GET['field']))	$attr=$_GET['field'];
	
	//get table from the sent attribute
	$arr=array($attr,$conn->getDatabase(),'','');
	$row=$q->prepareQuery($arr, 3);
	$objName=$row[0];
	
	//check if this user has permissions to insert a new entry in the target table
	$perm->tablePermissions($objName, $user_id);
	if($perm->getInsert()) {	//user has permissions to insert
		echo $objName;
	}
}


?>