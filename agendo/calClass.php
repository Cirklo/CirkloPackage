<?php
include_once('collectionClass.php');
/**
  * @author Nuno Moreno
  * @copyright 2009-2010 Nuno Moreno
  * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
  * @version 1.0
  * @abstract Class for configure a single cell in the calendar. This cells will be part of the calendar object, which
  * contains a collection of cell classes.
  * Because I was not able to recall this subclass in the main code it might not be worthwhile doing it
*/

class calCell {
    private $User;
    private $Entry;
    private $NextEntry;
    private $NSlots;
    private $StartTime;
    private $Tag;
    private $StartDate;
    private $Repeat;
    private $EntryStatus;
    private $NextUser;
    
    function setUser($arg)      {$this->User=$arg;}
    function setEntry($arg)     {$this->Entry=$arg;}
    function setNextEntry($arg) {$this->NextEntry=$arg;}
    function setNSlots($arg)    {$this->NSlots=$arg;}
    function setStartTime($arg) {$this->StartTime=$arg;}
    function setTag($arg)       {$this->Tag=$arg;}
    function setStartDate($arg) {$this->StartDate=$arg;}
    function setRepeat($arg)    {$this->Repeat=$arg;}
    function setEntryStatus($arg)    {
        if ($arg==2 or $arg==4) {
                $datetime=$this->getStartDate(). date('Hi',$this->getStartTime());
                $min=substr($datetime,10,2);
                $hour=substr($datetime,8,2);
                $year=substr($datetime,0,4);
                $month=substr($datetime,4,2);
                $day=substr($datetime,6,2);
                $endtime=mktime($hour,$min + (cal::getConfTolerance()+$this->getNSlots()) * cal::getResolution(),0,$month,$day,$year);
                $now=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
                if ($endtime<$now) {
                    $arg=9;
                }
        }
        
        $this->EntryStatus=$arg;
    }
    function setNextUser($arg)    {$this->NextUser=$arg;}
    
    function getUser ()         {return $this->User;}
    function getEntry ()        {return $this->Entry;}
    function getNextEntry ()    {return $this->NextEntry;}
    function getNSlots ()       {return $this->NSlots;}
    function getStartTime ()    {return $this->StartTime;}
    function getTag ()          {return $this->Tag;}
    function getStartDate ()    {return $this->StartDate;} // first day of the week
    function getRepeat ()       {return $this->Repeat;}
    function getEntryStatus ()  {return $this->EntryStatus;}
    function getNextUser()       {return $this->NextUser;}
    /**
     * 
     * @method: tagging a calendar cell with slottype in arguments : 0 without entry, 1 with regular entry, 2 pre-reserve,3 deleted,4 monitored, 9 (not defined) for error status (eg did not confirm)
     * @param: resource
     */ 
    function designSlot ($slotType) {
        $cellbgLight='#ffffff';
        // case there is an entry
        if ($this->EntryStatus==1) $cellbgStrong=cal::RegCellColorOn;
        if ($this->EntryStatus==2) $cellbgStrong=cal::PreCellColorOn;
        if ($this->EntryStatus==4) $cellbgStrong=cal::MonCellColorOn;
        if ($this->EntryStatus==9) $cellbgStrong=cal::ErrCellColorOn;
        
        if ($this->EntryStatus==1) $cellbgLight=cal::RegCellColorOff;
        if ($this->EntryStatus==2) $cellbgLight=cal::PreCellColorOff;
        if ($this->EntryStatus==4) $cellbgLight=cal::MonCellColorOff;
        if ($this->EntryStatus==9) $cellbgLight=cal::ErrCellColorOff;
        if ($this->EntryStatus==5) $cellbgLight=cal::InUseCellColorOff;
        $cellgrey="#aaaaaa";
        
        switch ($slotType){
        case 0: // without entry
            $extra="OnMouseOver='swapColor(this,0,0);' OnMouseDown='swapColor(this,1,0);'";
            break;
        case 1: // with entry
            $extra="OnMouseDown='swapColor(this,1,1);'";
            if ($this->getRepeat()!='') {
                $extra = $extra . " style='background:$cellbgStrong'";
            } else {
                $extra = $extra . " style='background:$cellbgLight'";
            }
            break;
        case 2: // update
            $extra="OnMouseOver='swapColor(this,0,0);' OnMouseDown='swapColor(this,1,0);'";
            $extra = $extra . " style='background:$cellgrey'";
            break;
        }
        
        echo "<td align=center lang=". $cellbgLight . " title=" . $this->Entry ." width=10% " . $extra ." rowspan=". $this->NSlots .">". $this->Tag ."</td>\n";        
        
    }    
}

