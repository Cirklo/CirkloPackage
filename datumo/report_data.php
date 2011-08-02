<?php 
//include the information needed for the connection to MySQL data base server. 
// we store here username, database and password 
require_once "session.php";
$user_id = startSession();

require_once "__dbConnect.php"; 
require_once "queryClass.php";
require_once "dispClass.php";
$display=new dispClass();
$qClass=new queryClass();
// to the url parameter are added 4 parameters as described in colModel
// we should get these parameters to construct the needed query
// Since we specify in the options of the grid that we will use a GET method 
// we should use the appropriate command to obtain the parameters. 
// In our case this is $_GET. If we specify that we want to use post 
// we should use $_POST. Maybe the better way is to use $_REQUEST, which
// contain both the GET and POST variables. For more information refer to php documentation.
// Get the requested page. By default grid sets this to 1. 
$page = $_GET['page']; 
 
// get how many rows we want to have into the grid - rowNum parameter in the grid 
$limit = $_GET['rows']; 
// get index row - i.e. user click to sort. At first time sortname parameter -
// after that the index from colModel 
$sidx = $_GET['sidx']; 

// sorting order - at first time sortorder 
$sord = $_GET['sord']; 

//table to build query
if(isset($_GET['report_id'])) 		$report_id=$_GET['report_id'];
if(isset($_GET['extra_fields'])){
	$extra_fields=$_GET['extra_fields']; 	//as a string
	$arr_fields=explode(",", $extra_fields);//as an array
}
if(isset($_GET['extra_op'])){
	$extra_op=$_GET['extra_op'];			//as a string
	$arr_op=explode(",",$extra_op);			//as an array
}

// if we not pass at first time index use the first column for the index or what you want
if(!$sidx) $sidx=1; 

$totalrows = isset($_REQUEST['totalrows']) ? $_REQUEST['totalrows']: false; 

if($totalrows) { 
	$limit = $totalrows; 
}



//call database class and connect to database
$conn = new dbConnection();
$database = $conn->getDatabase();

//get query stored in the database
$query="SELECT report_query FROM report WHERE report_id=$report_id";
$sql=$conn->query($query);
$row=$sql->fetch(); 
$query=$row[0];

//handle multiple search
//initialize clause
$clause="";
if(isset($_GET['filters'])){
	$json=$_GET['filters'];
	//decode json array
	$search=json_decode($json);
	//initialize clause operator array
	$qops = array(
				  'eq'=>" = ",
				  'ne'=>" <> ",
				  'lt'=>" < ",
				  'le'=>" <= ",
				  'gt'=>" > ",
				  'ge'=>" >= ",
				  'bw'=>" LIKE ",
				  'bn'=>" NOT LIKE ",
				  'in'=>" IN ",
				  'ni'=>" NOT IN ",
				  'ew'=>" LIKE ",
				  'en'=>" NOT LIKE ",
				  'cn'=>" LIKE " ,
				  'nc'=>" NOT LIKE " );
	
	//get operator (the same operator for all clauses)
	$op=$search->groupOp;
//	print_r($search);
	//find the number of clauses in the filter
	$noClauses=sizeof($search->rules);
	//check if there is any clause in the main query
	if(strpos($query," WHERE ")){	//if exists
		$clause=" $op ";
	} else {	//WHERE clause does not exist
		$clause=" WHERE ";
	}
	//loop through all clauses
	foreach ($search->rules as $key=>$val){
		$clause.=$val->field.$qops[$val->op]."'".$val->data."'";
		$clause.= " $op ";
	}
	//remove the last operator from the string
	$clause=substr($clause,0,strlen($clause)-strlen($op)-1);
}

//search for input parameters
$sql=$conn->query("SELECT param_field FROM param WHERE param_report=$report_id");
//loop through all input parameters
$extra_query="";
for($i=0;$row=$sql->fetch();$i++){
	if(isset($arr_fields[$i])){
		if($arr_fields[$i]=="") 	continue;	//skip loop if this field is null
		//find the right operator
		switch($arr_op[$i]){
			case 0:
				$arr_op[$i]="=";
				break;
			case 1:
				$arr_op[$i]="<>";
				break;
			case 2:
				$arr_op[$i]=">";
				break;
			case 3:
				$arr_op[$i]="<";
				break;
		}
		$ref=$qClass->prepareQuery(array($row[0],$conn->getDatabase(),"",""),3);
		if($ref[0]!=""){
			//search path to information schema
			$conn->dbInfo();
			$resQuery="SELECT column_name FROM columns WHERE table_schema='".$conn->getDatabase()."' AND table_name='$ref[0]' AND ordinal_position=2";
			$sql=$conn->query($resQuery);
			$res_=$sql->fetch();
			//search path to main database
			$conn->dbConn();
			$sql_=$conn->query("SELECT * FROM $ref[0] WHERE $res_[0]='$arr_fields[$i]'");
			$result=$sql_->fetch();
			$arr_fields[$i]=$result[0];
			//$arr_fields[$i]=$display->FKfield($ref[0], $arr_fields[$i]);
		}
		$extra_query.=$row[0].$arr_op[$i]."'$arr_fields[$i]' AND ";	
	}
}
//do we have input parameters
if($extra_query!=""){
	$extra_query=substr($extra_query,0,strlen($extra_query)-4);
	if(strpos($query," WHERE ")){	//if exists
		$extra_query=" AND ".$extra_query;
	} else {	//WHERE clause does not exist
		if(strpos($clause," WHERE ")){
			$extra_query=" AND ".$extra_query;
		} else {
			$extra_query=" WHERE ".$extra_query;
		}
	}
}


//echo $flag;
// the actual query for the grid data 
$sql=$conn->prepare($query.$clause.$extra_query); 
//echo $sql->queryString;
$sql->execute();
//number of rows in the query
$count=$sql->rowCount();
// calculate the total pages for the query 
if( $count > 0 && $limit > 0) { 
              $total_pages = ceil($count/$limit); 
} else { 
              $total_pages = 0; 
} 
 
// if for some reasons the requested page is greater than the total 
// set the requested page to total page 
if ($page > $total_pages) $page=$total_pages;
 
// calculate the starting position of the rows 
$start = $limit*$page - $limit;
 
// if for some reasons start position is negative set it to 0 
// typical case is that the user type 0 for the requested page 
if($start <0) $start = 0; 
// constructing a JSON array (navigator)
$response->page = $page;
$response->total = $total_pages;
$response->records = $count;

// the actual query for the grid data 
$sql=$conn->prepare($query.$clause.$extra_query." ORDER BY $sidx $sord LIMIT $limit OFFSET $start"); 
$sql->execute();
for($i=0;$row=$sql->fetch();$i++){
	$response->rows[$i]["id"]=$row[0];
	$response->rows[$i]["cell"]=null;
	for($j=0;$j<$sql->columnCount();$j++){
		$arr[]=$row[$j];
	}
	$response->rows[$i]["cell"]=$arr;
	$arr=null;
}

//print_r(json_encode($response));exit();
// return the formated data
echo json_encode($response);
?>