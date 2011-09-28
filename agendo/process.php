<?php
//session_start();
	// This class was altered by Pedro Pires (The chosen two)
	require_once("commonCode.php");
	initSession();
	require_once("permClass.php");
	require_once("alertClass.php");
	require_once("functions.php");

?>

<?php
    /**
    * @author Nuno Moreno
    * @copyright 2009-2010 Nuno Moreno
    * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
    * @version 1.0
    * @abstract: script for dealing with ajax weekview requests. It sets the entries into different states
    * 1-> regular, 2-> pre-reserve, 3->deleted, 4->Monitored
    */
    
// require_once(".htconnect.php");
// require_once("__dbHelp.php");
$action=$_GET['action'];
//echo $action;
call_user_func($action);

function getUserId(){
	if(isset($_SESSION['user_id']) && $_SESSION['user_id']!='')
		return $_SESSION['user_id'];
	else {
		$sql= "select user_id from ".dbHelp::getSchemaName().".user where user_login = '".$_GET['user_id']."'";
		$res=dbHelp::query($sql) or die ($sql);
		$arr=dbHelp::fetchRowByIndex($res);
		// return $_GET['user_id'];
		return clean_input($arr[0]);
	}
}

function getPass(){
	if(isset($_SESSION['user_pass']) && $_SESSION['user_pass']!='')
		return $_SESSION['user_pass'];
	else
		// return cryptPassword($_GET['user_passwd']);
		return cryptPassword(clean_input($_GET['user_passwd']));
}

