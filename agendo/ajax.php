<?php
//session_start();	
// This class was altered by Pedro Pires (The chosen two)
	require_once("commonCode.php");
	require_once("functions.php");
	// initSession();
?>


<?php

/*
  @author Nuno Moreno
  @copyright 2009-2010 Nuno Moreno
  @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
  @version 1.0
  @ ajax request handler
*/

// require_once(".htconnect.php");

$type=$_GET['type'];
//echo $action;
call_user_func($type);

/**
   * returns resource name and id. This is sent between tags that are later (on javascript) separated for creating the dropdown list. It could be done in a much more elegant way
*/
function resource() {
    $value = (int)cleanValue($_GET['value']);
    // $res=dbHelp::query("select resource_id,resource_name from resource where resource_status<>2 and resource_type=" . $value);
    $res=dbHelp::query("select resource_id,resource_name from resource where resource_status<>2 and resource_type = :0", array($value));
    while($arr=dbHelp::fetchRowByName($res)){
        echo "<name>" . $arr['resource_name'];
        echo "<value>" . $arr['resource_id'];
    }
}

/**
   * @abstract returns user login for text input autofill.
   * @return ->name and id are returned separated by |
   * @todo It should be done using an object model. Waiting for version 2
*/

function user() {
    $value = cleanValue($_GET['value']);
    // $sql="select user_login, user_id from ".dbHelp::getSchemaName().".user where user_login like '".$value."%'";
    $sql="select user_login, user_id from ".dbHelp::getSchemaName().".user where user_login like :0";
    // $res=dbHelp::query($sql) or die ($sql);
    $res=dbHelp::query($sql, array($value.'%')) or die ($sql);
    $arr=dbHelp::fetchRowByIndex($res);
    echo $arr[0];
    // echo "|" . $arr[1];
    echo "|" . $arr[0];
}

/**
   * calls recover password method for the selected user id
*/
function newpwd(){
    require_once("alertClass.php");
    $alert= new alert;
    $alert->recover($_GET['value']);
}

/**
   * text input autofill for administrating table. 
*/

/*
function admin() {
    $value=cleanValue($_GET['value']);
    $tag=cleanValue($_GET['tag']);
    $table=cleanValue($_GET['table']);
	
    // $sql="show fields from $table";
    $sql="show fields from ".$table;
    // $res=dbHelp::query($sql) or die ($sql);
    $res=dbHelp::query($sql, array($table)) or die ($sql);
    $field1=dbHelp::fetchRowByIndex($res);
    $field2=dbHelp::fetchRowByIndex($res);
    
    $sql="select ".$field2[0].", ".$field1[0]." from ".$table." where lower(".$field2[0].") like lower('".$value."%')";
    $res=dbHelp::query($sql) or die ($sql);
    $arr=dbHelp::fetchRowByIndex($res);
    echo $arr[0];
    echo "|" . $arr[1];
}
*/

