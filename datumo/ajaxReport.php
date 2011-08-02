<?php
require_once("session.php");
$user_id = startSession();

/**
 * @author João Lagarto	/ Nuno Moreno
 * @abstract Ajax handler request -> get referenced table
 * 
 *  */

require_once("__dbConnect.php");

if(isset($_GET['type'])){
	$type=$_GET['type'];
	switch ($type){
		case 0:
			fields();
			break;
		case 1:
			autoSuggest();
			break;
		case 2:
			testQuery();
			break;
		case 3:
			clauses();
			break;
		case 4:
			reportInfo($user_id);
			break;
		case 5:
			createReport($user_id);
			break;
		case 6:
			inputParameters();
			break;		
		case 7:
			infoTable();
			break;			
	}

}

function fields(){
	//styles and javascripts
	echo "<link href='css/main.css' rel='stylesheet' type='text/css'>";
	echo "<link href='css/reports.css' rel='stylesheet' type='text/css'>";
	echo "<script type='text/javascript' src='js/jquery.reports.js'></script>";
	echo "Select attributes to display<br>";
	echo "<table>";
	echo "<tr>";
	//cloning rows
	echo "<td id=clone><a href=javascript:void(0) style='text-decoration:none;font-size:10px;' class=cloneMe onclick=\"javascript:multiFields('sum', this, 'multiple');\" title='clone row'>Add</a></td>";
    echo "<td id=delete><a href=javascript:void(0) style='text-decoration:none;font-size:10px;' class=deleteMe onclick=\"javascript:multiFields('subtract', this, 'multiple');\" title='cancel row'>Remove</a></td>";
	echo "<td><input type=text id=field name=field lang=__fk class=field></td>";	//field text input
	echo "<td> AS </td>";
	echo "<td><input type=text id=mask name=mask class=mask></td>";	//mask text input
	echo "</tr>";
	echo "</table>";
	echo "<input type=hidden id=multiple name=multiple value=0>";	//hidden textbox to control the number of attributes
	echo "<div class=next>";
	echo "<a href=javascript:void(0) id=nextClauses>Next</a>";
	echo "&nbsp;&nbsp;&nbsp";
	echo "<a href=javascript:void(0) id=nextParameters>Add input parameters</a>";
	echo "&nbsp;&nbsp;&nbsp";
	echo "<a href=javascript:void(0) id=finishQuery>Finish</a>";
	echo "</div>";
	echo "<hr>";
}

function autoSuggest(){
	header('Content-type: text/html; charset=UTF-8');
	require_once "queryClass.php";
	//database class
	$conn=new dbConnection();
	$qClass=new queryClass();
	//initialize local variables
	$s=""; 
	//$p="";
	//url variable
	if(isset($_REQUEST['query'])) $q=$_REQUEST['query'];
	else $q="";
	if(isset($_GET['arr'])){
		$arr=$_GET['arr'];
		$arr=explode(",",$arr);
		foreach($arr as $table){
			$s.="'$table',";
		}
		$arr=substr($s,0,strlen($s)-1);
	}
	//set search path to information schema
	$conn->dbInfo();
	$query="SELECT column_name FROM columns WHERE table_name IN ($arr) AND table_schema='".$conn->getDatabase()."' AND LOWER(column_name) regexp LOWER ('$q') LIMIT 25";
	
	//using try catch clause to avoid error display through the autosuggest
	try{
		$sql=$conn->query($query);
		echo '<ul>'."\n";
		if ($sql->rowCount()>0)	{
		    for($i=0;$row=$sql->fetch();$i++){
				//$p = $row[0];
				//highlight matching characters
				//$p = preg_replace('/(' . $q . ')/i', '<span style="font-weight:bold;">$1</span>', $p);
				echo "\t<li id=autocomplete_.$row[0]. rel=$row[0]>".utf8_encode($row[0])."</li>\n";
		    }   
		} else {
			echo "No results!";
		}
		echo '</ul>';	
	} catch (Exception $e){
		//do nothing
	}
	
}