//adding function. set the state to 1 or 2 depending on equipment state
function add(){
    $w=0;
    $update=clean_input($_GET['update']);
    if ($update>0) {update();exit;}
    $assistance=$_GET['assistance'];
    $code=clean_input($_GET['code']);
    $repeat=clean_input($_GET['repeat']);
    $enddate=clean_input($_GET['enddate']);
    $enddate=substr($enddate,0,4) . substr($enddate,5,2) . substr($enddate,8,2);
    $datetime=clean_input($_GET['datetime']);
    $min=substr($datetime,10,2);
    $hour=substr($datetime,8,2);
    $slots=clean_input($_GET['slots']);
    //$assistance=($assistance)?"1":"0";
    if($assistance=="true")	$assistance=1;
    else $assistance=0;

    $user_id=getUserId();
    $user_passwd=getPass();
    $resource=clean_input($_GET['resource']);
	
	//201102251200
    $year=substr($datetime,0,4);
    $month=substr($datetime,4,2);
    $day=substr($datetime,6,2);
	//****************
	
	$arr = getSlotsResolutionMaxHours($day, $month, $year, $user_id, $resource);

	if($arr[3] != $user_id && $arr[2] != 0){
		// check if the number of slots*resolution is bigger then the resource_maxhoursweek
		// if so, return error and exit
		$totalTime = ($arr[0] + $slots) * $arr[1] / 60;
		$timeUsed = $arr[0] * $arr[1] / 60;
		if($totalTime > $arr[2]){
			echo "You cannot book more then ".$arr[2]." hours per week, you have ".($arr[2] - $timeUsed)." hours left.";
			exit;
		}
	}
	//****************
	
    $perm= new permClass;
    if (!$perm->setPermission($user_id,$resource,$user_passwd)) {echo $perm->getWarning();exit;};
    if (!$perm->addRegular()) {echo $perm->getWarning();exit;};
    if (!$perm->addAhead($datetime, $slots)) {echo $perm->getWarning();exit;}
    if (!$perm->addBack($datetime)) {echo $perm->getWarning();exit;}
    $EntryStatus=$perm->getEntryStatus();
    if (!$perm->getEntryStatus()) {echo $perm->getWarning();exit;}
    
    //if there is no associated entries it creates a new set
    $sql="select repetition_id from repetition where repetition_code='".$code."'";
    $res=dbHelp::query($sql) or die($sql) ;
    //if there is no related entry already it creates one
    if (dbHelp::numberOfRows($res)==0) {    
        $sql="insert into repetition(repetition_code) values(" . $code . ")";
        dbHelp::query($sql) or die($sql);
    }

    //getting the entry code
    $sql="select repetition_id from repetition where repetition_code='". $code . "'";

    $res=dbHelp::query($sql) or die($sql);
    $arrrep=dbHelp::fetchRowByIndex($res);
    $weekahead=$datetime;
    $notify=new alert($resource);   
    if ($repeat=='false') $enddate='999999999999';
	
	$tempUser = $user_id;
	if(isset($_GET['impersonate'])){
		$tempUser = $_GET['impersonate'];
	}
    //building the repetition pattern
    while ((substr($weekahead,0,8)<=$enddate) && ($w<53)) {
        if (!$perm->addAhead($weekahead, $slots)) {echo $perm->getWarning();exit;}
        if (!$perm->checkOverlap($weekahead,$slots)) {echo $perm->getWarning();exit;}
        // $sql="insert into entry(entry_user,entry_datetime,entry_slots,entry_assistance,entry_repeat,entry_status,entry_resource,entry_action,entry_comments) values(".$user_id.",".dbHelp::convertDateStringToTimeStamp($weekahead,'%Y%m%d%H%i')."," . $slots .",". $assistance ."," . $arrrep[0] .",". $EntryStatus . "," . $resource . ", '".date('Y-m-d H:i:s',time())."',NULL)";
        $sql="insert into entry(entry_user,entry_datetime,entry_slots,entry_assistance,entry_repeat,entry_status,entry_resource,entry_action,entry_comments) values(".$tempUser.",".dbHelp::convertDateStringToTimeStamp($weekahead,'%Y%m%d%H%i')."," . $slots .",". $assistance ."," . $arrrep[0] .",". $EntryStatus . "," . $resource . ", '".date('Y-m-d H:i:s',time())."',NULL)";
        dbHelp::query($sql) or die($sql);

        // $sql="SELECT LAST_INSERT_ID()";
		// impersonate user here by get
        // $sql="SELECT entry_id from entry where entry_user = ".$user_id." and entry_datetime = ".dbHelp::convertDateStringToTimeStamp($weekahead,'%Y%m%d%H%i')." and entry_repeat = " . $arrrep[0] ." and entry_resource = " . $resource;
        $sql="SELECT entry_id from entry where entry_user = ".$tempUser." and entry_datetime = ".dbHelp::convertDateStringToTimeStamp($weekahead,'%Y%m%d%H%i')." and entry_repeat = " . $arrrep[0] ." and entry_resource = " . $resource;
        $res=dbHelp::query($sql) or die($sql);
        $last=dbHelp::fetchRowByIndex($res);
		
		// xfieldsinputtype: 1 = input, 2 = singlepickcheckbox, 3 = multipickcheckbox
        $sql="select xfields_name, xfields_id, xfields_label, xfields_type from xfields where xfields_resource=".$resource." and xfields_placement = 1 group by xfields_id, xfields_type";
        $res=dbHelp::query($sql) or die($sql);
        $extra= array();
		while($arr=dbHelp::fetchRowByIndex($res)){
			$val = '';
			$val=clean_input($_GET[$arr[0]."-".$arr[1]]);

            // eval("\$$var='$val';");
			if(($arr[3] == 2 || $arr[3] == 3) && $val=='true')
				$extra[$arr[0]]=$arr[2];
			else if($arr[3] == 1)
				$extra[$arr[2]]=$val;
				
            $sql="insert into xfieldsval(xfieldsval_entry,xfieldsval_field,xfieldsval_value) values(".$last[0].",".$arr[1].",'".$val."')";
            dbHelp::query($sql) or die($sql);
        }
		
        $notify->setSlots($slots);
        $notify->setEntry($last[0]);
        $notify->setUser($user_id);
        if ($assistance){
           $notify->toAdmin($weekahead,$extra,'assistance');
        } elseif ($perm->getResourceStatus()==4) {
            $notify->toAdmin($weekahead,$extra,'newentry');
        }
        if ($repeat=='false') $w=53;
        $w++;
        $weekaheadUTC=mktime(0,0,0,$month, $day+7*$w,$year);
        $weekahead=date("Ymd",$weekaheadUTC) . substr($datetime,8,4);
    }
    echo "Entry(ies) added";
}
//changes the entry state to 3, ie, invisible
function del(){
    $user_id=getUserId();
    $user_passwd=getPass();
	
    $resource=clean_input($_GET['resource']);
    $entry=clean_input($_GET['entry']);
    $seekentry='';
    // Gets the all the users, entry_ids and status of the given entry_id date, of a given resource
    $sql = "SELECT entry_user,entry_id,entry_status FROM entry where entry_datetime in (select entry_datetime from entry where entry_id=".$entry.") and entry_resource=".$resource." AND entry_status IN ( 1, 2, 4 ) order by entry_id";
    $res = dbHelp::query($sql) or die($sql);
    $found = false;
    $perm = new permClass;
	
    $seekentry = "";
	$tempUser = $user_id;
	$found = false;
	if(isset($_GET['impersonate'])){
		$tempUser = $_GET['impersonate'];
	}

    // for ($i=0;$i<dbHelp::numberOfRows($res);$i++) {
    while($arr=dbHelp::fetchRowByIndex($res)) {
		if($seekentry == ""){
			$seekentry=$arr[1];
		}
        // $arr=dbHelp::fetchRowByIndex($res);
		// Checks if the current user from the $res list is allowed to delete the current entry
        // if($perm->setPermission($arr[0],$resource,$user_passwd)){
		// Checks if the given user is allowed to delete the current entry
        // if($perm->setPermission($arr[0],$resource,$user_passwd) && $arr[0]==$user_id){
		if($tempUser == $arr[0]){
			$found=true;
			$seekentry=$arr[1];
			// Not used anymore
            // $user_id=$arr[0];//it might be the admin logging in
            break;
        }
    }

    $sqlResp = "SELECT resource_resp FROM resource where resource_id=".$resource;
    $resResp = dbHelp::query($sqlResp) or die($sqlResp);
	$arrResp = dbHelp::fetchRowByIndex($resResp);
    if(!$perm->setPermission($user_id,$resource,$user_passwd)){
		echo $perm->getWarning();
		return;
	}
	
	if(!$found && $tempUser != $arrResp[0]){
		echo "Wrong user!";
		return;
	}
    
    $deleteall=$_GET['deleteall'];
    if ($entry!=$arr[1]) $deleteall=0; //delete from monitor does not allow delete all 
    

    $extra =" and ".dbHelp::date_sub('entry_datetime',$perm->getResourceDelHour(),'hour')." > now()";
    if ($perm->addBack($arr[1])) $extra =""; //if you can delete back there is no time restriction
    
    $sql="select entry_repeat,".dbHelp::getFromDate('entry_datetime','%Y%m%d%H%i').",entry_status from entry where entry_id=". $entry;
    $res=dbHelp::query($sql);
    $arr=dbHelp::fetchRowByIndex($res);
    $status=$arr[2]; // to set the waitlist with the same status as previous one
    if ($deleteall==1){     
        $sql="update entry set entry_status=3 where entry_repeat=" . $arr[0] . $extra;
    } else {
        $sql="update entry set entry_status=3 where entry_id=" . $seekentry . $extra;
    }
    $resPDO = dbHelp::query($sql) or die ($sql);
    if (dbHelp::numberOfRows($resPDO)==0) {
        echo "No permission to delete selected entry(ies)";
    } else {
		$notify=new alert($resource);
        $notify->setUser($user_id);
        $notify->setEntry($entry);
        echo "Entry(ies) deleted!";
        if ($entry==$seekentry) { //only eventually notify if not delete from monitor
		
            if (($perm->getResourceStatus()==4)) { // if there is a manager and user is the same as in the entry, ie, not admin              
                $notify->toAdmin($arr[1],'','delete');
            }
            $notify->toWaitList('delete'); // for waiting list. As to be send before update the entry to regular.    
            
            $sql="select entry_resource,entry_datetime from entry where entry_id=". $entry;
			$res=dbHelp::query($sql);
			$arr=dbHelp::fetchRowByIndex($res);

            $sql="update entry set entry_status=".$status." where entry_status=4 and entry_resource=".$arr[0]." and entry_datetime='".$arr[1]."'";
            $res=dbHelp::query($sql) or die ($sql);
        }
        
        //always notify if it was deleted from admin
        if($perm->getWasAdmin()) $notify->fromAdmin('delete');
    }
}