/**
  * @author Nuno Moreno
  * @copyright 2009-2010 Nuno Moreno
  * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
  * @version 1.0
  * @abstract: the engine for building the calendar. It does it all!
  *
*/

class cal extends phpCollection{
    
    private $Duration=1;
    private static $Resolution;
    private $StartTime;
    private $EndTime;
    
    private $Day;
    private $Resource;
    private $StartDate;
    private $SlotStart;
    private $Slot=array();
    private $MaxSlots;
    private $CalRepeat;
    private static $ConfTolerance;
    private $ResourceName;
    private $ResourceImage;
    private $Status;
    private $StatusName;
    private $Link;
    private $RespName;
    private $RespEmail;
    private $DelTolerance;
    private $Update;
    private $Price;
    
    const RegCellColorOn= '#e3f8a1';
    const RegCellColorOff= '#e4efc2';
    
    const PreCellColorOn= '#f9f4a6';
    const PreCellColorOff= '#f8f6cf';

    const MonCellColorOn= '#afdde5';
    const MonCellColorOff= '#d7e9ec';

    const ErrCellColorOn='#f39ea8';
    const ErrCellColorOff='#f8dada';
    
    const InUseCellColorOff='#fbc314';
   
    //private $ResStatus;
    function __construct ($Resource,$update=0){
		// require_once("__dbHelp.php");
        $sql="select * from resource,resstatus,".dbHelp::getSchemaName().".user where resource_status=resstatus_id and user_id=resource_resp and resource_id=" . $Resource;
        $res=dbHelp::query($sql) or die ($sql);
        $arrresource= dbHelp::fetchRowByName($res);
		$this->setResource($Resource);
        $this->setStartTime($arrresource['resource_starttime']);
        $this->setEndTime($arrresource['resource_stoptime']);
        self::$Resolution=$arrresource['resource_resolution'];
        $this->setMaxSlots($arrresource['resource_maxslots']);
        self::$ConfTolerance=$arrresource['resource_confirmtol'];
        $this->ResourceName=$arrresource['resource_name'];
        $this->Status=$arrresource['resource_status'];
        $this->StatusName=$arrresource['resstatus_name'];
        $this->Link=$arrresource['resource_wikilink'];
        $this->RespEmail=$arrresource['user_email'];
        $this->RespName=$arrresource['user_firstname']. " " . $arrresource['user_lastname'];
        $this->DelTolerance=$arrresource['resource_delhour'];
        $this->Update=$update;
        $this->Price=$arrresource['resource_price'];
        $sql="SELECT DISTINCT pics_path FROM pics WHERE pics_resource=".$arrresource['resource_id'];
        $res=dbHelp::query($sql) or die ($sql);
        $arrresource= dbHelp::fetchRowByName($res);
        $this->ResourceImage=$arrresource['pics_path'];
    }
    function setStartTime($arg) {$this->StartTime=$arg;$this->SlotStart=$this->StartTime;}
    function setEndTime($arg) {$this->EndTime=$arg;}
    function setResource($arg) {$this->Resource=$arg;}
    function setStartDate($arg) {$this->StartDate=$arg;}
    function setEntry($arg) {$this->Entry=$arg;}
    function setMaxSlots($arg) {$this->MaxSlots=$arg;}
    function setCalRepeat($arg) {$this->CalRepeat=$arg;}
    //function setResStatus($arg) {$this->ResStatus=$arg;}

