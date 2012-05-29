<?php
// This file was altered by Pedro Pires (The chosen two)
	session_start();
	$pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
	$_SESSION['path'] = "../".$pathOfIndex[sizeof($pathOfIndex)-1];
	require_once("../agendo/commonCode.php");
	initSession();
	require_once("../agendo/calClass.php");
	require_once("../agendo/functions.php");
	require_once("../agendo/genMsg.php");

	if (isset($_GET['date']) && $_GET['date'] != ""){
		$calendarDate = cleanValue($_GET['date']);
	} else {        
		$calendarDate = date("Ymd",mktime(0,0,0, date("m"), date("d")-date("N"),date("Y")));
	}
	
	$ressql = "select resource_status, resource_mac, resource_id from resource where resource_id = :0";
	$res = dbHelp::query($ressql,array(cleanValue($_GET['resource'])));
	$arr = dbHelp::fetchRowByName($res);
	$resource = $arr['resource_id'];
	// $isResp = isResp(); // make this var?
	
	if(isset($_POST['functionName'])){
		call_user_func(cleanValue($_POST['functionName']));
		exit;
	}
	
	importJs();
	echo "<script type='text/javascript' src='../agendo/js/weekview.js'></script>";
	// Sets the resource and date in JS for later use in the auto refresh, yep the patching continues
	echo "<script type='text/javascript'> setDateAndResource(".$resource.",'".$calendarDate."'); </script>";
	// <META HTTP-EQUIV="REFRESH" CONTENT="180" />

?>

<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" /> 
<meta name="keywords" content="" />
<meta name="description" content="" /> 
<link href="../agendo/css/style.css" rel="stylesheet" type="text/css" />
<link href="../agendo/css/common.css" rel="stylesheet" type="text/css" />
<link href="../agendo/css/CalendarControl.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../agendo/js/cal.js"></script>
<script type="text/javascript" src="../agendo/js/ajax.js"></script>
<script type="text/javascript" src="../agendo/js/datefunc.js"></script>
<script type="text/javascript" src="../agendo/js/overdiv.js"></script>


<?php
//*************Check for view without logging in********************
if(!secureIpSessionLogin()){
	echo "<a href='index.php' style='color:#F7C439;'>Back to index</a>";
	showMsg('Please sign in to be able to view resources', true);
	exit;
}
//******************************************************************

//disable warning displays
error_reporting(0);

// $resource=clean_input($_GET['resource']);
// $ressql = "select resource_status, resource_mac from resource where resource_id = ".$resource;

// Flags for the resource type/status
$imResstatus5 = ($arr['resource_status'] == 5); // quick scheduling
$imResstatus3 = ($arr['resource_status'] == 3); // user confirmation
$imResstatus6 = ($arr['resource_status'] == 6); // sequencing

// *************************** applet for mac address *********************
if($imResstatus3){ // user confirmation
	echo "<object type='application/x-java-applet' WIDTH='1' HEIGHT='1' id='zeeApplet'>";
		echo "<param name='codebase' value = '../agendo' />";
		echo "<param name='archive' value='macApp.jar'/>";
		echo "<param name='code' value='MacAddressApplet'/>";
		echo "<param name='scriptable' value='true'/>";
		
		echo "<param name='color' value='#1e4F54'/>";
		echo "<param name='action' value='checkMac'/>";
		echo "<param name='mac' value='".$arr['resource_mac']."'/>";
	echo "</object>";
	
	// Checks if the macaddress associated to this resource is the current one
	echo "<script type='text/javascript'>";
		echo "macConfimation('".$arr['resource_mac']."');";
	echo "</script>";
}
//*************************************************************************


if (isset($_GET['update'])) {$update=cleanValue($_GET['update']);$entry=cleanValue($_GET['update']);} else {$update=0;} ;
//instatiation for calendar
$calendar=new cal($resource,$update);

//getting the variables 
if (isset($_GET['action'])) {$action = cleanValue($_GET['action']);} else {$action = 0;} ;
if (isset($_GET['msg'])) {$msg = cleanValue($_GET['msg']);} else {$msg ='';} ;
 
//html body starts Here
//##############################################
echo "<body onload=init(" . $calendar->getStatus() . "," . $calendar->getMaxSlots() . ")>";

//for displaying help
echo "<div id=help style='display:none;position:absolute;border-style:solid;border-width:1px;background-color: white;z-index:99;padding:3px;'>";
echo "<p style='text-align:center'>Equipment status: " . $calendar->getStatusName()."</p>";
echo "<p style='text-align:center'>Equipment Responsible: <a href=mailto:" . $calendar->getRespEmail() . ">". $calendar->getRespName() . "</a>"."</p>";
echo "<p style='text-align:center'>Delete Tolerance: " . $calendar->getDelTolerance(). " hour(s)"."</p>";
echo "<p style='text-align:center'>Daily Maximum Slot Number: " . $calendar->getMaxSlots()."</p>";
echo "<p style='text-align:center'><a target=_new href=../agendo/prices.php>Click to look at prices (excluding VAT and dedicated assistance)</a></p>";
if ($calendar->getStatus()==3) echo "<p style='text-align:center'>Tolerance for confirmation " . $calendar->getConfTolerance()*$calendar->getResolution()/60 . " hours(s) before or after entry"."</p>";
echo "<p style='text-align:center'>Further info: <a href="  . $calendar->getLink() . ">" .$calendar->getLink() . "</a></p>" ;
echo "<hr />";