function testQuery(){
	//database class
	$conn=new dbConnection();
	//other classes
	$qClass=new queryClass();
	//url variables
	if(isset($_GET['tables'])){
		$objName=$_GET['tables'];
		$objName_string=implode(",",$objName);
	}
	if(isset($_GET['fields'])){
		$fields=$_GET['fields'];
		$fields_string=implode(",",$fields);
	}
	if(isset($_GET['masks'])){
		$masks=$_GET['masks'];
	}
	$query="SELECT $fields_string FROM $objName_string";
	echo $query;
	try{
		$sql=$conn->query($query);
		echo true;
	} catch (Exception $e){
		echo false;
	}
}

function clauses(){
	//styles and javascripts
	echo "<link href='css/main.css' rel='stylesheet' type='text/css'>";
	echo "<link href='css/reports.css' rel='stylesheet' type='text/css'>";
	echo "<script type='text/javascript' src='js/jquery.reports.js'></script>";
	//url variables
	if(isset($_GET['fields'])){
		$fields=$_GET['fields']; 				//array
		$fields_string=implode(",",$fields);	//string
	}
	echo "Create clauses to filter data (table relations are automatically created)<br>";
	echo "<table>";
	echo "<tr>";
	//cloning rows
	echo "<td id=clone><a href=javascript:void(0) style='text-decoration:none;font-size:10px;' class=cloneMe onclick=\"javascript:multiFields('sum', this, 'multiple_clause');\" title='clone row'>Add</a></td>";
    echo "<td id=delete><a href=javascript:void(0) style='text-decoration:none;font-size:10px;' class=deleteMe onclick=\"javascript:multiFields('subtract', this, 'multiple_clause');\" title='cancel row'>Remove</a></td>";
	echo "<td><input type=text id=clause name=clause class=clause size=50></td>";	//field text input
	//operator options (AND, OR)
	echo "<td><select name=op id=op class=op>";
	echo "<option value=AND>AND</option>"; 		
	echo "<option value=OR>OR</option>";
	echo "</select></td>";
	echo "</tr>";
	echo "</table>";
	//hidden textbox to control the number of attributes
	echo "<input type=hidden id=multiple_clause name=multiple_clause value=0>";	
	echo "<div class=next>";
	echo "<a href=javascript:void(0) id=nextParameters2>Add input parameters</a>";
	echo "&nbsp;&nbsp;&nbsp";
	echo "<a href=javascript:void(0) id=finishQuery2>Finish</a>";
	echo "</div>";
	echo "<hr>";
}

function reportInfo($user_id){
	//styles and javascripts
	echo "<link href='css/main.css' rel='stylesheet' type='text/css'>";
	echo "<link href='css/reports.css' rel='stylesheet' type='text/css'>";
	echo "<script type='text/javascript' src='js/jquery.reports.js'></script>";
	//include
	require_once "resClass.php";
	//database class
	$conn=new dbConnection();
	//other classes
	$perm=new restrictClass();
	echo "Report Information<br>";
	echo "<table>";
	echo "<tr>";
	echo "<td style='font-size:10px;'>Report Name</td><td><input type=text name=report_name id=report_name size=10 maxlength=20></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td style='font-size:10px;'>Report Description</td><td><input type=text name=report_desc id=report_desc size=40 maxlength=150></td>";
	echo "</tr>";
	$perm->userInfo($user_id);
	$admin_level=$perm->getUserLevel();
	if($admin_level==2){ //regular user
		$r="readonly";
	} else {
		$r="";
	}
	echo "<tr>";
	echo "<td style='font-size:10px;'>Confidentiality</td><td><select id=report_conf name=report_conf $r>";
	echo "<option value=2 selected>Private</option>";
	echo "<option value=1 >Public</option>";
	echo "</td></tr>";	
	echo "</table>";
	echo "<div class=next>";
	echo "<a href=javascript:void(0) id=createReport>Create Report</a>";
	echo "</div>";
}

