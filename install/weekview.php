<?php
// This file was altered by Pedro Pires (The chosen two)
	session_start();
	$pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
	$_SESSION['path'] = "../".$pathOfIndex[sizeof($pathOfIndex)-1];
	require_once("../agendo/commonCode.php");
	require_once("../agendo/calClass.php");
	require_once("../agendo/functions.php");
	require_once("../agendo/genMsg.php");

	if(isset($_POST['functionName'])){
		call_user_func($_POST['functionName']);
		exit;
	}
	// logIn($resource, $user_id, $pwd, $logOff);
	// logIn();
	importJs();
	initSession();
?>

<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" /> 
<META HTTP-EQUIV="REFRESH" CONTENT="180">
<meta name="keywords" content="" />
<meta name="description" content="" /> 
<link href="../agendo/css/style.css" rel="stylesheet" type="text/css" />
<link href="../agendo/css/CalendarControl.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../agendo/js/cal.js"></script>
<script type="text/javascript" src="../agendo/js/ajax.js"></script>
<script type="text/javascript" src="../agendo/js/datefunc.js"></script>
<script type="text/javascript" src="../agendo/js/overdiv.js"></script>
<script type="text/javascript" src="../agendo/js/weekview.js"></script>
<script type="text/javascript" src="../agendo/js/commonCode.js"></script>


<?php
//********************************************
$smallScript = "<script type='text/javascript'>alert('Please sign in to be able to access resources.');</script>"."<meta HTTP-EQUIV='REFRESH' content='0; url=./'>";
if(!secureIpSessionLogin()){
	echo $smallScript;
	exit;
}
//********************************************

//disable warning displays
error_reporting(0);
$resource=clean_input($_GET['resource']);
// $resource=$_GET['resource'];

// Used to hide buttons, and show or not, a custom interface
$ressql = "select resource_status from resource where resource_id = ".$resource;
$res = dbHelp::mysql_query2($ressql) or die($ressql);
$arr = dbHelp::mysql_fetch_row2($res);
$imResstatus5 = false;
if($arr[0] == 5){
	$imResstatus5 = true;
	// if (isset($_COOKIE["resourceStatus"]) && $_COOKIE["resourceStatus"] == 5){
		// $ressql = "select resinterface_phpfile from resinterface where resinterface_resource = ".$resource;
		// $res = dbHelp::mysql_query2($ressql) or die($ressql);
		// $arr = dbHelp::mysql_fetch_row2($res);
		// echo "<meta HTTP-EQUIV='REFRESH' content='0; url=".$arr[0]."?resource=".$resource."'>";
		// exit;
	// }
}
// ***********************************************************

if (isset($_GET['update'])) {$update=clean_input($_GET['update']);$entry=clean_input($_GET['update']);} else {$update=0;} ;
//instatiation for calendar
$calendar=new cal($resource,$update);
$message=new genMsg;

//getting the variables 
if (isset($_GET['action'])) {$action =$_GET['action'];} else {$action =0;} ;
if (isset($_GET['msg'])) {$msg =$_GET['msg'];} else {$msg ='';} ;
 
//html body starts Here
//##############################################
echo "<body onload=init(" . $calendar->getStatus() . "," . $calendar->getMaxSlots() . ")>";


// for displaying user info
echo "<div id=DisplayUserInfo style='display:none;position:absolute;border-style:solid;border-width:1px;background-color: white;z-index:99;padding:3px;'></div>";
//for displaying user confirmation comments
echo "<div id=InputComments style='display:none;position:absolute;border-style:solid;border-width:1px;background-color: white;z-index:99;padding:3px;'>";
    echo "<form name=entrycomments id=entrycomments>";
    echo "<textarea name=txtcomments id=txtcomments rows=3 cols=25></textarea>";
    echo "</form>";
    echo "<center><a href=\"javascript:addcomments(0)\">add comment</a>&nbsp;&nbsp;&nbsp;";
    echo "<a href=\"javascript:addcomments()\">everything ok!</a>";