echo "<table id='legend'>";
	echo "<tr>";
		echo "<td bgcolor=".cal::RegCellColorOff.">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>Confirmed</td>";
		echo "<td bgcolor=".cal::PreCellColorOff.">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>To be confirmed</td>";
		echo "<td bgcolor=".cal::ErrCellColorOff.">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>Unconfirmed</td>";
		echo "<td bgcolor=".cal::MonCellColorOff.">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>Monitored</td>";
		echo "<td bgcolor=".cal::InUseCellColorOff.">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>In use</td>";
	echo "</tr>";
echo "</table>";

if($calendar->monitor($calendar->getResourceName())){ // same object using 2 different methods one inside the other? :(
	echo "<hr><p style='text-align:center'><a href=../agendo/monitor.php target='blank'>Monitored resource</a>";
}
if ($calendar->getLink()!='') echo "<hr><p style='text-align:center'>More info <a href='" . $calendar->getLink() . "' target=_blank>here</a>";
echo "<p style='text-align:right'><a href=# onclick=\"javascript:d=document.getElementById('help');d.style.display ='none'\">close</a>";
echo "</div>";

//for displaying calendar
$sql = "SELECT configParams_name, configParams_value from configParams where configParams_name='institute' or configParams_name='shortname' or configParams_name='url'";
$res = dbHelp::query($sql);
$instituteArray = array();
while($arr = dbHelp::fetchRowByName($res)){
	$instituteArray[$arr['configParams_name']] = $arr['configParams_value'];
}

//This is where msgs are displayed (changed)
echo "<div id='msg' style=\"top:200;left:178px;width:574px;height:50;filter:alpha(opacity=0);line-height:50px;\" class=msg >";
	echo $msg;
echo "</div>";
echo "<div style='margin:auto;width:1024px;height:100%;height:100%;'>";
// echo "<title>".strtoupper($institute[1])." Reservations</title>";
echo "<title>".strtoupper($instituteArray['shortname'])." Reservations</title>";
// echo "<center>";
echo "<table id='master' style='margin:auto' width=750>";
	echo "<tr>";
		echo "<td colspan=2>";
			echo "<table class=logo id='logo'>";
				echo "<tr>";
					echo "<td style='font-size:11px;text-align:left' >";
						echo "<table style='height:100%'>";
						echo "<tr>";
							echo "<td style='padding-left:10px'>";
							// echo "<h1><a href=index.php>".strtoupper($institute[1])." Reservations</a></h1>";
							echo "<h1><a href=index.php>".strtoupper($instituteArray['shortname'])." Reservations</a></h1>";
							echo "</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td style='padding-left:10px'>";
							// echo "<h3><a href='http://".$institute[2]."'>".$institute[0]."</a></h3>";
							echo "<h3><a href='http://".$instituteArray['url']."'>".$instituteArray['institute']."</a></h3>";
							echo "</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td style='padding-left:10px'>";
							echo "<h2>" . date ("D,d M Y"). "</h2>";
							echo "</td>";
						echo "</tr>";
						echo "</table>";
					echo "</td>";
					
					echo "<td align=right>";
						echo "<table style='height:100%'>";
						echo "<tr valign='top'>";
							echo "<td>";
							loggedInAs("weekview", $calendar->getResource());
							echo "</td>";
						echo "</tr>";
						echo "<tr valign='bottom'>";
							echo "<td>";
							// Links for help, videos, resources and user/management
							echoUserVideosResourceHelpLinks();
							echo "</td>";
						echo "</tr>";
						echo "</table>";
					echo "</td>";
				echo "</tr>";
			echo "</table>";

			// Videos div
			echoVideosDiv();
			
			// Resources div
			echoResourcesDiv();

			// Group View
			require_once('../agendo/monitoring.php');
			
			// User/management div
			echoUserDiv("weekview", $calendar->getResource());
		echo "</td>";
	echo "</tr>";
			
