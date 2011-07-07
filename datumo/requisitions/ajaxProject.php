<?php
require_once "../session.php";
$user_id = startSession();

//includes
require_once "../__dbConnect.php";
require_once "projectClass.php";

if(isset($_GET['graph'])){	drawGraphs($user_id);}
if(isset($_GET['info'])){	writeInfo($user_id);}

function drawGraphs($user_id){
	//get url variables
	if(isset($_GET['account']))	$account_id=$_GET['account'];
	
	//call classes
	$conn=new dbConnection();
	$project=new projectClass();
	//set account details
	$project->setAccountInfo($account_id);
	
	//labels and colors
	$curLabel="Current Budget";
	$curColor="#f7c439";
	$iniLabel="Initial Budget";
	$iniColor="#164f55";
	
	//draw graphs related to the project
	$iniBudget = $project->getIniBudget().", '$iniLabel', $iniColor";
	$curBudget = $project->getCurBudget().", '$curLabel', $curColor";
	$arr = array(0=>$curBudget, 1=>$iniBudget);
	
	echo "var arrayOfData = new Array([".$project->getIniBudget().",'$iniLabel','$iniColor'],";
	echo "[".$project->getCurBudget().", '$curLabel', '$curColor']);";
	
	//echo $curBudget.", Current Budget, #00FF00";
}

function writeInfo($user_id){
	//get url variables
	if(isset($_GET['account']))	$account_id=$_GET['account'];
	
	//call classes
	$conn=new dbConnection();
	$project=new projectClass();
	//set account details
	$project->setAccountInfo($account_id);
	echo "<h2 style='text-transform:uppercase'>Project</h2> ".$project->getProjectName();
	echo "<h2 style='text-transform:uppercase'>Account Number</h2> ".$project->getAccountNo();
	
	$timeToExpire = dateDiff("-", $project->getEndDate(), date("Y-m-d"));
	echo "<h2 style='text-transform:uppercase'>Time to expire</h2> ".$timeToExpire." (".$project->getEndDate().")";
	
	
	
}

/**
 *	Method to calculate the difference between two dates.
 *	It redirects the result to another method 
 *  
 */

function dateDiff($dformat, $endDate, $beginDate){
	$date_parts1=explode($dformat, $beginDate);
	$date_parts2=explode($dformat, $endDate);
	$start_date=gregoriantojd($date_parts1[1], $date_parts1[2], $date_parts1[0]);
	$end_date=gregoriantojd($date_parts2[1], $date_parts2[2], $date_parts2[0]);
	return timeFormat($end_date-$start_date);
	
}

/**
 * Method that receives the number of days and formats it in order to return the number of months and days, depending on its value
 * 
 */

function timeFormat($time){
	//if the number of days is negative
	if($time<0){
		return "<font color=#FF0000>EXPIRED</font>";
	}
	//Are we within 31 days?
	if($time>=0 and $time < 31){
		return "<font color=#FF0000>$time days EXPIRING</font>";
	}
	//More than a month left. Format the input value to return the number of months and days
	if($time>=31){
		//initialize variable
		$return="Approximately ";
		//calculate the number of months left
		$time=round($time/30.5);
		$return.=(int)$time." month(s)";
		//calculate the number of days in the last month
		$remainder=round($time%30.5);
		if($remainder!=0){
			$return .= " AND ".(int)($remainder/100*30.5)." days";
		}
		return $return;
	}
}


?>