    function getStartDate() {return $this->StartDate;}
    function getEntry() {return $this->Entry;}
    function getResource() {return $this->Resource;}
    function getStartTime() {return $this->StartTime;}
    function getEndTime() {return $this->EndTime;}
    function getMaxSlots() {return $this->MaxSlots;}
    function getCalRepeat() {return $this->CalRepeat;}
    function getResourceName() {return $this->ResourceName;}
    function getStatus() {return $this->Status;}
    function getStatusName() {return $this->StatusName;}
    function getLink() {return $this->Link;}
    function getRespEmail() {return $this->RespEmail;}
    function getRespName() {return $this->RespName;}
    function getDelTolerance() {return $this->DelTolerance;}
    function getPrice() {return $this->Price;}
    function getResourceImage() {return $this->ResourceImage;}
    
    public final static function getConfTolerance() {return self::$ConfTolerance;}
    public final static function getResolution() {return self::$Resolution;}
    function getResStatus() {return $this->ResStatus;}
    
/**
  * @author Nuno Moreno
  * @copyright 2009-2010 Nuno Moreno
  * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
  * @version 1.0
  * @abstract: Week drawer method
  * @param $resource
*/
    function draw_week(){
        $this->Slot = array_fill(0, ($this->EndTime-$this->SlotStart)/(self::$Resolution/60), array_fill(0, 8, 0));
        $day=substr($this->StartDate,6,2);
        $month=substr($this->StartDate,4,2);
        $year=substr($this->StartDate,0,4);
        $weekahead=mktime(0,0,0,$month, $day+7,$year);
        $weekbefore=mktime(0,0,0,$month, $day-7,$year);
        $updatecount=0;
        
        echo "<table class=calendar id=caltable align=center><tr><th>";
        echo "<font size=1 >". date("M Y",mktime(0,0,0,$month,$day,$year));
        echo "<br><a href=weekview.php?resource=" . $this->getResource() . "&date=". date("Ymd",$weekbefore) . ">";
        echo "<img width=12px height=12px  src=pics/left.gif border=0>&nbsp;</a>";
        echo "<a href=weekview.php?resource=" . $this->getResource() . "&date=". date("Ym").(date("d")-date("N")) . ">";
        echo "<img width=10px src=pics/today.gif border=0>&nbsp;</a>";
        echo "<a href=weekview.php?resource=" . $this->getResource() . "&date=". date("Ymd",$weekahead) . ">";
        echo "<img width=12px height=12px src=pics/right.gif border=0></a>";
        
        echo "</th>"; 
        for ($i=1;$i<8;$i++) {
            $extra='';
            if (date('Ymd',mktime(0,0,0,$month,$day+$i,$year ))==date('Ymd')) $extra ="style='color:#bb3322'";
            echo "<th $extra>" . date("d-D",mktime(0,0,0,$month,$day+$i,$year)) . "</th>";
        }
        //echo "<th>" date("d", $this). "Monday</th>";
        //<th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th><th>Sunday</th>";
        $this->SlotStart=$this->StartTime;
        
        $nline=0;
        $ncells=0;
		$sqlDate1 = dbHelp::getFromDate('entry_datetime','%Y%m%d');
		$sqlDate2 = dbHelp::getFromDate('entry_datetime','%H%i');
		$schemaName = dbHelp::getSchemaName();
		// to catch entries in use and stop them from being seen on weekview if theres already a "normal" entry in a specific time for resource type = quickscheduling
		$hourMinArray = array();
        while ($nline<($this->EndTime-$this->StartTime)/(self::$Resolution/60)) {
            echo "<tr>";
            //$this->SlotStart=$this->StartTime+ self::$Resolution*$nline;
            $this->SlotStart=mktime($this->StartTime,self::$Resolution*$nline);
            $this->Duration=self::$Resolution;
            //$from=floor($this->SlotStart) . "." . ($this->SlotStart - floor($this->SlotStart))*60;
            //$to=floor($this->SlotStart + $this->Duration) . "." . ($this->SlotStart + $this->Duration-floor($this->SlotStart + $this->Duration))*60;
            $from=date("H.i",$this->SlotStart);
            $to=date("H.i",mktime($this->StartTime,self::$Resolution*($nline+1)));
            //$to=date("H.i",mktime($this->StartTime+self::$Resolution*($nline+1),0));
            //$txt= number_format($from,2) . "-" . number_format($to,2);
            $txt=$from."-".$to;
            echo "<td align=center width=10% class=date >". $txt ."</td>\n";

			// $sql= "select user_login,entry_id,entry_user,entry_repeat, entry_status,entry_slots from entry,".dbHelp::getSchemaName().".user where entry_status<>3 and entry_resource=" . $this->getResource() ." and user_id=entry_user and ".dbHelp::getFromDate('entry_datetime','%Y%m%d')."='". $this->Day . "' and ".dbHelp::getFromDate('entry_datetime','%H%i')."='" . date('Hi',$this->SlotStart) . "' order by entry_id";
           for($weekday=1;$weekday<8;$weekday++){
                    //start day always a monday
                    $cell= new calCell;
                    $this->Day=date("Ymd",mktime(0,0,0,substr($this->StartDate,4,2),substr($this->StartDate,6,2)+$weekday,substr($this->StartDate,0,4)));
                    // $sql= "select user_login,entry_id,entry_user,entry_repeat, entry_status, entry_slots from entry,".$schemaName.".user where entry_status<>3 and entry_resource=" . $this->getResource() ." and user_id=entry_user and ".$sqlDate1."='". $this->Day . "' and ".$sqlDate2."='" . date('Hi',$this->SlotStart) . "' order by entry_id";
					if($oneTooMany){
						$sql= "select user_login,entry_id,entry_user,entry_repeat, entry_status, entry_slots, resource_status from entry,".$schemaName.".user, resource where resource_id = entry_resource and entry_status<>3 and entry_resource=" . $this->getResource() ." and user_id=entry_user and ".$sqlDate1."='". $this->Day . "' and ".$sqlDate2."='" . date('Hi',$otherStartTime) . "' order by entry_id";
						$res=dbHelp::query($sql) or die ($sql);
						$arr= dbHelp::fetchRowByName($res);
						$arr['entry_slots']--;
					}
					else{
						$sql= "select user_login,entry_id,entry_user,entry_repeat, entry_status, entry_slots, resource_status from entry,".$schemaName.".user, resource where resource_id = entry_resource and entry_status<>3 and entry_resource=" . $this->getResource() ." and user_id=entry_user and ".$sqlDate1."='". $this->Day . "' and ".$sqlDate2."='" . date('Hi',$this->SlotStart) . "' order by entry_id";
						$res=dbHelp::query($sql) or die ($sql);
						$arr= dbHelp::fetchRowByName($res);
					}
					
                    $cell->setStartDate($this->Day);
                    if ($arr['entry_id']!='') {
					
						// for resource status = quick scheduling, to prevent "regular" entries from becoming overlapped by "in use" ones
						$oneTooMany = false;
						if($arr['resource_status'] == 5){
							if(isset($hourMinArray[$weekday.date('Hi',$this->SlotStart)])){
								$oneTooMany = true;
								$otherStartTime = mktime($this->StartTime,self::$Resolution*($nline));
								$hourMinArray[$weekday.date('Hi',$this->SlotStart)] = null;
								continue;
							}
							else{
								$entryEndedAt = $weekday.date('Hi',mktime($this->StartTime,self::$Resolution*($nline + (int)$arr['entry_slots'] - 1)));
								$hourMinArray[$entryEndedAt] = $arr['entry_status'];
							}
						}
						// ************************************************************************************************************
						
                        $cell->setNSlots($arr['entry_slots']);
                        $cell->setEntry($arr['entry_id']);
                        if ($arr['entry_repeat']==$this->CalRepeat) $cell->setRepeat($this->CalRepeat);
                        $cell->setUser($arr['user_login']);
                        $cell->setStartTime($this->SlotStart);
                        if (dbHelp::numberOfRows($res)>1){
                            // mysql_data_seek($res,1);
                            // $arr= dbHelp::fetchRowByName($res);
                            //$cell->setStatus(4);
                            $cell->setNextUser($arr['user_login']);
                            $cell->setNextEntry($arr['entry_id']);
                        }
                        $cell->setEntryStatus($arr['entry_status']);
                        if ($this->Update!=$cell->getEntry()){
                            //$cell->setTag("<a onmouseover=\"ShowContent('DisplayUserInfo'," . $cell->getEntry() . ")\ onmouseout=\"HideContent('DisplayUserInfo')\" href=weekview.php?resource=" . $this->Resource . "&entry=" . $cell->getEntry(). ">" . $cell->getUser() ."</a><br><a onmouseover=\"ShowContent('DisplayUserInfo'," . $cell->getNextEntry() . ")\" onmouseout=\"HideContent('DisplayUserInfo')\" href=#>" . $cell->getNextUser() ."</a>" );
                            $cell->setTag("<li style='list-style:none;padding:1px;cursor:pointer;' onmouseover=\"ShowContent('DisplayUserInfo'," . $cell->getEntry() . ")\" onmouseout=\"HideContent('DisplayUserInfo')\">" . $cell->getUser() ."<br><li style='list-style:none;cursor:pointer' onmouseover=\"ShowContent('DisplayUserInfo'," . $cell->getNextEntry() . ")\" onmouseout=\"HideContent('DisplayUserInfo')\">" . $cell->getNextUser() ."</li>" );
                            for ($j=0;$j<$cell->getNSlots();$j++){
								$this->Slot[$nline+$j][$weekday]=1;
							}
                            $cell->designslot(1);
							$this->add($cell->getEntry(),$cell);
                        } else {
                            $updatecount=$cell->getNSlots()-1;
                            $cell->setEntry(0);
                            $updDay=$this->Day;
                            $cell->setNSlots(1); 
                            $cell->designSlot(2);
                            $this->add($ncells. "empty",$cell);
                        }
                        //$nline=$nline+$cell->getNSlots()-1;
                        
                    } else {
                        $cell->setNSlots(1);
                        $cell->setEntry('0');
                        $cell->setUser('');
                        $cell->setStartTime($this->SlotStart);
                        $cell->setTag('');
                        if ($this->Slot[$nline][$weekday]!=1)
                            if ($this->Update==0) {
                                $cell->designSlot(0);
                            } else {
                                if ($updatecount>0 and $this->Day==$updDay) {
                                    //$cell->setTag($updatecount);
                                    $cell->designSlot(2);
                                    $updatecount=$updatecount-1;
                                } else {
                                    $cell->designSlot(0);
                                };
                            }
                        $this->add($ncells. "empty",$cell);
                    } //case it doesn't have an entry
                    //echo $cell->getEntry();
                    //$cell->designSlot();
                    
                    $ncells=$ncells+1;    
                //} //case not calendar hours
			} // end week days
            $nline=$nline+1;
            echo "</tr>";
        } // end of time
        echo "</table>";
    }
	
	public function monitor($resource){
    	$sql="SELECT DISTINCT equip_resourceid FROM equip, resource WHERE resource_id=equip_resourceid AND resource_name='$resource'";
    	$res=dbHelp::query($sql) or die ($sql);
        if(dbHelp::numberOfRows($res)==0){
        	return false;
        } else {
        	return true;
        }
    }

}

?>