// *******************************************        calendar stuff made here     ***********************************************************
			$calendar->setStartDate($calendarDate);
			if ($calendar->getStatus()==0 or $calendar->getStatus()==2) { //inactive or invisible
				echo "<tr>";
					echo "<td>";
					echo "<h1 style='color:#cc8888'>".$calendar->getResourceName()." not available for reservations</h1>";
					$sql ="SELECT user_firstname,user_lastname,user_email from ".dbHelp::getSchemaName().".user,resource where user_id=resource_resp";
					$res = dbHelp::query($sql);
					$arr = dbHelp::fetchRowByName($res);
					echo "<h2>Please contact ".$arr['user_firstname']." ".$arr['user_lastname']."</h2>";
					echo "<a href=weekview.php?resource=" . ($calendar->getResource() -1) . "&date=" . $calendar->getStartDate() . "><img border=0 src=pics/resminus.png></a>";
					echo "<a href=weekview.php?resource=" . ($calendar->getResource() +1) . "&date=" . $calendar->getStartDate() . "><img border=0 src=pics/resplus.png></a>";
					echo "</td>";
				echo "</tr>";
				exit;
			}

			if(isset($_POST['action'])){ // is this of any use?
				call_user_func(cleanValue($_POST['action']));
			}
			
			if(isset($_GET['entry'])){ // same as the next if?
				$entry=(int)cleanValue($_GET['entry']);
				$calendar->setEntry($entry);
				$sql ="SELECT xfields_name, xfieldsval_value, xfields_label, xfields_type, xfields_id, xfields_placement from xfields, xfieldsval, xfieldsinputtype where xfieldsval_field=xfields_id and xfields_resource=" . $calendar->getResource(). " and xfieldsval_entry=".$calendar->getEntry()." and xfields_placement = 1 group by xfields_id, xfields_type";
				
				// $sqlWeekDay = "select ".dbHelp::date_sub(dbHelp::getFromDate('entry_datetime','%Y%m%d'),'1','day')." from entry where entry_id=".$calendar->getEntry();
				// $res = dbHelp::query($sqlWeekDay);
				$sqlWeekDay = "select ".dbHelp::date_sub(dbHelp::getFromDate('entry_datetime','%Y%m%d'),'1','day')." from entry where entry_id=:0";
				$res = dbHelp::query($sqlWeekDay, array($entry));
				$arr1 = dbHelp::fetchRowByIndex($res);
				
				$sqlWeekNumber = "select ".dbHelp::getFromDate("'".$arr1[0]."'",'%w');
				$res = dbHelp::query($sqlWeekNumber);
				$arr2 = dbHelp::fetchRowByIndex($res);
				
				// $sqle="select entry_user, entry_repeat, ".dbHelp::getFromDate(dbHelp::date_sub("'".$arr1[0]."'",$arr2[0],'day'),'%Y%m%d')." as date, ".dbHelp::getFromDate('entry_datetime','%h')." as dateHour, ".dbHelp::getFromDate('entry_datetime','%i')." as dateMinutes, entry_slots from entry where entry_id=".$calendar->getEntry();
				// $rese=dbHelp::query($sqle);
				$sqle="select entry_user, entry_repeat, ".dbHelp::getFromDate(dbHelp::date_sub("'".$arr1[0]."'",$arr2[0],'day'),'%Y%m%d')." as date, ".dbHelp::getFromDate('entry_datetime','%h')." as dateHour, ".dbHelp::getFromDate('entry_datetime','%i')." as dateMinutes, entry_slots from entry where entry_id=:0";
				$rese=dbHelp::query($sqle, array($entry));
				$arre= dbHelp::fetchRowByName($rese);
				
				$calendar->setStartDate($arre['date']);
				$calendar->setCalRepeat($arre['entry_repeat']);
				$user=$arre['entry_user'];
				$nslots=$arre['entry_slots'];
				$entrystart= $arre['dateHour'] + $arre['dateMinutes']/60;
				
			}
			elseif($update != 0){ // same as the previous if?
				$calendar->setEntry($update);
				$sql ="SELECT xfields_name, xfieldsval_value, xfields_label, xfields_type, xfields_id, xfields_placement from xfields, xfieldsval, xfieldsinputtype where xfieldsval_field=xfields_id and xfields_resource=" . $calendar->getResource(). " and xfieldsval_entry=".$calendar->getEntry()." and xfields_placement = 1 group by xfields_id, xfields_type";
				
				// $sqle="select entry_user, entry_repeat, @d:= date_format(date_sub(entry_datetime,interval 1 day),'%Y%m%d'),  @wd:=date_format(@d,'%w'), date_format(date_sub(@d, interval @wd day),'%Y%m%d') as date, date_format(entry_datetime,'%h') + date_format(entry_datetime,'%i')/60 as starttime, entry_slots from entry where entry_id=".$calendar->getEntry();
				$sqlWeekDay = "select ".dbHelp::date_sub(dbHelp::getFromDate('entry_datetime','%Y%m%d'),'1','day')." from entry where entry_id=".$calendar->getEntry();
				$res = dbHelp::query($sqlWeekDay);
				$arr1 = dbHelp::fetchRowByIndex($res);
				
				$sqlWeekNumber = "select ".dbHelp::getFromDate("'".$arr1[0]."'",'%w');
				$res = dbHelp::query($sqlWeekNumber);
				$arr2 = dbHelp::fetchRowByIndex($res);
				
				$sqle="select entry_user, entry_repeat, ".dbHelp::getFromDate(dbHelp::date_sub("'".$arr1[0]."'",$arr2[0],'day'),'%Y%m%d')." as date, ".dbHelp::getFromDate('entry_datetime','%h')." as dateHour, ".dbHelp::getFromDate('entry_datetime','%i')." as dateMinutes, entry_slots from entry where entry_id=".$calendar->getEntry();
				$rese=dbHelp::query($sqle);
				$arre= dbHelp::fetchRowByName($rese);
				
				$calendar->setStartDate($arre['date']);
				$calendar->setCalRepeat($arre['entry_repeat']);
				$user=$arre['entry_user'];
				$nslots=$arre['entry_slots'];
				$entrystart= $arre['dateHour'] + $arre['dateMinutes']/60;
			}
			else{
					$calendar->setEntry(0);
					$entrystart=0;
					$nslots=1;
					$user='';
					$sql ="SELECT xfields_name, xfields_label, xfields_type, xfields_id, xfields_placement from xfields, xfieldsinputtype where xfields_resource=". $calendar->getResource()." and xfields_placement = 1  group by xfields_id, xfields_type";
			}
