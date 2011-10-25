<?php 

require_once "../Datumo2.0/.htconnect.php";

if(isset($_GET['type'])){
	$type=(int)$_GET['type'];
	switch($type){
		case 0:
			resourceDisplay();
			break;
		case 1:
			$ld=loadInfo((int)$_GET['equip'], (int)$_GET['resource'], (int)$_GET['time']);
			echo $ld;
			break;
		case 2: 
			limits();
	}
}


function resourceDisplay(){
	$conn=new dbConnection();
	
	//url variables
	if(isset($_GET['resource']))	$resource_id=(int)$_GET['resource'];
	$query="SELECT board_address, user_login, user_email, resource_status
	FROM board, resource, user, equip 
	WHERE user_id=equip_user 
	AND equip_boardid=board_id 
	AND equip_resourceid=resource_id 
	AND resource_id='$resource_id' 
	GROUP BY board_address";
	$sql=$conn->query($query);
	$row=$sql->fetch();
	echo "<table class=display cellpadding=3px>";
	echo "<tr><td><b>Board Address</td><td>$row[0]</td></tr>";
	echo "<tr><td><b>Responsible</b></td><td><a href=mailto:$row[2]>$row[1]</a></td></tr>";
	if($row[3]!=2)
		echo "<tr><td colspan=2><a href=http://calendar.igc.gulbenkian.pt/weekview.php?resource=$resource_id target=_blank>View schedule</a></td></tr>";
	echo "</table>";
	monitoredParams($resource_id);
}

function monitoredParams($resource_id){
	$conn=new dbConnection();
	$query="SELECT equip_id, parameter_type, equip_desc
	FROM resource, parameter, equip 
	WHERE equip_para=parameter_id
	AND equip_resourceid=resource_id 
	AND resource_id='$resource_id'";	
	$sql=$conn->query($query);
	echo "<table class=display cellpadding=3px>";
	//set the number of plots into a variable
	//$noPlots=$sql->rowCount();
	$noPlots=0;
	for($i=0;$row=$sql->fetch();$i++){
		//Parameters to check
		if($row[2]!="NA"){
			$check="checked";
			$noPlots++;
		} else {
			$check="";
		}
		echo "<tr>";
		echo "<td><input type=checkbox name=".($i+1)." id=".($i+1)." $check onclick=getValuesToPlot() disabled></td>";
		echo "<td>$row[1]</td>";
		echo "<td><input type=text class=params name=tag_".($i+1)." id=tag_".($i+1)." value='$row[2]' readonly></td>";
		echo "</tr>";
	}
	echo "</table>";
	drawPlots($noPlots);
}

function drawPlots($noPlots){
	echo "<div class=plots_holder>";
	//echo "<table border=1><tr>";
	//increment counter to draw all plots
	for($i=0;$i<$noPlots;$i++){
		echo "<div id=plot_".($i+1)." class=plots_div></div>";
	}
	//echo "</tr></table>";
	echo "</div>";	
}

function loadInfo($parameter_id, $resource_id, $tm){	
	//call database class
	$conn=new dbConnection();
	
	//initialize local variables
	$calc = 288 * $tm;		
	$status=resourceStatus($resource_id); //check resource type -> is it available for reservation?
	$query="SELECT measure_value, measure_date, equip_calibration 
		FROM measure, equip, parameter 
		WHERE measure_equip = equip_id 
		AND parameter_id = equip_para 
		AND equip_resourceid = '".$resource_id."'
		AND parameter_id = '".$parameter_id."' AND measure_date > NOW() - INTERVAL '".$tm."' DAY 
		ORDER BY measure_id DESC"; 
	$sql=$conn->query($query);
	for($i=0;$row=$sql->fetch();$i++){				
		$time = $row[1];
		$year = (int)$time[0].$time[1].$time[2].$time[3];
		$month = (int)$time[5].$time[6];
		$day = (int)$time[8].$time[9];
		$hour = (int)$time[11].$time[12];
		$minute = (int)$time[14].$time[15];
		$sec = (int)$time[17].$time[18];
		// first correct the timestamps - they are recorded as the daily
		// midnights in UTC+0100, but Flot always displays dates in UTC
		// so we have to add one hour to hit the midnights in the plot
		$value = $row[0];
		$cval = $row[2];
		eval ("\$var = ".str_replace(':','$',$cval).";");
		
		$timestamp = mktime($hour,$minute,$day,$month,$day,$year);
		$json->measure[]= array($timestamp*1000, $var);
		if($status!=0 and $status!=2){ //resource is available for reservation
			$query2="SELECT user_login, entry_datetime FROM entry, resource,user 
				WHERE entry_resource=resource_id
				AND user_id=entry_user
				AND entry_datetime BETWEEN DATE_SUB('$time', INTERVAL (entry_slots*resource_resolution) minute)
				AND '$time'
				AND resource_id=$resource_id
				AND entry_status IN (1,2)";
			try{
				$sql2=$conn->query($query2);
				if($sql2->rowCount()>0){
					$a=1500;
				} else {
					$a=0;
				}
			} catch (Exception $e){
				echo $e->getMessage();
			}
			
			$json->entry[]=array($timestamp*1000, $a);
		}
	}
	$str = json_encode($json);
	return $str;
}

function limits(){
	//call database class
	$conn=new dbConnection();
	//url variables
	if(isset($_GET['resource']))	$resource=(int)$_GET['resource'];
	if(isset($_GET['param']))	$parameter_id=(int)$_GET['param'];
	
	$query="SELECT equip_min, equip_max, equip_calibration, resource_type 
	FROM equip, resource
	WHERE equip_resourceid=resource_id 
	AND equip_resourceid='$resource' 
	AND equip_para=$parameter_id";
	
	$sql=$conn->query($query);
	//echo $sql->queryString;
	$row=$sql->fetch();
	if($row[3]==10){ //environmental control resource
		$value = $row[0];
		$cval = $row[2];
		eval ("\$var = ".str_replace(':','$',$cval).";");
		if($parameter_id<5)
			$row['equip_max']=$var;
		else 
			$row['equip_min']=$var;
		$value = $row[1];
		eval ("\$val = ".str_replace(':','$',$cval).";");
		if($parameter_id<5)
			$row['equip_min']=$val;
		else 
			$row['equip_max']=$val;
	} else {	//resource that is set for reservation
		$row['equip_min']=0;
		$row['equip_max']=5000;
	}
	echo json_encode($row);
}

function resourceStatus($resource_id){
	//call database class
	$conn=new dbConnection();
	$query="SELECT resource_status FROM resource WHERE resource_id=$resource_id";
	$sql=$conn->query($query);
	$row=$sql->fetch();
	return $row[0];
}


?>