echo "</div>";
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
		echo "<td bgcolor=". cal::RegCellColorOff . ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>Confirmed</td>";
		echo "<td bgcolor=". cal::PreCellColorOff . ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>To be confirmed</td>";
		echo "<td bgcolor=". cal::ErrCellColorOff . ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>Unconfirmed</td>";
		echo "<td bgcolor=". cal::MonCellColorOff . ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>Monitored</td>";
		echo "<td bgcolor=". cal::InUseCellColorOff . ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>In use</td>";
	echo "</tr>";
echo "</table>";

if($calendar->monitor($calendar->getResourceName())){
	echo "<hr><p style='text-align:center'><a href=../agendo/monitor.php target='blank'>Monitored resource</a>";
}
if ($calendar->getLink()!='') echo "<hr><p style='text-align:center'>More info <a href='" . $calendar->getLink() . "' target=_blank>here</a>";
echo "<p style='text-align:right'><a href=# onclick=\"javascript:d=document.getElementById('help');d.style.display ='none'\">close</a>";
echo "</div>";

//for displaying calendar
// $sql = "SELECT mainconfig_institute, mainconfig_shortname, mainconfig_url FROM mainconfig WHERE mainconfig_id = 1";
// $res = dbHelp::mysql_query2($sql);
// $institute = dbHelp::mysql_fetch_row2($res);
$sql = "SELECT configParams_name, configParams_value from configParams where configParams_name='institute' or configParams_name='shortname' or configParams_name='url'";
$res = dbHelp::mysql_query2($sql);
for($i=0; $arr = dbHelp::mysql_fetch_row2($res); $i++){
	$institute[$i] = $arr[1];
}

//This is where msgs are displayed (changed)
echo "<div id='msg' style=\"top:200;left:178px;width:574px;height:50;filter:alpha(opacity=0);line-height:50px;\" class=msg >";
	echo $msg;
echo "</div>";
echo "<div style='margin:auto;width:1024px;height:100%;height:100%;'>";
echo "<title>".strtoupper($institute[1])." Reservations</title>";
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
							echo "<h1><a href=index.php>".strtoupper($institute[1])." Reservations</a></h1>";
							echo "</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td style='padding-left:10px'>";
							echo "<h3><a href='http://".$institute[2]."'>".$institute[0]."</a></h3>";
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

			// User/management div
			echoUserDiv("weekview", $calendar->getResource());
		echo "</td>";
	echo "</tr>";
			