// **************************************************    end of calendar stuff being done      **********************************************************


	echo "<tr>";
		// ************************************************************    entry div stuff here   ***************************************************************************
		echo "<td style='vertical-align:top;height:100%;'>";
			$res=dbHelp::query($sql);
			$nxfields=dbHelp::numberOfRows($res);
			echo "<div id=entrydiv class=entrydiv>";
				echo "<form name=entrymanage id=entrymanage >";
				echo "<table id=entryinner class=entryinner align=center>";
					echo "<tr>";
						echo "<td colspan=2>";
						echo "<table style='width:160px;padding:0px;' align=center>";
							echo "<tr>";
								echo "<td>";
								$sql="select resource_status,resource_id from resource where resource_status not in (0,2) order by resource_name";
								$resResources=dbHelp::query($sql);
								$nextDetected = false;
								$currentDetected = false;
								// Loop that finds the previous and next "line" composed of resourceStatus and resourceId of the $calendar->getResource() id
								while($resArray = dbHelp::fetchRowByIndex($resResources)){
									$currentId = $resArray[1];
									if($calendar->getResource()!=$currentId){
										if(!$currentDetected){
											$arrprev=$resArray;
										}
										else if(!$nextDetected){
											$arrnext = $resArray;
											$nextDetected = true;
										}
									}
									else{
										$currentDetected = true;
									}
								}

								$n=0;

								if (sizeof($arrprev)!=0) {
									echo "<a href=weekview.php?resource=" . $arrprev[1] . "&date=" . $calendar->getStartDate() . "><img border=0 src=pics/resminus.png></a>"; 
								} else {
									echo "<img border=0 src=pics/resminus.png>"; 
								}
								echo "</td>";
								
								echo "<td style='width:140px;vertical-align:middle;text-align:center'><a style='font-size:20px' href=\"javascript:d=document.getElementById('help');d.style.display ='block';AssignPosition(d)\">";
								echo "<img height=64 src=pics/" . $calendar->getResourceImage() . " border=0 size=64></a>";
								echo "</td>";
							
								echo "<td>";
								$n=0;
								if (sizeof($arrnext)!=0) {
									echo "<a href=weekview.php?resource=" . $arrnext[1] . "&date=" . $calendar->getStartDate() . "><img border=0 src=pics/resplus.png /></a>";
								} else {
									echo "<img border=0 src=pics/resplus.png>"; 
								}
								echo "</td>";
								
							echo "</tr>";
						echo "</table>";

					echo "<tr>";
						echo "<td colspan=2>";
							echo "<h2 align=center>". $calendar->getResourceName() ."</h2>";
						// ************************************** mac green/red light ************************************
						if($imResstatus3){
							echo "<hr>";
							echo "<div id='possibleConfirmation' style='text-align:center;font-size:13px;color:#EAE8D5;'>";
								$confirmationImgStyle = "style='display:none;height:17px;width:17px;float:left;padding-left:5px;'";
								echo "<img id='possibleConfirmationImgOk' src='pics/green_light.png' ".$confirmationImgStyle."/>";
								echo "<img id='possibleConfirmationImgNotOk' src='pics/red_light.png' ".$confirmationImgStyle."/>";
								echo "<a id='possibleConfirmationText' style='color:#EAE8D5;'>";
									echo "Checking if confirmation is possible";
								echo "</a>";
							echo "</div>";
						}
						// ***********************************************************************************************
						echo "</td>";
					echo "</tr>";
						
					echo "<tr>";
						echo "<td colspan=2>";
						echo "<hr>";
						echo "</td>";
					echo "</tr>";

					//***********************************************
					//************* Weekly hours left ***************
					// if(isset($_SESSION['user_id']) && $calendar->getRespId() != $_SESSION['user_id']){
						// $day=substr($calendar->getStartDate(),6,2);
						// $month=substr($calendar->getStartDate(),4,2);
						// $year=substr($calendar->getStartDate(),0,4);
						// $slotDate = date('Ymd', mktime(0, 0, 0, $month, (int)$day + 1, $year));
						// $day=substr($slotDate,6,2);
						// $month=substr($slotDate,4,2);
						// $year=substr($slotDate,0,4);
						
						// $arrSRM = getSlotsResolutionMaxHours($day, $month, $year, $_SESSION['user_id'], $calendar->getResource());
						// $totalSlots = $arrSRM[0];
						// $resolution = $arrSRM[1];
						// $maxHours 	= $arrSRM[2];

						// if($arrSRM[3] != $user_id && $maxHours != 0){
							// $timeUsed = $totalSlots * $resolution/60;
							// $maxSlots = $maxHours * 60 / $resolution;
							// $slotsLeft = $maxSlots - $totalSlots;
							
							// if($slotsLeft < 0){
								// $slotsLeft = 0;
							// }
							
							// $timeLeft = $maxHours - $timeUsed;
							// if($timeLeft < 0){
								// $timeLeft = 0;
							// }
						$timeSlotsText = getTimeAndSlotsLeft();
						if($timeSlotsText !== false){
							echo "<tr>";
								echo "<td colspan='2' style='text-align:center;' id='hoursLeftTd'>";
								// echo "You have ".$timeLeft." hours (".$slotsLeft." entries) left to book";
								echo $timeSlotsText;
								echo "</td>";
							echo "</tr>";
							
							echo "<tr>";
								echo "<td colspan='2'>";
								echo "<hr>";
								echo "</td>";
							echo "</tr>";
						}
					// }
					//************* Weekly hours left ***************
					//***********************************************
	
					if($calendar->monitor($calendar->getResourceName())){ // ...this doesnt make a whole lot of sense
						echo "<tr><td colspan=2 align=center><a href=ekrano target='blank'>Monitored resource</a></td></tr>";
						echo "<tr><td colspan=2><hr></td></tr>";
					}
					
					// similar stuff
					// $sqlSimilar = "select similarresources_similar, resource_name from resource, (select similarresources_similar from similarresources where similarresources_resource = ".$resource.") as similars where resource_id = similarresources_similar";
					// $resSimilar = dbHelp::query($sqlSimilar);
					// if(dbHelp::numberOfRows($resSimilar)>0){
						// echo "<tr>";
							// $extraGet = "";
							// if(isset($_GET['date'])){
								// $extraGet = "&date=".$_GET['date'];
							// }
							// echo "<td colspan=2>Similar Resources</td>";
						// echo "</tr>";
					
						// echo "<tr>";
							// echo "<td colspan=2>";
							// echo "<select id='similarResources' class='similar' onChange=window.location='./weekview.php?resource='+this.value>";
							// echo "<option value='".$resource."' >This Resource</option>";

							// while($arrSimilar = dbHelp::fetchRowByIndex($resSimilar)){
								// echo "<option value='".$arrSimilar[0]."' >".$arrSimilar[1]."</option>";
							// }
							// echo "</select>";
							// echo "</td>";
						// echo "</tr>";

						// echo "<tr>";
							// echo "<td colspan=2>";
							// echo "<hr>";
							// echo "</td>";
						// echo "</tr>";
					// }
					// /similar stuff
					
				
					$buttonDisplay1 = $buttonDisplay2 = $buttonDisplay3 = $buttonDisplay4 = $buttonDisplay5 = 'inline-table';
					$tdButtonsDisplay = 'table-row';
					$confirmButtonOnclick = "ManageEntries('confirm');"; // default confirm action
					// Used to hide buttons
					if(!$imResstatus5){
					//**********************
						echo "<tr>";
							echo "<td colspan=2>Repeat Week Pattern</td>";
						echo "</tr>";
						
						echo "<tr>";
							echo "<td>";
							echo "<center><input lang=send type=checkbox onkeypress='return noenter()' onclick=\"activate_date(document.entrymanage.enddate);\" id=repeat name=repeat value=1></td><td><input style='width:70px' class=inpbox name='enddate' lang=send id='enddate' size=9 disabled=true type=textbox value=''>";
							echo "</td>";
						echo "</tr>";
						
						echo "<tr>";
							echo "<td align=center>";
							echo "<input lang=send type=checkbox onkeypress='return noenter()' id='assistance' name='assistance' value=1>";
							echo "</td>";
							
							echo "<td>";
							echo "Assistance";
							echo "</td>";
						echo "</tr>";
							
						echo "<script type='text/javascript'>Calendar.setup({inputField	 : 'enddate',baseField    : 'element_2',button: 'enddate',ifFormat: '%Y %e, %D',onSelect: selectDate});	</script>";

						// ********************************** Xfields echoing *****************************
						if($nxfields > 0){
							echo "<tr><td colspan=2><hr></td></tr>";
							
							// xfieldsinputtype: 1 = input, 2 = singlepickcheckbox, 3 = multipickcheckbox
							$formerName;
							for ($i=0;$i<$nxfields;$i++){
								$arrxfields= dbHelp::fetchRowByName($res);
								$extraHtml = '';
								echo "<tr>";
									if($arrxfields['xfields_type'] == 1){
										if ($calendar->getEntry()!=0)
											$extraHtml = "value='" . $arrxfields['xfieldsval_value']. "'";
										echo "<td colspan=2>";
										echo $arrxfields['xfields_label'];
										echo "<br><input lang=send onkeypress='return noenter()' class=inpbox  id='".$arrxfields['xfields_name']."-".$arrxfields['xfields_id']."' name='".$arrxfields['xfields_label']."' ".$extraHtml.">";
										echo "</td>";
									}
									elseif($arrxfields['xfields_type'] == 4){// input numeric
										if ($calendar->getEntry()!=0)
											$extraHtml = "value='" . $arrxfields['xfieldsval_value']. "'";
										echo "<td colspan=2>";
										echo $arrxfields['xfields_label'];
										echo "<br><input lang=send onkeypress='return noenter()' class=numericXfield  id='".$arrxfields['xfields_name']."-".$arrxfields['xfields_id']."' name='".$arrxfields['xfields_label']."' ".$extraHtml.">";
										echo "</td>";
									}
									elseif($arrxfields['xfields_type'] == 5){// input that wont give an error if its empty
										if ($calendar->getEntry()!=0)
											$extraHtml = "value='" . $arrxfields['xfieldsval_value']. "'";
										echo "<td colspan=2>";
										echo $arrxfields['xfields_label'];
										echo "<br><input lang=send onkeypress='return noenter()' class='emptyAllowedText'  id='".$arrxfields['xfields_name']."-".$arrxfields['xfields_id']."' name='".$arrxfields['xfields_label']."' ".$extraHtml.">";
										echo "</td>";
									}
									else if($arrxfields['xfields_type'] == 2 || $arrxfields['xfields_type'] == 3){
										if($formerName != $arrxfields['xfields_name']){
											echo "<tr><td colspan=2>".$arrxfields['xfields_name']."</td></tr>";
											echo "<tr><td colspan=2>";
												echo "<table id='".$arrxfields['xfields_name']."'>";
												echo "</table>";
											echo "</td></tr>";
										}
										
										$extraHtml = '';
										if ($calendar->getEntry()!=0 && $arrxfields['xfieldsval_value']=='true'){
											$extraHtml = 'checked';
										}
											
											//****
										echo "<script type='text/javascript'>";
										echo "addRadioOrCheck('".$arrxfields['xfields_name']."','".$arrxfields['xfields_id']."','".$arrxfields['xfields_label']."','".$arrxfields['xfields_type']."', '".$extraHtml."');";
										echo "</script>";
											//****
									
									}
									// $formerInputType = $arrxfields['xfields_type'];
									$formerName = $arrxfields['xfields_name'];
								echo "</tr>";
							}
						}
						// ********************************* /Xfields echoing *****************************

						// Wont be dislayed if theres an active user session
						$display = 'table-cell';
						// if(isset($_SESSION['user_name']) && $_SESSION['user_name']!='') // wtf was i thinking??
						if(isset($_SESSION['user_id']) && $_SESSION['user_id']!='')
							$display = 'none';
							
						echo "<tr><td colspan=2 style='display:".$display."'><hr></td></tr>";

						echo "<tr>";
							echo "<td colspan=2 style='display:".$display."'>User Name<br>";
							echo "<input name=user_id class=inpbox onkeypress='return noenter()' id=user_id lang=send title='' value='' onblur=ajaxUser(this) />";
							echo "</td>";
						echo "</tr>";
						
						echo "<tr>";
							echo "<td colspan=2 style='display:".$display."'>";
							echo "Password<br><input class=inpbox onkeypress='return noenter()' lang=send type=password id=user_passwd name=user_passwd value='' />";
							echo "</td>";
						echo "</tr>";

						echo "<tr><td colspan=2><hr></td></tr>";
						
						$restatus6EditButton = "";
						if($imResstatus6){
							require_once("../agendo/itemHandling.php");
							if($calendar->isResp()){
								$buttonDisplay2 = 'none';
								$restatus6EditButton = "<input type=button disabled id='editItemsButton' class='bu' title='Allows the editing of items in the entry' value='Edit' onclick=\"editEntryItems(".$resource.");\" />";
								$confirmButtonOnclick = "done();";
							}
							// elseif(hasPermission($_SESSION['user_id'], $resource)){
							else{
								$tdButtonsDisplay = 'none';
								$buttonDisplay1 = $buttonDisplay2 = $buttonDisplay3 = $buttonDisplay4 = $buttonDisplay5 = 'none';
								echo "<tr>";
									echo "<td colspan=2 style='text-align:center;'>";
										echo "<input type='button' id='addItemButton' class='bu' title='Press to add/remove items' value='Add/Remove items' onclick='itemInsertShowDivAndCheckUser(".$resource.", \"itemInsertHtml\");' />";
									echo "</td>";
								echo "</tr>";
								
								echo "<tr><td colspan=2><hr></td></tr>";
							}
						}
					}
					else{ // Used to hide buttons
						$tdButtonsDisplay = 'none';
						$buttonDisplay1 = $buttonDisplay2 = $buttonDisplay3 = $buttonDisplay4 = $buttonDisplay5 = 'none';
					}
					//*********************
					echo "<tr style='display:".$tdButtonsDisplay.";' ><td colspan=2 align='center'>";
						echo "<input type=button style='width:40px;display:".$buttonDisplay1.";' onkeypress='return noenter()' id=delButton class=bu title='Deletes the selected entry' value='Del' onclick=\"ManageEntries('del');\" />";
						// This is rather horrible old chap, is there a better way to do this without rewritting the whole weekview.php?
						echo $restatus6EditButton;
						echo "<input type=button style='width:60px;display:".$buttonDisplay2.";' onkeypress='return noenter()' id=monitorButton class=bu title='Puts the user on a waiting list for the selected entry' value='WaitList' onclick=\"ManageEntries('monitor');\" />";
						echo "<input type=button style='width:40px;display:".$buttonDisplay3.";' onkeypress='return noenter()' id=addButton class=bu value='Add' onclick=\"ManageEntries('add','" . $calendar->getStartTime(). "','" . cal::getResolution()/60 . "');\" /><br>";
						echo "<input type=button style='width:70px;display:".$buttonDisplay4.";' onkeypress='return noenter()' id=updateButton  class=bu value='Update' onclick=\"ManageEntries('update','" . $calendar->getStartTime(). "','" . cal::getResolution()/60 . "');\" />";
						echo "<input type=button style='width:70px;display:".$buttonDisplay5.";' onkeypress='return noenter()' id=confirmButton class=bu value='Confirm' onclick=\"".$confirmButtonOnclick."\" />";
						// echo "<input type=button style='width:70px;display:".$buttonDisplay5.";' onkeypress='return noenter()' id=confirmButton class=bu value='Confirm' onclick=\"ManageEntries('confirm');\" />";
					echo "</td></tr>";

					echo "<tr style='display:".$tdButtonsDisplay.";'><td colspan=2;><hr></td></tr>";
				echo "</table>";
				
				echo "<input name=action lang=send style='visibility:hidden;font-size:0px' value='' id=action>";
				//echo "<input name=maxslots  style='visibility:hidden;font-size:0px' value='' id=maxslots>";
				echo "<input name=code lang=send style='visibility:hidden;font-size:0px' value='' id=code>";
				echo "<input name=resource lang=send style='visibility:hidden;font-size:0px' value=". $calendar->getResource() . " id=resource>";
				echo "<input name=entry lang=send style='visibility:hidden;font-size:0px' value=". $calendar->getEntry(). " id=entry>";
				echo "<input name=update lang=send style='visibility:hidden;font-size:0px' value=". $update. " id=update>";
				echo "<input name=tdate style='visibility:hidden;font-size:0px' value=". $calendar->getStartDate() . " id=tdate>";
				echo "</form>";
				//GET announcements

					echo "<table align=left style='width:100%;padding:2px;'>";
						// *************************************************************************
						// Checks if the user logged is the one responsible for the current resource
						if($calendar->isResp() && !$imResstatus5 && !$imResstatus6){
							echo "<tr>";
								echo "<td colspan=2>";
								echo "<label>Perform action as:</label>";
								echo "</td>";
							echo "</tr>";
						
							echo "<tr>";
								echo "<td>";
								echo "<input type='checkbox' id='impersonateCheck' onchange='impersonateCheckChange()' />";
								echo "</td>";
								
								echo "<td>";
								echo "<input type='text' id='usersList' onclick='impersonateCheckChange(true)'/>";
								echo "</td>";
							echo "</tr>";
							
							echo "<tr><td colspan=2><hr></td></tr>";
						}
						// *************************************************************************

						
						echo "<tr>";
							echo "<td colspan=2><b>Announcements</b></td>";
						echo "</tr>";
						
						$message=new genMsg;
						echo "<tr>";
							echo "<td colspan=2>";
							$message->announcement($resource);
							echo "</td>";
						echo"</tr>";
					echo "</table>";
				
			echo "</div>";

		echo "</td>";
			// *********************************************************    end of entry div stuff here   ***************************************************************************

			// ***************************** draw calendar *************************
			echo "<td style='height:100%;vertical-align:top;'>";
			echo "<div id='calendar' class='calendar'> ";
				// calling method for design weekview
				echo $calendar->draw_week();
			echo "</div>";
			echo "</td>";
			// *************************** end draw calendar ************************
			
		echo "</tr>";
