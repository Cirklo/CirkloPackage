<?php

if(isset($_GET['type']) and !isset($_GET['sidx'])){
	$type=$_GET['type'];
	switch($type){
		case 0:
			uploadImage();
			break;
	}
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

?>