// *******************************************        calendar stuff made here     ***********************************************************
			if (isset($_GET['date'])){
				$calendar->setStartDate($_GET['date']);
			} else {        
				$calendar->setStartDate(date("Ymd",mktime(0,0,0, date("m"), date("d")-date("N"),date("Y"))));
			}
		   if ($calendar->getStatus()==0 or $calendar->getStatus()==2) { //inactive or invisible
				echo "<tr>";
					echo "<td>";
					echo "<h1 style='color:#cc8888'>".$calendar->getResourceName()." not available for reservations</h1>";
					$sql ="SELECT user_firstname,user_lastname,user_email from ".dbHelp::getSchemaName().".user,resource where user_id=resource_resp";
					$res = dbHelp::mysql_query2($sql);
					$arr = dbHelp::mysql_fetch_array2($res);
					echo "<h2>Please contact ".$arr['user_firstname']." ".$arr['user_lastname']."</h2>";
					echo "<a href=weekview.php?resource=" . ($calendar->getResource() -1) . "&date=" . $calendar->getStartDate() . "><img border=0 src=pics/resminus.png></a>";
					echo "<a href=weekview.php?resource=" . ($calendar->getResource() +1) . "&date=" . $calendar->getStartDate() . "><img border=0 src=pics/resplus.png></a>";
					echo "</td>";
				echo "</tr>";
				exit;
			}

			if (isset($_POST['action'])) call_user_func($_POST['action']);
			if (isset($_GET['entry'])){
				$entry=clean_input($_GET['entry']);
				$calendar->setEntry($entry);
				$sql ="SELECT xfields_name, xfieldsval_value, xfields_label, xfields_type, xfields_id from xfields, xfieldsval, xfieldsinputtype where xfieldsval_field=xfields_id and xfields_resource=" . $calendar->getResource(). " and xfieldsval_entry=".$calendar->getEntry()." group by xfields_id, xfields_type";
				
				$sqlWeekDay = "select ".dbHelp::date_sub(dbHelp::getFromDate('entry_datetime','%Y%m%d'),'1','day')." from entry where entry_id=".$calendar->getEntry();
				$res = dbHelp::mysql_query2($sqlWeekDay);
				$arr1 = dbHelp::mysql_fetch_row2($res);
				$sqlWeekNumber = "select ".dbHelp::getFromDate("'".$arr1[0]."'",'%w');
				$res = dbHelp::mysql_query2($sqlWeekNumber);
				$arr2 = dbHelp::mysql_fetch_row2($res);
				$sqle="select entry_user, entry_repeat, ".dbHelp::getFromDate(dbHelp::date_sub("'".$arr1[0]."'",$arr2[0],'day'),'%Y%m%d')." as date, ".dbHelp::getFromDate('entry_datetime','%h')." as dateHour, ".dbHelp::getFromDate('entry_datetime','%i')." as dateMinutes, entry_slots from entry where entry_id=".$calendar->getEntry();
				
				$rese=dbHelp::mysql_query2($sqle);
				$arre= dbHelp::mysql_fetch_array2($rese);
				$calendar->setStartDate($arre['date']);
				$calendar->setCalRepeat($arre['entry_repeat']);
				$user=$arre['entry_user'];
				$nslots=$arre['entry_slots'];
				// $entrystart=$arre['starttime'];
				$entrystart= $arre['dateHour'] + $arre['dateMinutes']/60;
				
			} elseif ($update!=0) {
				$calendar->setEntry($update);
				$sql ="SELECT xfields_name, xfieldsval_value, xfields_label, xfields_type, xfields_id from xfields, xfieldsval, xfieldsinputtype where xfieldsval_field=xfields_id and xfields_resource=" . $calendar->getResource(). " and xfieldsval_entry=".$calendar->getEntry()." group by xfields_id, xfields_type";
				
				// $sqle="select entry_user, entry_repeat, @d:= date_format(date_sub(entry_datetime,interval 1 day),'%Y%m%d'),  @wd:=date_format(@d,'%w'), date_format(date_sub(@d, interval @wd day),'%Y%m%d') as date, date_format(entry_datetime,'%h') + date_format(entry_datetime,'%i')/60 as starttime, entry_slots from entry where entry_id=".$calendar->getEntry();
				$sqlWeekDay = "select ".dbHelp::date_sub(dbHelp::getFromDate('entry_datetime','%Y%m%d'),'1','day')." from entry where entry_id=".$calendar->getEntry();
				$res = dbHelp::mysql_query2($sqlWeekDay);
				$arr1 = dbHelp::mysql_fetch_row2($res);
				
				$sqlWeekNumber = "select ".dbHelp::getFromDate("'".$arr1[0]."'",'%w');
				$res = dbHelp::mysql_query2($sqlWeekNumber);
				$arr2 = dbHelp::mysql_fetch_row2($res);
				
				$sqle="select entry_user, entry_repeat, ".dbHelp::getFromDate(dbHelp::date_sub("'".$arr1[0]."'",$arr2[0],'day'),'%Y%m%d')." as date, ".dbHelp::getFromDate('entry_datetime','%h')." as dateHour, ".dbHelp::getFromDate('entry_datetime','%i')." as dateMinutes, entry_slots from entry where entry_id=".$calendar->getEntry();
				$rese=dbHelp::mysql_query2($sqle);
				$arre= dbHelp::mysql_fetch_array2($rese);
				
				$calendar->setStartDate($arre['date']);
				$calendar->setCalRepeat($arre['entry_repeat']);
				$user=$arre['entry_user'];
				$nslots=$arre['entry_slots'];
				$entrystart=$arre['starttime'];
				$entrystart= $arre['dateHour'] + $arre['dateMinutes']/60;
			} else {
					$calendar->setEntry(0);
					$entrystart=0;
					$nslots=1;
					$user='';
					$sql ="SELECT xfields_name, xfields_label, xfields_type, xfields_id from xfields, xfieldsinputtype where xfields_resource=". $calendar->getResource()." group by xfields_id, xfields_type";
			}
