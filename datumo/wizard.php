<?php 
//include the information needed for the connection to MySQL data base server. 
// we store here username, database and password 
require_once "session.php";
$user_id = startSession();

require_once "__dbConnect.php"; 
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
if(isset($_GET['report_id'])) $report_id=$_GET['report_id'];

// if we not pass at first time index use the first column for the index or what you want
if(!$sidx) $sidx=1; 

//call database class and connect to database
$conn = new dbConnection();
$database = $conn->getDatabase();

$query="SELECT report_query FROM report WHERE report_id=$report_id";
$sql=$conn->query($query);
$row=$sql->fetch(); 
$query=$row[0];


//echo $flag;
// the actual query for the grid data 
$sql=$conn->prepare($query); 
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
$sql=$conn->prepare($query." ORDER BY $sidx $sord LIMIT $limit OFFSET $start"); 
//echo $sql->queryString;
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