echo "</table>";

//Do we have publicity in this page??
require_once "../datumo/pub.php";
$pub=new pubHandler($resource);
pageViews($resource);

echo "</div>";


// for displaying user info
echo "<div id=DisplayUserInfo style='display:none;position:absolute;z-index:99;padding:3px;'></div>";
$sql = "select xfields_type, xfields_label, xfields_name, xfields_id from xfields where xfields_placement = 2 and xfields_resource = ".$resource;
$res = dbHelp::query($sql);
//for displaying user confirmation comments
echo "<div id='InputComments' style='display:none;position:absolute;border-style:solid;border-width:1px;background-color: white;z-index:99;padding:3px;text-align:center;'>";
	if(dbHelp::numberOfRows($res) > 0){
		echo "<form id='confirmXfields'>";
		while($arr = dbHelp::fetchRowByName($res)){
			if($arr['xfields_type'] == '1'){// input text
				echo "<label>".$arr['xfields_label']."</label>";
				echo "<br><input lang=send onkeypress='return noenter()' class=inpbox  id='".$arr['xfields_name']."-".$arr['xfields_id']."' name='".$arr['xfields_label']."'><br>";
			}
			elseif($arr['xfields_type'] == '4'){// input numeric
				echo "<label>".$arr['xfields_label']."</label>";
				echo "<br><input lang=send onkeypress='return noenter()' class=numericXfield  id='".$arr['xfields_name']."-".$arr['xfields_id']."' name='".$arr['xfields_label']."'><br>";
			}
			elseif($arr['xfields_type'] == '2' || $arr['xfields_type'] == '3'){// radio or checkbox
				if($formerName != $arr['xfields_name']){
					echo "<label>".$arr['xfields_name']."</label><br>";
					echo "<table id='".$arr['xfields_name']."'>";
					echo "</table>";
				}
				// ****
				echo "<script type='text/javascript'>";
				echo "addRadioOrCheck('".$arr['xfields_name']."','".$arr['xfields_id']."','".$arr['xfields_label']."','".$arr['xfields_type']."');";
				echo "</script>";
				// ****
			}
			$formerName = $arr['xfields_name'];
			echo "&nbsp";
		}
		echo "</form>";
	}
	
    echo "<form name='entrycomments' id='entrycomments'>";
		echo "<textarea name=txtcomments id=txtcomments rows=3 cols=25></textarea>";
    echo "</form>";
	
	echo "<a href='javascript:addcomments(0)' title='Confirm and leave the comment written above'>Comment and confirm</a>&nbsp;&nbsp;&nbsp;";
	echo "<a href='javascript:addcomments()' title='Confirm without leaving a comment'>Confirm</a>&nbsp;&nbsp;&nbsp;";
	echo "<a onclick='document.getElementById(\"InputComments\").style.display=\"none\";document.getElementById(\"txtcomments\").value=\"\";'>Cancel</a>";