// **************************************************    end of calendar stuff being done      **********************************************************
			
			
	echo "<tr>";
		// ************************************************************    entry div stuff here   ***************************************************************************
		echo "<td style='vertical-align:top;height:100%;'>";
		
			$res=dbHelp::mysql_query2($sql);
			$nxfields=dbHelp::mysql_numrows2($res);

			echo "<div id=entrydiv class=entrydiv>";
				echo "<form name=entrymanage id=entrymanage >";
				// echo "<tr><td colspan=3>";
				echo "<table id=entryinner class=entryinner align=center>";
					echo "<tr>";
						echo "<td colspan=2>";
						echo "<table style='width:160px;padding:0px;' align=center>";
							echo "<tr>";
								echo "<td>";
								$sql="select resource_status,resource_id from resource where resource_status not in (0,2) order by resource_name";
								$resResources=dbHelp::mysql_query2($sql);
								$nextDetected = false;
								$currentDetected = false;
								// Loop that finds the previous and next "line" composed of resourceStatus and resourceId of the $calendar->getResource() id
								while($resArray = dbHelp::mysql_fetch_row2($resResources)){
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
									else
										$currentDetected = true;
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
						echo "</td>";
					echo "</tr>";
						
					echo "<tr>";
						echo "<td colspan=2>";
						echo "<hr>";
						echo "</td>";
					echo "</tr>";

					//***********************************************
					//************* Weekly hours left ***************
					
					if(isset($_SESSION['user_id'])){
						$day=substr($calendar->getStartDate(),6,2);
						$month=substr($calendar->getStartDate(),4,2);
						$year=substr($calendar->getStartDate(),0,4);
						$slotDate = date('Ymd', mktime(0, 0, 0, $month, (int)$day + 1, $year));
						$day=substr($slotDate,6,2);
						$month=substr($slotDate,4,2);
						$year=substr($slotDate,0,4);
						
						$arrSRM = getSlotsResolutionMaxHours($day, $month, $year, $_SESSION['user_id'], $calendar->getResource());

						if($arrSRM[3] != $user_id){
							$totalSlots = $arrSRM[0];
							$resolution = $arrSRM[1];
							$maxHours 	= $arrSRM[2];
							
							$timeUsed = $totalSlots * $resolution/60;
							$maxSlots = $maxHours * 60 / $resolution;
							
							echo "<tr>";
								echo "<td colspan=2 align='center'>";
								echo "You have ".($maxHours - $timeUsed)." hours (".($maxSlots - $totalSlots)." entries) left to book";
								echo "</td>";
							echo "</tr>";
							
							echo "<tr>";
								echo "<td colspan=2>";
								echo "<hr>";
								echo "</td>";
							echo "</tr>";
						}
					}
					
					//************* Weekly hours left ***************
					//***********************************************
	
					if($calendar->monitor($calendar->getResourceName())){
						echo "<tr><td colspan=2 align=center><a href=ekrano target='blank'>Monitored resource</a></td></tr>";
						echo "<tr><td colspan=2><hr></td></tr>";
					}
					

					// similar stuff
					$sqlSimilar = "select similarresources_similar, resource_name from resource, (select similarresources_similar from similarresources where similarresources_resource = ".$resource.") as similars where resource_id = similarresources_similar";
					$resSimilar = dbHelp::mysql_query2($sqlSimilar);
					if(dbHelp::mysql_numrows2($resSimilar)>0){
						echo "<tr>";
							echo "<td colspan=2>Similar Resources</td>";
						echo "</tr>";
					
						echo "<tr>";
							echo "<td colspan=2>";
							echo "<select id='similarResources' class='similar'>";
							while($arrSimilar = dbHelp::mysql_fetch_row2($resSimilar)){
								echo "<option value='".$arrSimilar[0]."' onclick='similarResources(this.value)'>".$arrSimilar[1]."</option>";
							}
							echo "</select>";
							echo "</td>";
						echo "</tr>";

						echo "<tr>";
							echo "<td colspan=2>";
							echo "<hr>";
							echo "</td>";
						echo "</tr>";
					}
					// /similar stuff
					
				
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
						if($nxfields > 0)
							echo "<tr><td colspan=2><hr></td></tr>";
						
						// xfieldsinputtype: 1 = input, 2 = singlepickcheckbox, 3 = multipickcheckbox
						$formerName;
						for ($i=0;$i<$nxfields;$i++){
							$arrxfields= dbHelp::mysql_fetch_array2($res);
							$extraHtml = '';
							echo "<tr>";
								if($arrxfields['xfields_type'] == 1){
									if ($calendar->getEntry()!=0)
										$extraHtml = "value='" . $arrxfields['xfieldsval_value']. "'";
									echo "<td colspan=2>";
									echo $arrxfields['xfields_label'];
									echo "<br><input lang=send onkeypress='return noenter()' class=inpbox  id='".$arrxfields['xfields_name'].$arrxfields['xfields_id']."' name='".$arrxfields['xfields_label']."' ".$extraHtml.">";
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
									if ($calendar->getEntry()!=0 && $arrxfields['xfieldsval_value']=='true')
										$extraHtml = 'checked';
										
										//****
									echo "<script type='text/javascript'>";
									echo "addRadioOrCheck('".$arrxfields['xfields_name']."','".$arrxfields['xfields_id']."','".$arrxfields['xfields_label']."','".$arrxfields['xfields_type']."');";
									echo "</script>";
										//****
								
								}
								// $formerInputType = $arrxfields['xfields_type'];
								$formerName = $arrxfields['xfields_name'];
							echo "</tr>";
						}
						// ********************************* /Xfields echoing *****************************

						// Wont be dislayed if theres an active user session
						$display = 'table-cell';
						if(isset($_SESSION['user_name']) && $_SESSION['user_name']!='')
							$display = 'none';
							
						echo "<tr><td colspan=2 style='display:".$display."'><hr></td></tr>";

						echo "<tr>";
							echo "<td colspan=2 style='display:".$display."'>User Name<br>";
							echo "<input name=user_id class=inpbox onkeypress='return noenter()' id=user_id lang=send title='' value=''  onblur=ajaxUser(this) />";
							echo "</td>";
						echo "</tr>";
						
						echo "<tr>";
							echo "<td colspan=2 style='display:".$display."'>";
							echo "Password<br><input class=inpbox onkeypress='return noenter()' lang=send type=password id=user_passwd name=user_passwd value='' />";
							echo "</td>";
						echo "</tr>";

						echo "<tr><td colspan=2><hr></td></tr>";

						echo "<tr><td colspan=2 align=justify>";
							echo "<input type=button style='width:40px' onkeypress='return noenter()' id=delButton class=bu title='Deletes the selected entry' value='Del' onClick=\"ManageEntries('del');\">";
							echo "<input type=button style='width:60px' onkeypress='return noenter()' id=monitorButton class=bu title='Puts the user on a waiting list for the selected entry' value='WaitList' onClick=\"ManageEntries('monitor');\">";
							echo "<input type=button style='width:40px' onkeypress='return noenter()' id=addButton class=bu value='Add' onClick=\"ManageEntries('add','" . $calendar->getStartTime(). "','" . cal::getResolution()/60 . "');\"><br>";
							echo "<input type=button style='width:70px' onkeypress='return noenter()' id=updateButton  class=bu value='Update' onClick=\"ManageEntries('update','" . $calendar->getStartTime(). "','" . cal::getResolution()/60 . "');\">";
							echo "<input type=button style='width:70px' onkeypress='return noenter()' id=confirmButton class=bu value='Confirm' onClick=\"ManageEntries('confirm');\">";
						echo "</td></tr>";
						
						echo "<tr><td colspan=2><hr></td></tr>";

					// Used to hide buttons
					}
					//*********************
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

					echo "<table border=0 align=left>";
						echo "<tr>";
							echo "<td><b>Announcements</b></td>";
						echo "</tr>";
						
						echo "<tr>";
							echo "<td>";
							$message->announcement($resource);
							echo "</td>";
						echo"</tr>";
					echo "</table>";
				
			echo "</div>";

		echo "</td>";
			// *********************************************************    end of entry div stuff here   ***************************************************************************

			// ***************************** draw calendar *************************
			echo "<td style='height:100%;vertical-align:top;'>";
			echo "<div id=calendar class='calendar'> ";
				// calling method for design weekview
				$calendar->draw_week();
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
echo "</body></html>";


?>