function update(){
    // $extra='';
    $datetime=clean_input($_GET['datetime']);
    $slots=clean_input($_GET['slots']);
    
    // $user_id=clean_input($_GET['user_id']);
    // $user_passwd=clean_input($_GET['user_passwd']);
	// $user_passwd=cryptPassword($user_passwd);
    $user_id=getUserId();
    $user_passwd=getPass();

    $resource=clean_input($_GET['resource']);
    $entry=clean_input($_GET['entry']);
    
	//**********************************************
    $day=substr($datetime,6,2);
    $month=substr($datetime,4,2);
    $year=substr($datetime,0,4);
	
	$arr = getSlotsResolutionMaxHours($day, $month, $year, $user_id, $resource);
	$totalSlots = $arr[0];
	$resolution = $arr[1];
	$maxHours = $arr[2];

	if($arr[3] != $user_id && $maxHours != 0){
		$sql="select entry_slots from entry where entry_id=". $entry;
		$res=dbHelp::query($sql) or die($sql);
		$arr=dbHelp::fetchRowByIndex($res);
		$formerEntrySlots = $arr[0];
		
		$newTime = ($totalSlots - $formerEntrySlots + $slots) * $resolution / 60;
		$timeUsed = $totalSlots * $resolution/60;

		if($newTime > $maxHours){
			echo "You cannot book more then ".$maxHours." hours per week, you have ".($maxHours - $timeUsed)." hours left.";
			return;
		}
	}
	//**********************************************
	
    $perm= new permClass;
    if (!$perm->setPermission($user_id,$resource,$user_passwd)) {echo $perm->getWarning();return;}
    if (!$perm->addBack($datetime)) {echo $perm->getWarning();return;}

    // $extra =" and addtime(entry_datetime,'-" .  $perm->getResourceDelHour() . ":0:0') > now()";
    $extra =" and ".dbHelp::date_sub('entry_datetime', $perm->getResourceDelHour(), 'hour')." > now()";
    if ($perm->addBack($arr[1])) $extra =""; //if you can delete back there is no time restriction
    //if (!$perm->addBack($datetime)) $extra =" and addtime(entry_datetime,'-" .  $perm->getResourceDelHour() . ":0:0') > now()";
    
    if (!$perm->addAhead($datetime, $slots)) {echo $perm->getWarning();return;}
    //checking datetime before update
    // $sql="select @edt:=entry_datetime,@res:=entry_resource,entry_user from entry where entry_id=". $entry;
    $sql="select entry_datetime,entry_resource,entry_user from entry where entry_id=". $entry;
    $resdt=dbHelp::query($sql) or die($sql);
    $arrdt=dbHelp::fetchRowByIndex($resdt);

	// check impersonate user here by get *************************************************************************************************************
	// $tempUser = $user_id;
	// if(isset($_GET['impersonate'])){
		// $tempUser = $_GET['impersonate'];
	// }

    // if ($tempUser!=$arrdt[2]) {echo "Wrong User";exit;} // if update not from same user
    if ($user_id!=$arrdt[2] && !$perm->getWasAdmin()) {echo "Wrong User";exit;} // if update not from same user
	
	//************************************
	// if resource needs confirmation by resp or user
	if (!$perm->getWasAdmin() && ($perm->getResourceStatus() == 4 || $perm->getResourceStatus() == 3)){ 
	
		// current date in time format
		$todaysDate = time(date("YmdHi"));
		
		// entry time with addded resource delete hour(s)
		$otherDate = strtotime($datetime) - $perm->getResourceDelHour()*60*60;

		$sql="select entry_status from entry where entry_id = ". $entry;
		$res = dbHelp::query($sql) or die("Entry info not updated!");
		$arr = dbHelp::fetchRowByIndex($res);
		
		// if the user is too late to change an entry and the entry is confirmed
		// if ($otherDate < $todaysDate && $arr[0] == 1) {
			// echo "You can't modify this entry. Talk to the person responsible for the equipment.";
			// exit;
		// }
		if ($otherDate > $todaysDate) {
			$sql="update entry set entry_status = 2 where entry_id=". $entry;
			$res = dbHelp::query($sql) or die("Entry info not updated!");
		}
    }
	//************************************
	
	// impersonate user here by get *********************************************************************************************************************
    // $sql="update entry set entry_user=".$tempUser.", entry_datetime=".dbHelp::convertDateStringToTimeStamp($datetime,'%Y%m%d%H%i').",entry_slots=".$slots." where entry_id=". $entry;
    $sql="update entry set entry_user=".$arrdt[2].", entry_datetime=".dbHelp::convertDateStringToTimeStamp($datetime,'%Y%m%d%H%i').",entry_slots=".$slots." where entry_id=". $entry;
    $resPDO = dbHelp::query($sql.$extra) or die($sql.$extra);
    if (dbHelp::numberOfRows($resPDO) == 0) {
        echo "Entry info not updated.";
		exit;
    } 
	else {
        //notification for waiting list
        // $sql="select entry_id, user_id from entry,".dbHelp::getSchemaName().".user where entry_user=user_id and entry_status=4 and entry_datetime=@edt and entry_resource=@res order by entry_id";
        $sql="select entry_id, user_id from entry,".dbHelp::getSchemaName().".user where entry_user=user_id and entry_status=4 and entry_datetime='".$arrdt[0]."' and entry_resource=".$arrdt[1]." order by entry_id";
        $res=dbHelp::query($sql);
        $arrStatus=dbHelp::fetchRowByIndex($res);
        if (dbHelp::numberOfRows($res)>0) {
            $notify=new alert($resource);
            $notify->setUser($arrStatus[1]);
            $notify->setEntry($arrStatus[0]);
            $notify->toWaitList('update'); //only eventually notify if not delete from monitor
            
            $sql="delete from entry where entry_id=" . $arrStatus[0]; // deleting a monitoring entry
            dbHelp::query($sql);
        }
    }
    
	// xfieldsinputtype: 1 = input, 2 = singlepickcheckbox, 3 = multipickcheckbox
	$sql="select xfields_name,xfields_id, xfields_label from xfields where xfields_resource=".$resource." group by xfields_id, xfields_type";
    $res=dbHelp::query($sql) or die($sql);
    while($arr=dbHelp::fetchRowByIndex($res)) {
		$val = '';
		$val=clean_input($_GET[$arr[0]."-".$arr[1]]);
        $extra[$arr[0]]=$val;
        // eval("\$$var='$val';");
        $sql="update xfieldsval set xfieldsval_value='".$val."' where xfieldsval_entry=".$entry." and xfieldsval_field=".$arr[1];
        dbHelp::query($sql) or die("Entry info not updated!");
    }
	
    $notify=new alert($resource);
    $notify->setUser($user_id);
    $notify->setSlots($slots);
    $notify->setEntry($entry);
    if ($perm->getResourceStatus()==4) {
        $notify->toAdmin($datetime,$extra,'update');
    }
    
    if ($perm->getWasAdmin()){
        $notify=new alert($resource);
        $notify->setEntry($entry);
        $notify->setUser($user_id);
        $notify->fromAdmin('update',$extra);
    }
    
    echo "Entry info updated!";
}
//set up one entry on top of another one and sets it up=4
function monitor(){

    // $user_id=clean_input($_GET['user_id']);
    // $user_passwd=clean_input($_GET['user_passwd']);
	// $user_passwd=cryptPassword($user_passwd);
    $user_id=getUserId();
    $user_passwd=getPass();

    $resource=clean_input($_GET['resource']);
    $entry=clean_input($_GET['entry']);
    $code=clean_input($_GET['code']);
    
    $perm= new permClass;
    if (!$perm->setPermission($user_id,$resource,$user_passwd)) {echo $perm->getWarning();return;}
    
    $sql="insert into repetition(repetition_code) values(" . $code . ")";
    dbHelp::query($sql) or die($sql);
    
    $sql="select repetition_id from repetition where repetition_code='". $code . "'";
    $res=dbHelp::query($sql) or die($sql);
    $arrrep=dbHelp::fetchRowByIndex($res);

    $sql="select entry.entry_datetime, resource.resource_resp from entry, resource where entry.entry_resource=resource.resource_id and entry_id=".$entry;
    $res=dbHelp::query($sql) or die($sql);
	$currentDate = date('Y-m-d H:i:s',time());
    $arr=dbHelp::fetchRowByIndex($res);
	// Only the "manager"/responsavel of a certain resource can monitor entries in the past
	if($currentDate > $arr[0] && $user_id != $arr[1]){
		echo "You cannot monitor entries in the past";
		exit;
	}
	
	// impersonate user here by get
	$tempUser = $user_id;
	if(isset($_GET['impersonate'])){
		$tempUser = $_GET['impersonate'];
	}

	// Block of code changed to stop users from getting in the waiting list more then once
    $sql="select * from entry where entry_user = ".$tempUser." and entry_status != 3 and entry_datetime in (select entry_datetime from entry where entry_id=".$entry.")";
    $res=dbHelp::query($sql) or die($sql);
    $arr=dbHelp::fetchRowByIndex($res);
    // if ($arr[1]==$user_id) {echo "User already on the waiting list!";exit;};

	if(!empty($arr[0])){
		echo "User already on the waiting list!";
		exit;
	};
    $sql="select * from entry where entry_id=" . $entry;
    $res=dbHelp::query($sql) or die($sql);
    $arr=dbHelp::fetchRowByIndex($res);
    // end of block change
	
		// impersonate user here by get
    $sql="insert into entry(entry_user,entry_datetime,entry_slots,entry_assistance,entry_repeat,entry_status,entry_resource,entry_action,entry_comments) values(" . $tempUser . ",'" .$arr[2] . "',". $arr[3] . ",". $arr[4] . ",". $arrrep[0] . ",4," .$arr[7]. ",'".date('Y-m-d H:i:s',time())."',NULL)";  
    dbHelp::query($sql) or die($sql);
    
    // $sql="SELECT LAST_INSERT_ID()";
	// $sql="SELECT entry_id from entry where entry_user = ".$user_id." and entry_datetime = (".dbHelp::convertDateStringToTimeStamp($weekahead,'%Y%m%d%H%i').") and entry_repeat = " . $arrrep[0] ." and entry_resource = " . $resource;
	$sql="SELECT entry_id from entry where entry_user = ".$tempUser." and entry_datetime = '".$arr[2]."' and entry_repeat = " . $arrrep[0] ." and entry_resource = " . $resource;
    $res=dbHelp::query($sql) or die($sql);
    $last=dbHelp::fetchRowByIndex($res);
        
	// xfieldsinputtype: 1 = input, 2 = singlepickcheckbox, 3 = multipickcheckbox
	// $sql="select xfields_name,xfields_id, xfields_label from xfields,resxfields where resxfields_field=xfields_id and resxfields_resource=".$resource." group by xfields_id, xfields_type";
    $sql="select xfields_name,xfields_id from xfields where xfields_resource=".$resource." group by xfields_id, xfields_type";
	$res=dbHelp::query($sql) or die($sql);
	while($arrx=dbHelp::fetchRowByIndex($res)){
		// $var=$arrx[0];
		$val=clean_input($_GET[$arrx[0]."-".$arrx[1]]);
		// eval("\$$var='$val';");
		$sql="insert into xfieldsval(xfieldsval_entry,xfieldsval_field,xfieldsval_value) values(".$last[0].",".$arrx[1].",'".$val."')";
		dbHelp::query($sql) or die($sql);
	}
    echo "Entry monitored!";
}