echo "</div>";

echo "</body>";
echo "</html>";


	// Returns just the calendar to JS to avoid the auto refresh header
	function getCalendarWeek(){
		try{
			global $calendarDate;
			global $resource;
			$calendar=new cal($resource);
			$calendar->setStartDate(date("Ymd",strtotime($calendarDate)));
			if(isset($_POST['entry']) && isset($_POST['action'])){
				$calendar->setEntry($_POST['entry']);
				if($_POST['action'] == 'all'){
					$sql = "select entry_repeat from entry where entry_id = :0";
					$prep = dbHelp::query($sql, array($_POST['entry']));
					$row = dbHelp::fetchRowByIndex($prep);
					$calendar->setCalRepeat($row[0]);
				}
				elseif($_POST['action'] == 'update'){
					$calendar->setCalUpdate($_POST['entry']);
				}
			}
			
			$json->calendar = $calendar->draw_week();
			$json->success = true;
		}
		catch(Exception $e){
			$json->success = false;
			$json->message = "Error: ".$e->getMessage();
		}
		echo json_encode($json);
	}

	// returns the amount of time and slots left for a user for a resource
	function getTimeAndSlotsLeft(){
		global $calendarDate;
		global $resource;
		
		if(isset($_SESSION['user_id']) && isResp($_SESSION['user_id'], $resource) === false){
			$day=substr($calendarDate,6,2);
			$month=substr($calendarDate,4,2);
			$year=substr($calendarDate,0,4);
			$slotDate = date('Ymd', mktime(0, 0, 0, $month, (int)$day + 1, $year));
			$day=substr($slotDate,6,2);
			$month=substr($slotDate,4,2);
			$year=substr($slotDate,0,4);
			
			$arrSRM = getSlotsResolutionMaxHours($day, $month, $year, $_SESSION['user_id'], $resource);
			$totalSlots = $arrSRM[0];
			$resolution = $arrSRM[1];
			$maxHours 	= $arrSRM[2];

			// if($arrSRM[3] != $user_id && $maxHours != 0){
			if($maxHours != 0){
				$timeUsed = $totalSlots * $resolution / 60;
				$maxSlots = $maxHours * 60 / $resolution;
				$slotsLeft = $maxSlots - $totalSlots;
				
				if($slotsLeft < 0){
					$slotsLeft = 0;
				}
				
				$timeLeft = $maxHours - $timeUsed;
				if($timeLeft < 0){
					$timeLeft = 0;
				}
				
				$timeSlotsText = "You have ".$timeLeft." hours (".$slotsLeft." entries) left to book";
				if(isAjax()){
					$json->timeSlotsText = $timeSlotsText;
					echo json_encode($json);
				}
				return $timeSlotsText;
			}
		}
		
		return false;
	}
?>

