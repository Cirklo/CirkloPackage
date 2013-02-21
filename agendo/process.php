<?php
	// This class was altered by Pedro Pires (The chosen two)
	require_once("commonCode.php");
	// initSession(); // dont think its necessary here, only on the weekview and index page
	require_once("permClass.php");
	require_once("alertClass.php");
	require_once("functions.php");


    /**
    * @author Nuno Moreno
    * @copyright 2009-2010 Nuno Moreno
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract: script for dealing with ajax weekview requests. It sets the entries into different states
    * 1-> regular, 2-> pre-reserve, 3->deleted, 4->Monitored
    */
    
	$action=$_GET['action'];
	call_user_func($action);

	//adding function. set the state to 1 or 2 depending on equipment state
	function add(){
		$w=0;
		$update = (int)cleanValue($_GET['update']);
		if ($update>0) {update();exit;}
		
		$assistance=$_GET['assistance'];
		$code=$_GET['code'];
		$repeat=cleanValue($_GET['repeat']);
		$enddate=cleanValue($_GET['enddate']);
		$enddate=substr($enddate,0,4) . substr($enddate,5,2) . substr($enddate,8,2);
		$datetime=cleanValue($_GET['datetime']);
		$min=substr($datetime,10,2);
		$hour=substr($datetime,8,2);
		$slots=(int)cleanValue($_GET['slots']);
		$assistance = ($assistance == "true") ? "1" : "0";

		$user_id=getUserId();
		$user_passwd=getPass();
		$resource = (int)cleanValue($_GET['resource']);
		
		//201102251200
		$year=substr($datetime,0,4);
		$month=substr($datetime,4,2);
		$day=substr($datetime,6,2);
		//****************
		
		$arr = getSlotsResolutionMaxHours($day, $month, $year, $user_id, $resource);
		
		// takes the current number of slots and returns the ones not in a happyhour usage
		if(($slotsNotInHH = filterHappyHourSlotsOut($hour, $min, $day, $month, $year, $slots, $resource, $arr[1])) === false){
			$slotsNotInHH = $slots;
		}
		
		// $arr[0] = sum(entry_slots), $arr[1] = resource_resolution, $arr[2] = resource_maxhoursweek, $arr[3] = resource_resp 
		if($arr[3] != $user_id && $arr[2] != 0){
			// $totalTime = ($arr[0] + $slots) * $arr[1] / 60;
			$totalTime = ($arr[0] + $slotsNotInHH) * $arr[1] / 60;
			$timeUsed = $arr[0] * $arr[1] / 60;
			// check if the number of slots*resolution is bigger then the resource_maxhoursweek
			// if so, return error and exit
			if($totalTime > $arr[2]){
				throw new Exception("You cannot book more then ".$arr[2]." hours per week, you have ".($arr[2] - $timeUsed)." hours left.");
				exit;
			}
		}
		//****************
		
		$perm = new permClass;
		if (!$perm->setPermission($user_id,$resource,$user_passwd)){
			throw new Exception($perm->getWarning());
		}
		if (!$perm->addRegular()){
			throw new Exception($perm->getWarning());
		}
		if (!$perm->addAhead($datetime, $slots)){
			throw new Exception($perm->getWarning());
		}
		if (!$perm->addBack($datetime)){
			throw new Exception("Not allowed to add an entry for this hour");
		}
		
		$EntryStatus = $perm->getEntryStatus();
		if (!$perm->getEntryStatus()){
			throw new Exception($perm->getWarning());
		}
		
		//if there is no associated entries it creates a new set
		$sql="select repetition_id, repetition_code from repetition where repetition_code= :0";
		$res=dbHelp::query($sql, array($code));
		//if there is no related entry already it creates one
		if (dbHelp::numberOfRows($res)==0) {    
			$sql = "insert into repetition(repetition_code) values(:0)";
			dbHelp::query($sql, array($code));
		}

		//getting the entry code
		$sql="select repetition_id from repetition where repetition_code=:0";
		$res=dbHelp::query($sql, array($code));
		$arrrep=dbHelp::fetchRowByIndex($res);
		$weekahead=$datetime;
		$notify=new alert($resource);   
		if($repeat=='false') $enddate = '999999999999';
		
		$tempUser = $user_id;
		if(isset($_GET['impersonate'])){
			$tempUser = (int)$_GET['impersonate'];
		}
		
		// assigning project to entry section
		$projectValue = "";
		if(isset($_GET['selectedProject'])){
			$projectValue = $_GET['selectedProject'];
			if($projectValue == '-1'){
				$projectValue = null;
			}
		}
		else{
			// $sql = "select permissions_project_default from permissions where permissions_user = :0 and permissions_resource = :1";
			// $prep = dbHelp::query($sql, array($tempUser, $resource));
			// $res = dbHelp::fetchRowByIndex($prep);
			// if(!isset($res[0])){
				// $projectValue = null;
			// }
			// else{
				// $projectValue = $res[0];
			// }
			$projectValue = null;
		}

		$entriesIdArray = array();
		//building the repetition pattern
		while((substr($weekahead,0,8)<=$enddate) && ($w<53)){
			if (!$perm->addAhead($weekahead, $slots)){
				throw new Exception($perm->getWarning());
			}
			
			if (!$perm->checkOverlap($weekahead,$slots)){
				throw new Exception($perm->getWarning());
			}
			
			$sql = "
				insert into entry(
					entry_user
					,entry_datetime
					,entry_slots
					,entry_assistance
					,entry_repeat
					,entry_status
					,entry_resource
					,entry_action
					,entry_comments
					".(isset($projectValue) ? ",entry_project" : "")."
				) 
				values(
					:0
					,".dbHelp::convertDateStringToTimeStamp($weekahead,'%Y%m%d%H%i')."
					,:1
					,:2,".$arrrep[0]."
					,:3
					,:4
					,'".date('Y-m-d H:i:s',time())."'
					,NULL
					".(isset($projectValue) ? ",:5" : "")."
				)
			";
			$sqlDataArray = array($tempUser, $slots, $assistance, $EntryStatus, $resource);
			if(isset($projectValue)){
				$sqlDataArray[] = $projectValue;
			}
			dbHelp::query($sql, $sqlDataArray);

			$sql="
				SELECT 
					entry_id 
				from 
					entry 
				where 
					entry_user = :0 
					and entry_datetime = ".dbHelp::convertDateStringToTimeStamp($weekahead, '%Y%m%d%H%i')." 
					and entry_repeat = ".$arrrep[0] ." 
					and entry_resource = :1
			";
			$res=dbHelp::query($sql, array($tempUser, $resource));
			$last=dbHelp::fetchRowByIndex($res);
			$entriesIdArray[] = $last[0];
			
			// xfieldsinputtype: 1 = input, 2 = singlepickcheckbox, 3 = multipickcheckbox
			$sql = "
				select 
					xfields_name
					, xfields_id
					, xfields_label
					, xfields_type 
				from 
					xfields 
				where 
					xfields_resource=:0 
					and xfields_placement = 1 
				group by 
					xfields_id
					, xfields_type
				"
			;
			$res=dbHelp::query($sql, array($resource));
			$extra= array();
			while($arr=dbHelp::fetchRowByIndex($res)){
				$val = '';
				$val = cleanValue($_GET[str_replace(" ", "_", $arr[0])."-".$arr[1]]);
				// eval("\$$var='$val';");

				if(($arr[3] == 2 || $arr[3] == 3) && $val=='true')
					$extra[$arr[0]]=$arr[2];
				else if($arr[3] == 1)
					$extra[$arr[2]]=$val;
					
				$sql="insert into xfieldsval(xfieldsval_entry,xfieldsval_field,xfieldsval_value) values(".$last[0].",".$arr[1].", :0)";
				dbHelp::query($sql, array($val));
			}
			
			$notify->setSlots($slots);
			$notify->setEntry($last[0]);
			$notify->setUser($user_id);
			
			if($assistance){
			   $notify->toAdmin($weekahead,$extra,'assistance');
			} 
			elseif($perm->getResourceStatus() == 4){
				$notify->toAdmin($weekahead,$extra,'newentry');
			}
			
			if ($repeat=='false') $w=53;
			$w++;
			$weekaheadUTC=mktime(0,0,0,$month, $day+7*$w,$year);
			$weekahead=date("Ymd",$weekaheadUTC) . substr($datetime,8,4);
		}
		
		if($perm->getResourceStatus() == 6){ // sequencing
			$json->functionName = "itemInsertShowDivAndCheckUser";
			// $json->arguments = array($resource, 'itemManagementHtml', $entriesIdArray);
			$json->arguments = array($resource, 'itemManagementHtml', $entriesIdArray[0], true);
		}
		else{
			$json->message = "Entry(ies) added";
		}
		
		// $json->success = true;
		echo json_encode($json);
	}
	//changes the entry state to 3, ie, invisible
	function del(){
		$user_id=getUserId();
		$user_passwd=getPass();
		
		$resource=(int)cleanValue($_GET['resource']);
		$entry=(int)cleanValue($_GET['entry']);

		// Gets the all the users, entry_ids and status of the given entry_id date, of a given resource
		$sql = "SELECT entry_user,entry_id,entry_status, entry_datetime FROM entry where entry_datetime in (select entry_datetime from entry where entry_id = :0) and entry_resource = :1 AND entry_status IN ( 1, 2, 4 ) order by entry_id";
		$res = dbHelp::query($sql, array($entry, $resource));
		$perm = new permClass;
		$tempUser = $user_id;
		$found = false;
		if(isset($_GET['impersonate'])){
			$tempUser = $_GET['impersonate'];
		}

		$seekentry = "";
		while($arr=dbHelp::fetchRowByIndex($res)){
			if($seekentry == ""){
				$seekentry=$arr[1];
			}

			if($tempUser == $arr[0]){
				$found=true;
				$seekentry=$arr[1];
				break;
			}
		}

		$sqlResp = "SELECT resource_resp FROM resource where resource_id = :0";
		$resResp = dbHelp::query($sqlResp, array($resource));
		$arrResp = dbHelp::fetchRowByIndex($resResp);
		if(!$perm->setPermission($user_id,$resource,$user_passwd)){
			throw new Exception($perm->getWarning());
		}
		
		if(!$found && $tempUser != $arrResp[0]){
			throw new Exception("Wrong user!");
		}
		
		$deleteall=$_GET['deleteall'];
		if($entry!=$arr[1]){
			$deleteall=0; //delete from monitor does not allow delete all 
		}

		if (!$perm->addBack($arr[3], true)){
			throw new Exception($perm->getWarning());
		}
		
		$sql="select entry_repeat,".dbHelp::getFromDate('entry_datetime','%Y%m%d%H%i').",entry_status from entry where entry_id = :0";
		$res=dbHelp::query($sql, array($entry));
		$arr=dbHelp::fetchRowByIndex($res);
		// $status=$arr[2]; // to set the waitlist with the same status as previous one, this is bad, will make the next user on the waiting list automaticaly confirmed
		if ($deleteall==1){     
			$repeat = $arr[0];
			$sql="update entry set entry_status=3 where entry_repeat = ".$repeat;
		} else {
			$sql="update entry set entry_status=3 where entry_id = ".$seekentry;
		}
		
		$resPDO = dbHelp::query($sql);
		if (dbHelp::numberOfRows($resPDO)==0) {
			throw new Exception("No permission to delete selected entry(ies)");
		} else {
			$notify=new alert($resource);
			$notify->setUser($user_id);
			$notify->setEntry($entry);
			
			if ($entry==$seekentry) { //only eventually notify if not delete from monitor
			
				if (($perm->getResourceStatus()==4)) { // if there is a manager and user is the same as in the entry, ie, not admin              
					$notify->toAdmin($arr[1],'','delete');
				}
				$notify->toWaitList('delete'); // for waiting list. As to be send before update the entry to regular.    
				
				if(($waitListEntry = waitListAuxFunction($entry)) !== false){
					// $sql = "update entry set entry_status=".$status." where entry_status=4 and entry_resource=".$arr[0]." and entry_datetime='".$arr[1]."'";
					// changes the status of the first entry_id found
					// $sql = "update entry set entry_status=".$status." where entry_id = :0"; // makes the next user confirmed if the deleter had already confirmed it
					$status = 1;
					if($perm->getResourceStatus() == 3 || $perm->getResourceStatus() == 4){
						$status = 2;
					}
					$sql = "update entry set entry_status = ".$status." where entry_id = :0";
					$res = dbHelp::query($sql, array($waitListEntry));
				}
			}
			
			//always notify if it was deleted from admin
			if($perm->getWasAdmin()) $notify->fromAdmin('delete');
		}
		
		if($perm->getResourceStatus() == 6){ // sequencing
		
			$entryOrRepeat = $entry;
			$select = "select item_assoc_id, item_assoc_item from item_assoc";
			if($deleteall == 1){
				$entryOrRepeat = $repeat;
				$sql = $select.", entry where item_assoc_entry = entry_id and entry_repeat = :0";
			}
			else{
				$sql = $select." where item_assoc_entry = :0";
			}
			
			$prep = dbHelp::query($sql, array($entryOrRepeat));
			while($row = dbHelp::fetchRowByIndex($prep)){
				// check if there are more entries with the same item, if there arent then make the item available
				$sqlCount = "select count(item_assoc_item) from item_assoc where item_assoc_item = ".$row[1];
				$prepCount = dbHelp::query($sqlCount);
				$rowCount = dbHelp::fetchRowByIndex($prepCount);
				if($rowCount[0] == 1){
					// changes the item status to available
					$sqlItem = "update item set item_state = 1 where item_id = :0 and item_state != 1";
					$prepItem = dbHelp::query($sqlItem, array($row[1]));
				}
				
				// deletes the association of that item with the entry
				$sqlItem = "delete from item_assoc where item_assoc_id = :0";
				$prepItem = dbHelp::query($sqlItem, array($row[0]));
			}
		}
		
		$json->message = "Entry(ies) deleted!";
		mailingList($entry);
		echo json_encode($json);
	}

	function update(){
		$datetime=cleanValue($_GET['datetime']);
		$slots=(int)cleanValue($_GET['slots']);
		$assistance = ($_GET['assistance'] == 'true') ? 1: 0;
		$user_id=getUserId();
		$user_passwd=getPass();

		$resource=cleanValue($_GET['resource']);
		$entry=cleanValue($_GET['entry']);
		
		$day=substr($datetime,6,2);
		$month=substr($datetime,4,2);
		$year=substr($datetime,0,4);
		
		$arr = getSlotsResolutionMaxHours($day, $month, $year, $user_id, $resource);
		$totalSlots = $arr[0];
		$resolution = $arr[1];
		$maxHours = $arr[2];

		if($arr[3] != $user_id && $maxHours != 0){
			$sql="select entry_slots from entry where entry_id= :0";
			$res=dbHelp::query($sql, array($entry));
			$arr=dbHelp::fetchRowByIndex($res);
			$formerEntrySlots = $arr[0];
			
			$newTime = ($totalSlots - $formerEntrySlots + $slots) * $resolution / 60;
			$timeUsed = $totalSlots * $resolution/60;

			if($newTime > $maxHours){
				throw new Exception ("You cannot book more then ".$maxHours." hours per week, you have ".($maxHours - $timeUsed)." hours left.");
			}
		}
		
		$perm= new permClass;
		if(!$perm->setPermission($user_id,$resource,$user_passwd)){
			throw new Exception ($perm->getWarning());
		}
		
		if(!$perm->addBack($datetime, true)){
			throw new Exception ($perm->getWarning());
		}
		
		if(!$perm->addAhead($datetime, $slots)){
			throw new Exception ($perm->getWarning());
		}

		//checking datetime before update
		$sql="select entry_datetime, entry_resource, entry_user, entry_id, entry_slots from entry where entry_id= :0";
		$resdt=dbHelp::query($sql, array($entry));
		$arrdt=dbHelp::fetchRowByIndex($resdt);
		$entry = $arrdt[3];
		$oldSlots = $arrdt[4];
		$oldDate = $arrdt[0];

		// Doesnt let the entry be removed if its before the currenttime - delhour
		if(!$perm->addBack($arrdt[0], true)){
			throw new Exception ($perm->getWarning());
		}
		
		// if update not from same user
		if($user_id!=$arrdt[2] && !$perm->getWasAdmin()){
			throw new Exception("Wrong User");
		}
		
		// if resource needs confirmation by resp or user
		// if (!$perm->getWasAdmin() && ($perm->getResourceStatus() == 4 || $perm->getResourceStatus() == 3)){
		// always sets the entry updated to unconfirmed
		if ($perm->getResourceStatus() == 4 || $perm->getResourceStatus() == 3){ 
			// current date in time format
			$todaysDate = time(date("YmdHi"));

			// $sql="select entry_status from entry where entry_id = :0";
			// $res = dbHelp::query($sql, array($entry));
			// $arr = dbHelp::fetchRowByIndex($res);
			
			$delHourLimit = strtotime($datetime) + $perm->getResourceDelHour()*60*60;
			$dateBackLimit = date("YmdHi", $delHourLimit);
			// if ($delHourLimit > $todaysDate) {
				$sql="update entry set entry_status = 2 where entry_id = :0";
				$res = dbHelp::query($sql, array($entry));
			// }
		}
		
		// assigning project to entry section
		$extraDataArray = array($slots);
		$projectUpdateSql = "";
		if(isset($_GET['selectedProject'])){
			$projectUpdateSql = ",entry_project=:1";
			$projectValue = $_GET['selectedProject'];
			if($projectValue == '-1'){
				$projectValue = NULL;
			}
			$extraDataArray[] = $projectValue;
		}
		// *******************
		
		// get waiting list data before update
		$waitListData = waitListAuxFunction($entry, true);
		
		$newDate = dbHelp::convertDateStringToTimeStamp($datetime,'%Y%m%d%H%i');
		$sql = "update entry set entry_user=".$arrdt[2].", entry_datetime=".$newDate.",entry_slots= :0, entry_action = '".date('Y-m-d H:i:s',time())."' ".$projectUpdateSql.", entry_assistance = ".$assistance." where entry_id=". $entry;
		$resPDO = dbHelp::query($sql, $extraDataArray);
		if(dbHelp::numberOfRows($resPDO) == 0){
			// this should never occur because the action date should always be different then the one previously set
			throw new Exception("Entry info not updated.");
		}
		else{
			// notification for waiting list, deletes the first user on the waiting list, not particularly usefull
			// $sql = "select entry_id, user_id from entry,".dbHelp::getSchemaName().".user where entry_user=user_id and entry_status=4 and entry_datetime='".$arrdt[0]."' and entry_resource=".$arrdt[1]." order by entry_id";
			// $res = dbHelp::query($sql);
			// $arrStatus = dbHelp::fetchRowByIndex($res);
			// if(dbHelp::numberOfRows($res)>0){
				// $notify = new alert($resource);
				// $notify->setUser($arrStatus[1]);
				// $notify->setEntry($arrStatus[0]);
				// $notify->toWaitList('update'); //only eventually notify if not delete from monitor
				
				// $sql = "delete from entry where entry_id=".$arrStatus[0]; // deleting a monitoring entry
				// dbHelp::query($sql);
			// }
			
			// not using the "toWaitList" from alertClass because its way too complex and doesnt iterate through all the users in waiting list
			$compareRes = dateAndTimeCompare($oldDate, $oldSlots, $datetime, $slots, $resolution);
			// 0 means its the same date
			if($compareRes != 0){
				if($waitListData !== false){
					$tempEntryRow = array();
					$sql = "select user_firstname, user_lastname, user_email, resource_name from resource join user on resource_resp = user_id where resource_id = :0";
					$prep = dbHelp::query($sql, array($resource));
					$row = dbHelp::fetchRowByIndex($prep);
					$replyToPerson = $row[0]." ".$row[1];
					$replyToPersonMail = $row[2];
					$resName = $row[3];
					
					$sql = "select user_email from ".dbHelp::getSchemaName().".user where user_id=:0";
					// completely out of the original slot(s)
					if($compareRes == -1){
						$tempEntryRow = current($waitListData);
						$prep = dbHelp::query($sql, array($tempEntryRow['entry_user']));
						$row = dbHelp::fetchRowByIndex($prep);
						$subject = "You are booked for resource ".$resName;
						$address = $row[0];
						$message = "You are now booked for resource ".$resName." at ".$oldDate.".";
						
						$resstatus = 1;
						if($perm->getResourceStatus() == 4 || $perm->getResourceStatus() == 3){
							$resstatus = 2;
							$message .= "\nPlease confirm the entry.";
						}
						// no need to put the vars in an array when making the query, they are safe
						$sql = "update entry set entry_status=".$resstatus." where entry_id=".$tempEntryRow['entry_id'];
						$res = dbHelp::query($sql);
						
						$mailObj = getMailObject($subject, $address, $message, $replyToPerson, $replyToPersonMail);
						sendMailObject($mailObj);
					}
					else{
						$subject = "Entry for resource ".$resName." has changed";
						$newDateEnd = dbHelp::convertToDate(strtotime($datetime) + $slots * $resolution * 60, true);

						$messageBegin = "Entry for ".$resName." that started at ".$oldDate." and ended at ";
						$messageEnd = " was changed to start at ".dbHelp::convertToDate($datetime)." and end at ".$newDateEnd.".\nPlease check if you want to stay on the waiting list at the new time.";
						foreach($waitListData as $tempEntryRow){
							$prep = dbHelp::query($sql, array($tempEntryRow['entry_user']));
							$row = dbHelp::fetchRowByIndex($prep);
							$address = $row[0];
							
							$sqlEntry = "update entry set entry_datetime=".$newDate.",entry_slots= :0, entry_action = '".date('Y-m-d H:i:s',time())."' where entry_id=".$tempEntryRow['entry_id'];
							$resPDO = dbHelp::query($sqlEntry, array($slots));
							$tempTime = strtotime($tempEntryRow['entry_datetime']) + $tempEntryRow['entry_slots'] * $resolution * 60;
							$tempOldDateEnd = dbHelp::convertToDate($tempTime, true);
							$mailObj = getMailObject($subject, $address, $messageBegin.$tempOldDateEnd.$messageEnd, $replyToPerson, $replyToPersonMail);
							sendMailObject($mailObj);
						}
					}
				}
			}
		}
		
		// xfieldsinputtype: 1 = input, 2 = singlepickcheckbox, 3 = multipickcheckbox
		$sql="select xfields_name,xfields_id, xfields_label from xfields where xfields_resource= :0 group by xfields_id, xfields_type";
		$res=dbHelp::query($sql, array($resource));
		while($arr=dbHelp::fetchRowByIndex($res)){
			$val = '';
			$val=cleanValue($_GET[$arr[0]."-".$arr[1]]);
			$extra[$arr[0]]=$val;
			$sql="update xfieldsval set xfieldsval_value= :0 where xfieldsval_entry=".$entry." and xfieldsval_field=".$arr[1];
			dbHelp::query($sql, array($val));
		}
		
		$notify=new alert($resource);
		$notify->setUser($user_id);
		$notify->setSlots($slots);
		$notify->setEntry($entry);
		if($perm->getResourceStatus()==4){
			$notify->toAdmin($datetime,$extra,'update');
		}
		
		if($perm->getWasAdmin()){
			// $notify=new alert($resource);
			// $notify->setEntry($entry);
			// $notify->setUser($user_id);
			$notify->fromAdmin('update',$extra);
		}
		
		$json->message = "Entry info updated!";
		mailingList($entry, $oldDate, $oldSlots);
		echo json_encode($json);
	}

	//set up one entry on top of another one and sets it up=4
	function monitor(){
		$user_id=getUserId();
		$user_passwd=getPass();

		$resource=cleanValue($_GET['resource']);
		$entry=cleanValue($_GET['entry']);
		$code=cleanValue($_GET['code']);
		
		$perm = new permClass;
		if(!$perm->setPermission($user_id,$resource,$user_passwd)){
			throw new Exception($perm->getWarning());
		}
		
		$sql = "insert into repetition(repetition_code) values(:0)";
		dbHelp::query($sql, array($code));
		
		$sql = "select repetition_id from repetition where repetition_code= :0";
		$res = dbHelp::query($sql, array($code));
		$arrrep = dbHelp::fetchRowByIndex($res);

		$sql = "select entry.entry_datetime, resource.resource_resp from entry, resource where entry.entry_resource=resource.resource_id and entry_id= :0";
		$res = dbHelp::query($sql, array($entry));
		$currentDate = date('Y-m-d H:i:s',time());
		$arr = dbHelp::fetchRowByIndex($res);
		
		// Only the "manager"/responsavel of a certain resource can monitor entries in the past
		if($currentDate > $arr[0] && $user_id != $arr[1]){
			throw new Exception("You cannot monitor entries in the past");
		}
		
		// impersonate user here by get
		$tempUser = $user_id;
		if(isset($_GET['impersonate'])){
			$tempUser = $_GET['impersonate'];
		}

		// Block of code changed to stop users from getting in the waiting list more then once
		$sql="select * from entry where entry_user = :0 and entry_status != 3 and entry_datetime in (select entry_datetime from entry where entry_id= :1)";
		$res=dbHelp::query($sql, array($tempUser, $entry));
		$arr=dbHelp::fetchRowByIndex($res);
		if(!empty($arr[0])){
			throw new Exception("User already on the waiting list!");
		}

		$sql="select * from entry where entry_id= :0";
		$res=dbHelp::query($sql, array($entry));
		$arr=dbHelp::fetchRowByIndex($res);
		// end of block change
		
		// impersonate user here by get
		$sql="insert into entry(entry_user,entry_datetime,entry_slots,entry_assistance,entry_repeat,entry_status,entry_resource,entry_action,entry_comments) values(" . $tempUser . ",'" .$arr[2] . "',". $arr[3] . ",". $arr[4] . ",". $arrrep[0] . ",4," .$arr[7]. ",'".date('Y-m-d H:i:s',time())."',NULL)";  
		dbHelp::query($sql);
		
		$sql="SELECT entry_id from entry where entry_user = ".$tempUser." and entry_datetime = '".$arr[2]."' and entry_repeat = " . $arrrep[0] ." and entry_resource = :0";
		$res=dbHelp::query($sql, array($resource));
		$last=dbHelp::fetchRowByIndex($res);
			
		// xfieldsinputtype: 1 = input, 2 = singlepickcheckbox, 3 = multipickcheckbox
		$sql="select xfields_name,xfields_id from xfields where xfields_resource= :0 group by xfields_id, xfields_type";
		$res=dbHelp::query($sql, array($resource));
		while($arrx=dbHelp::fetchRowByIndex($res)){
			$val=cleanValue($_GET[$arrx[0]."-".$arrx[1]]);
			$sql="insert into xfieldsval(xfieldsval_entry,xfieldsval_field,xfieldsval_value) values(".$last[0].",".$arrx[1].", :0)";
			dbHelp::query($sql, array($val));
		}
		
		$json->message = "Entry monitored!";
		echo json_encode($json);
	}

	// change the entry status from 2 to 1
	function confirm(){
		$user_id = getUserId();
		$user_passwd = getPass();

		$resource = cleanValue($_GET['resource']);
		$entry = cleanValue($_GET['entry']);
		$macChecksOut = $_GET['mac'];

		$perm = new permClass;
		if(!$perm->setPermission($user_id,$resource,$user_passwd)){
			throw new Exception($perm->getWarning());
		}
		
		if($perm->getResourceStatus() == 4 && $perm->getWasAdmin()){
			$notify = new alert($resource);
			$notify->setEntry($entry);
			$notify->fromAdmin('confirm');
		}
		elseif(!$perm->confirmEntry($entry, $macChecksOut)){
			throw new Exception($perm->getWarning());
		}

		$sql = "update entry set entry_status = 1 where entry_id = :0";
		$resPDO = dbHelp::query($sql, array($entry));
		if(dbHelp::numberOfRows($resPDO) == 0){
			// theres really no need for this
			// throw new Exception("No changes were made to this entry");
			// this is an ugly patch until i merge the addcomments method and this method
			$json->message = "";
			echo json_encode($json);
			return;
	}
		
		// Delete other entries from that day for the same resource, we dont wanna know who was on waiting list?
		// no we dont.... if we do, "we" will have to fix the problem of showing the entry in blue due to having someone in waiting list
		// $sql = "select entry_datetime from entry where entry_id = :0";
		// $res = dbHelp::query($sql, array($entry));
		// $arr = dbHelp::fetchRowByIndex($res);

		// $sql = "delete from entry where entry_datetime='".$arr[0]."' and entry_status in (1,2,4) and entry_id <> :0 and entry_resource = :1";
		// dbHelp::query($sql, array($entry, $resource));
		// ******************************************************************************************************
		
		$json->message = "Entry Confirmed";
		echo json_encode($json);
	}

	// merge this method with the confirm one?
	function addcomments(){
		$resource=cleanValue($_GET['resource']);
		$entry=cleanValue($_GET['entry']);
		$comments=cleanValue($_GET['comments']);
		$user_id=getUserId();

		$notify=new alert($resource);
		$notify->setEntry($entry);
		$notify->setUser($user_id);
		
		if ($comments != ''){
			// this is quite bad, it allows for comments to be changed over and over again
			$sql="update entry set entry_comments= :0 where entry_id= :1";
			$prep = dbHelp::query($sql, array($comments, $entry));
			if($notify->getResourceResp() != $user_id && dbHelp::numberOfRows($prep) > 0){
				$notify->toAdmin(date("YmdHi"),'','comment',$comments);
			}
			// this is ignored
			$json->message = "Comment added";
			echo json_encode($json);
		}
	}

	function addCommentsXfields(){
		$entry = $_POST['entry'];
		$idArray = $_POST['idArray'];
		foreach($idArray as $id => $value){
			if($value != 'undefined'){
				$sql="insert into xfieldsval(xfieldsval_entry,xfieldsval_field,xfieldsval_value) values(:0, :1, :2)";
				dbHelp::query($sql, array($entry, $id, $value));
			}
		}
	}

	function getUserId(){
		if(isset($_SESSION['user_id']) && $_SESSION['user_id']!='')
			return $_SESSION['user_id'];
		else {
			$sql= "select user_id from ".dbHelp::getSchemaName().".user where user_login = :0";
			$res=dbHelp::query($sql, array($_GET['user_id']));
			$arr=dbHelp::fetchRowByIndex($res);
			
			isBlacklisted($arr[0]);
			
			return $arr[0];
		}
	}

	function getPass(){
		if(isset($_SESSION['user_pass']) && $_SESSION['user_pass']!=''){
			return $_SESSION['user_pass'];
		}
		else{
			return cryptPassword(cleanValue($_GET['user_passwd']));
		}
	}
	
	function mailingList($entry, $oldDate = null, $oldSlots = 0){
		$sql = "select entry_datetime, entry_slots, resource_resolution, resource_name, resource_id, entry_user from entry, resource where entry_id = :0 and resource_id = entry_resource";
		$prep = dbHelp::query($sql, array($entry));
		$res = dbHelp::fetchRowByIndex($prep);
		$finalDate = date('Y-m-d H:i:00',(strtotime($res[0]) + $res[1] * $res[2] * 60));
		
		$resource = $res[4];
		$user = $res[5];
		$replyToPerson = "";
		$replyToPersonMail = "";
		if(!isset($oldDate)){
			$subject = 'Entry removed';
			$message = "Resource is now available from ".$res[0]." to ".$finalDate." on resource ".$res[3];
		}
		else{
			$oldFinalDate = date('Y-m-d H:i:00',(strtotime($oldDate) + $oldSlots * $res[2] * 60));
			$subject = 'Entry updated';
			$message = "Booking for resource ".$res[3]." from ".$oldDate." to ".$oldFinalDate." has changed to start at ".$res[0]." and to end at ".$finalDate;
		}

		$sql = "select user_email, user_firstname from permissions, user where permissions_resource = ".$resource." and permissions_sendmail = 1 and user_id = permissions_user and permissions_user != ".$user;
		$prep = dbHelp::query($sql, array($entry));
		while($res = dbHelp::fetchRowByIndex($prep)){
			$mailObj = getMailObject($subject, $res[0], $message, $replyToPerson, $replyToPersonMail);
			sendMailObject($mailObj, false);
			// sendMailObject($mailObj);
		}
	}
	
	function filterHappyHourSlotsOut($hour, $min, $day, $month, $year, $slots, $resource, $resourceResolution){
		$hhArray = getHappyHoursFromResource($resource);
		
		$temp = current($hhArray);
		if(isset($temp) && $resourceResolution != 0){
			$hhTime = 0;
			// 'Y-m-d H:i:00'
			$date = $year."-".$month."-".$day." ".$hour.":".$min.":00";
			foreach($hhArray as $hh){
				if(($tempArray = $hh->getCostAndDiscountTime($date, $slots, $resourceResolution, 0)) !== false){
					$hhTime += $tempArray['time'];
				}
			}
			$slots -= ceil($hhTime / $resourceResolution);
			
			return $slots;
		}
		
		return false;
	}
	
	// returns the first entry_id and entry_user of a set of entries in waiting list or if $giveAll == true returns all the entries with status = 4
	function waitListAuxFunction($entry_id, $giveAll = false){
		$sql = "
			select 
				*
			from 
				(select entry_resource as res,entry_datetime as date from entry where entry_id = :0) temp 
				inner join 
					entry 
				on 
					entry_resource = res 
					and entry_datetime = date 
			where 
				entry_id != :0 
				and entry_status = 4 
		";
		$res = dbHelp::query($sql, array($entry_id));
		if(dbHelp::numberOfRows($res) > 0){
			if($giveAll){
				$waitingListArray = array();
				while($arr = dbHelp::fetchRowByName($res)){
					$waitingListArray[] = $arr;
				}
				return $waitingListArray;
			}
			$arr = dbHelp::fetchRowByIndex($res);
			return $arr[0];
		}
		
		return false;
	}
	
	
	function dateAndTimeCompare($oldDate, $oldSlots, $newDate, $newSlots,  $resolution){
		if($oldDate == $newDate && $oldResolution == $newResolution)
			return 0; // same time
			
		$oldTimeStart = strtotime($oldDate);
		$oldTimeEnd = $oldTimeStart + $oldSlots * $resolution * 60;
		
		$newTimeStart = strtotime($newDate);
		$newTimeEnd = $newTimeStart + $newSlots * $resolution * 60;
		// throw new Exception(date('Y-m-d H:i:00', $oldTimeStart)."-".date('Y-m-d H:i:00', $oldTimeEnd)."     ".date('Y-m-d H:i:00', $newTimeStart)."-".date('Y-m-d H:i:00', $newTimeEnd));
		if($newTimeEnd <= $oldTimeStart || $newTimeStart >= $oldTimeEnd){
			return -1; // completely out of the original slot(s)
		}
		
		return 1; // partially the same
	}
?>