//change the entry status from  2 to 1
function confirm(){

    // $user_id=clean_input($_GET['user_id']);
    // $user_passwd=clean_input($_GET['user_passwd']);
	// $user_passwd=cryptPassword($user_passwd);
    $user_id=getUserId();
    $user_passwd=getPass();

    $resource=clean_input($_GET['resource']);
    $entry=clean_input($_GET['entry']);

    $perm = new permClass;
    if (!$perm->setPermission($user_id,$resource,$user_passwd)) {
		echo $perm->getWarning();
		return;
	}
    if ($perm->getResourceStatus()==4 && $perm->getWasAdmin()) {
        $notify=new alert($resource);
        $notify->setEntry($entry);
        $notify->fromAdmin('confirm');
    } elseif (!$perm->confirmEntry($entry)) {
        echo $perm->getWarning();
        exit;
    }
    // echo $perm->getResourceStatus(),$perm->getWasAdmin();

    $sql="update entry set entry_status=1 where entry_id=" . $entry;
    $resPDO = dbHelp::query($sql) or die($sql);
    // if (mysql_affected_rows()!=0)  echo $perm->getWarning();
    if (dbHelp::numberOfRows($resPDO)!=0) echo $perm->getWarning();
    
    // $sql="select @dt:=entry_datetime from entry where entry_id=" . $entry;
    // dbHelp::query($sql) or die($sql);
    // $sql="delete from entry where entry_datetime=@dt and entry_status in (1,2,4) and entry_id<>". $entry . " and entry_resource=" . $resource;

    $sql="select entry_datetime from entry where entry_id=" . $entry;
    $res = dbHelp::query($sql) or die($sql);
	$arr = dbHelp::fetchRowByIndex($res);
    $sql="delete from entry where entry_datetime='".$arr[0]."' and entry_status in (1,2,4) and entry_id<>".$entry." and entry_resource=" . $resource;
    dbHelp::query($sql) or die($sql);
}

function addcomments(){
    $resource=clean_input($_GET['resource']);
    $entry=clean_input($_GET['entry']);
    $comments=clean_input($_GET['comments']);
	// $user=clean_input($_GET['user_id']);
    $user_id=getUserId();

    $notify=new alert($resource);
    $notify->setEntry($entry);
    $notify->setUser($user_id);
    
    if ($comments!=''){
		$sql="update entry set entry_comments='" . $comments . " 'where entry_id=" . $entry;
		dbHelp::query($sql) or die($sql);
		if($notify->getResourceResp() != $user_id) $notify->toAdmin(date("YmdHi"),'','comment',$comments); 
		echo "Comment added";
	}
}

function addCommentsXfields(){
	$entry = $_POST['entry'];
	$resource = $_POST['resource'];
	$idArray = $_POST['idArray'];
	foreach($idArray as $id => $value){
		if($value != 'undefined'){
			$sql="insert into xfieldsval(xfieldsval_entry,xfieldsval_field,xfieldsval_value) values(".$entry.",".$id.",'".$value."')";
			dbHelp::query($sql);
		}
	}
}

?>