function createReport($user_id){
	//print_r($_GET);
	//call databse class
	$conn=new dbConnection();
	$database=$conn->getDatabase();
	//get url variables to build the query
	if(isset($_GET['tables'])){			//query tables
		$objName=$_GET['tables'];		//array form
		$objName_string=implode(",",$objName);	//tables as a string
	}
	if(isset($_GET['fields'])){			//query fields to display
		$fields=$_GET['fields'];		//array form
		$fields_string=implode(",",$fields);	//displayed as string
	}
	if(isset($_GET['masks'])){			$masks=$_GET['masks'];	}	//field masks
	if(isset($_GET['clauses'])){		//Where clauses
		$clauses=$_GET['clauses'];	
	} else {
		$clauses="";
	}
	if(isset($_GET['op'])){				//query operators
		$op=$_GET['op'];	
	} else {
		$op="";
	}
	if(isset($_GET['params'])){			//input parameters
		$params=$_GET['params'];
	} else {
		$params=null;
	}
	if(isset($_GET['report_name'])){	$report_name=$_GET['report_name'];	}
	if(isset($_GET['report_desc'])){	$report_desc=$_GET['report_desc'];	}
	if(isset($_GET['report_conf'])){	$report_conf=$_GET['report_conf'];	}
	//initialize variables
	$where="";
	$q="";
	//check foreign key relations
	if(sizeof($objName)==1){ //there is only one table -> no need to check for relations
		//do nothing
	} else {
		//get relation clauses
		$rel=checkFK($objName);
		foreach($rel as $row){
			$q.=$row." AND ";
		}
	}
	//loop through all clauses (build main query clause)
	$i=0; 	//start counter
	if($clauses!=""){
		foreach($clauses as $arr){
			if($arr==""){	//don't accept blank clauses
				continue; 
				$i++;	
			}
			//check if this is the last clause
			if($i!=sizeof($clauses)-1)
				$q.=$arr." ".$op[$i]." ";
			else 
				$q.=$arr;
			$i++; 	//increment counter
		}
	}
	//validate where clause
	if($q!=""){
		if(sizeof($clauses)==1 and $clauses[0]=="")
			$q=substr($q,0,strlen($q)-4);	//remove AND operator
		$where=" WHERE $q ";
		
	}
	
	$query="SELECT $fields_string FROM $objName_string $where";
	$query=str_replace("'", "\"", $query);
	$conn->beginTransaction();
	try{
		$sql=$conn->query($query);
		//insert a new report in the database
		$sql=$conn->query("INSERT INTO report VALUES ('','$report_name','$report_desc','$query',$user_id,$report_conf)");
		$report_id=$conn->lastInsertId();
		//loop through all masks
		$j=0;	//start counter
		foreach($masks as $arr){
			if($arr=="")	$arr=$fields[$j];
			$sql=$conn->query("INSERT INTO reprop VALUES ('',$report_id,'$fields[$j]','$arr')");
			$j++;
		}
		//are there any input parameters??
		if($params!=""){
			foreach($params as $arr){	//loop through all input parameters
				$sql=$conn->query("INSERT INTO param VALUES ('',$report_id,'$arr','')");
			}
		}
		$conn->commit();
		echo "Report successfully created";
	}catch(Exception $e){
		$conn->rollBack();
		echo $e->getMessage();
		echo "There's an error in your query. Please verify it. If the problem persists please contact the administrator";
	}
}