function DisplayUserInfo() {
    $value=cleanValue($_GET['value']);
    // $sql="select concat(user_firstname, ' ', user_lastname) name,user_email,user_mobile,user_phone,user_phonext,department_name,institute_name,date_format(entry_datetime,'%H:%i') s,date_format(date_add(entry_datetime,interval resource_resolution*entry_slots minute),'%H:%i') e from ".dbHelp::getSchemaName().".user,entry,department,institute,resource where user_dep=department_id and department_inst=institute_id and entry_user=user_id and entry_resource=resource_id and entry_id=" . $value;
    // $sql="select user_firstname,user_lastname,user_email,user_mobile,user_phone,user_phonext,department_name,institute_name,date_format(entry_datetime,'%H:%i') s,date_format(date_add(entry_datetime,interval resource_resolution*entry_slots minute),'%H:%i') e from ".dbHelp::getSchemaName().".user,entry,department,institute,resource where user_dep=department_id and department_inst=institute_id and entry_user=user_id and entry_resource=resource_id and entry_id=" . $value;
	// $sqlAux = "select resource_resolution,entry_slots from ".dbHelp::getSchemaName().".user,entry,department,institute,resource where user_dep=department_id and department_inst=institute_id and entry_user=user_id and entry_resource=resource_id and entry_id=" . $value;
	// $res=dbHelp::query($sqlAux) or die ($sqlAux);
    $sqlAux = "select resource_resolution,entry_slots from ".dbHelp::getSchemaName().".user,entry,department,institute,resource where user_dep=department_id and department_inst=institute_id and entry_user=user_id and entry_resource=resource_id and entry_id= :0";
    $res=dbHelp::query($sqlAux, array($value)) or die ($sqlAux);
    $arr=dbHelp::fetchRowByIndex($res);
	
    // $sql="select user_firstname,user_lastname,user_email,user_mobile,user_phone,user_phonext,department_name,institute_name,".dbHelp::getFromDate('entry_datetime','%H:%i')." as s,".dbHelp::getFromDate(dbHelp::date_add('entry_datetime',$arr[0]*$arr[1],'minute'),'%H:%i')." as e from ".dbHelp::getSchemaName().".user,entry,department,institute,resource where user_dep=department_id and department_inst=institute_id and entry_user=user_id and entry_resource=resource_id and entry_id=" . $value;
    // $res=dbHelp::query($sql) or die ($sql);
	$style = "
		padding:5px;
		background-color: white;
		-moz-border-radius: 3px;  /* Firefox */
		-webkit-border-radius: 3px;  /* Safari, Chrome */
		border-radius: 3px;  /* CSS3 */
		-moz-box-shadow: 3px 3px 4px #000;
		-webkit-box-shadow: 3px 3px 4px #000;
		box-shadow: 3px 3px 4px #000;
	";
    $sql="select user_firstname,user_lastname,user_email,user_mobile,user_phone,user_phonext,department_name,institute_name,".dbHelp::getFromDate('entry_datetime','%H:%i')." as s,".dbHelp::getFromDate(dbHelp::date_add('entry_datetime',$arr[0]*$arr[1],'minute'),'%H:%i')." as e from ".dbHelp::getSchemaName().".user,entry,department,institute,resource where user_dep=department_id and department_inst=institute_id and entry_user=user_id and entry_resource=resource_id and entry_id=:0";
    $res=dbHelp::query($sql, array($value)) or die ($sql);
    $arr=dbHelp::fetchRowByIndex($res);
    echo "<table style='".$style."'>";
    echo "<tr><td>Time: </td><td>" . $arr[8] ."-" .$arr[9] ."</td></tr>";
    echo "<tr><td>Name: </td><td>" . $arr[0] . " " . $arr[1] . "</td></tr>";
	
	// Only show this if a user is logged
	if(isset($_SESSION['user_id']) || $_SESSION['user_id']!= ''){
		echo "<tr><td>Email: </td><td>" . $arr[2] . "</td></tr>";
		echo "<tr><td>Mobile: </td><td>" . $arr[3] . "</td></tr>";
		echo "<tr><td>Phone: </td><td>" . $arr[4] . "</td></tr>";
		echo "<tr><td>Phone ext: </td><td>" . $arr[5] . "</td></tr>";
		echo "<tr><td>Department: </td><td>" . $arr[6] . "</td></tr>";
		echo "<tr><td>Institute: </td><td>" . $arr[7] . "</td></tr>";
	}
	
	$sql = "
		select 
			user_firstname
			,user_lastname 
		from 
			databasename.user
			,entry
			,(select entry_datetime as entryDate, resource_id as res from entry, resource where entry_id = :0) as entryDates
		where 
			entry_user=user_id 
			and entry_resource=res 
			and entry_datetime = entryDate 
			and entry_status = 4 
		order by entry_id
	";
    $res=dbHelp::query($sql, array($value)) or die ($sql);
	$arr=dbHelp::fetchRowByIndex($res);
	if($arr[0] != null){
		// $textColor = "#afdde5";
		echo "<tr><td colspan=2><hr></hr></td></tr>";
		$textColor = "#63a3ae";
		echo "<tr><td style='color:".$textColor.";'>Next user: </td><td style='color:".$textColor.";'>" . $arr[0] . " " . $arr[1] . "</td></tr>";
	}

    echo "</table>";
}

function DisplayEntryInfo() {
    $entry=cleanValue($_GET['entry']);
    // $sql ="select xfields_name, xfieldsval_value, xfields_type, xfields_id from xfieldsval,xfields where xfieldsval_field=xfields_id and xfieldsval_entry=".$entry." and xfields_placement = 1 group by xfields_id, xfields_type";
    // $res=dbHelp::query($sql) or die ($sql);
    $sql ="select xfields_name, xfieldsval_value, xfields_type, xfields_id from xfieldsval,xfields where xfieldsval_field=xfields_id and xfieldsval_entry=:0 and xfields_placement = 1 group by xfields_id, xfields_type";
    $res=dbHelp::query($sql, array($entry)) or die ($sql);
    
	while($arr=dbHelp::fetchRowByIndex($res)){
		if($arr[2] == 2 || $arr[2] == 3)
			echo "document.getElementById('".$arr[0]."-".$arr[3]."').checked=".$arr[1].";";
		else
			echo "document.getElementById('".$arr[0]."-".$arr[3]."').value='".$arr[1]."';";
    }
}

?>