function checkFK($objName){
	require_once "queryClass.php";
	//call database
	$conn=new dbConnection();
	//other
	$qClass=new queryClass();
	$conn->dbInfo();
	//loop through all tables
	$clause=array();
	if(sizeof($objName)==2) {	//2 tables selected
		$i=0; 	//start counter
		foreach($objName as $newObj){
			if($i==0)	$table=$objName[1];
			else 		$table=$objName[0];
			$arr = array($conn->getDatabase(),$table,$newObj,''); //table and database
			for($i = 0;$i<sizeof($arr);$i++){
				$qClass->__set($i, $arr[$i]);	
			}
			//select engine (mysql or pgsql)
			$qClass->engineHandler($conn->getEngine());
			//query number 1 -> necessary in order to select specific query from vault
			$sql = $conn->query($qClass->getSQL(8)); 
			if($sql->rowCount()>0){
				$row=$sql->fetch();	
				$row[1]=$row[0]."=".$table."_id";
				$clause[]=$row[1];
			}
			$i++;
		}	
	} elseif(sizeof($objName)==3){	//3 tables selected -> this is fuc&$%&/ hard
		$i=0; 	//initialize counte
		foreach($objName as $newObj){
			$table=array();
			if($i==0){
				$table[0]=$objName[1];
				$table[1]=$objName[2];
			}
			if($i==1){
				$table[0]=$objName[0];
				$table[1]=$objName[2];
			}
			if($i==2){
				$table[0]=$objName[0];
				$table[1]=$objName[1];
			}
			foreach($table as $res){
				$arr = array($conn->getDatabase(),$res,$newObj,''); //table and database
				for($i = 0;$i<sizeof($arr);$i++){
					$qClass->__set($i, $arr[$i]);	
				}
				//select engine (mysql or pgsql)
				$qClass->engineHandler($conn->getEngine());
				//query number 1 -> necessary in order to select specific query from vault
				$sql = $conn->query($qClass->getSQL(8)); 
				if($sql->rowCount()>0){
					$row=$sql->fetch();	
					$row[1]=$row[0]."=".$res."_id";
					$clause[]=$row[1];
				}
			}
			$i++;
		}
	}
	return $clause;	
}

function inputParameters(){
	//styles and javascripts
	echo "<link href='css/main.css' rel='stylesheet' type='text/css'>";
	echo "<link href='css/reports.css' rel='stylesheet' type='text/css'>";
	echo "<script type='text/javascript' src='js/jquery.reports.js'></script>";
	//database class
	$conn=new dbConnection();
	echo "Select input parameters for your report<br>";
	echo "<table>";
	echo "<tr>";
	//cloning rows
	echo "<td id=clone><a href=javascript:void(0) style='text-decoration:none;font-size:10px;' class=cloneMe onclick=\"javascript:multiFields('sum', this, 'multiple_params');\" title='clone row'>Add</a></td>";
    echo "<td id=delete><a href=javascript:void(0) style='text-decoration:none;font-size:10px;' class=deleteMe onclick=\"javascript:multiFields('subtract', this, 'multiple_params');\" title='cancel row'>Remove</a></td>";
	echo "<td><input type=text id=parameters name=parameters class=parameters lang=__fk size=50></td>";	//field text input
	echo "</tr>";
	echo "</table>";
	echo "<input type=hidden name=multiple_params id=multiple_params value=0>";
	echo "<div class=next>";
	echo "<a href=javascript:void(0) id=finishQuery3>Finish</a>";
	echo "</div>";
	echo "<hr>";
}

function getReference($arr){
	require_once "queryClass.php";
	$conn=new dbConnection();
	//change path to information schema
	$conn->dbInfo();
	$qClass=new queryClass();
	
	for($i = 0;$i<sizeof($arr);$i++){
		$qClass->__set($i, $arr[$i]);	
	}
	//select engine (mysql or pgsql)
	$qClass->engineHandler($conn->getEngine());
	$sql = $conn->query($qClass->getSQL(3)); 
	//echo $sql->queryString;
	$row=$sql->fetch();
	//return search path to main database
	$conn->dbConn();
	return $row[0];
}

function infoTable(){
	$conn=new dbConnection();
	//http variables
	if(isset($_GET['tables']) and $_GET['tables']!="")	$objName=$_GET['tables'];
	else{
		echo "No tables selected";
		exit();
	}
	//change search path to information schema
	$conn->dbInfo();
	echo "<table style='font-size:10px;'><tr>";
	echo "<td colspan=".sizeof($objName)."><b>Available attributes</b></td>";
	echo "</tr><tr>";
	//loop through selected tables
	foreach ($objName as $table){
		echo "<td valign=top>";
		echo "<table style='font-size:10px;float:left'>";
		echo "<th>$table</th>";
		$sql=$conn->query("SELECT column_name FROM columns WHERE table_schema='".$conn->getDatabase()."' AND table_name='$table'");
		//loop through all columns
		for($i=0;$row=$sql->fetch();$i++){
			echo "<tr><td>$row[0]</td></tr>";
		}
		echO "</table>";
		echo "</td>";
	}
	echO "</tr></table>";	
	
}

?>