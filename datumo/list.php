<?php 
require_once("session.php");
$user_id = startSession();
?>
<link href="css/main.css" rel="stylesheet" type="text/css">
<?php

/**
 * @author João Lagarto	/ Nuno Moreno
 * @copyright João Lagarto 2010
 * @version Datumo2.0
 * @license EUPL
 * @abstract pop up page to display all the foreign key values available for this specific table and user
 */

error_reporting(1);

//includes
require_once ("__dbConnect.php");
require_once ("dispClass.php");
require_once ("queryClass.php");
require_once ("resClass.php");
require_once ("searchClass.php");
require_once ("reportClass.php");

//http variables
if(isset($_GET['table'])){ $table=$_GET['table'];}
if(isset($_GET['page'])) { //page to be shown
	$pageNum = $_GET['page'];	
} else {
	$pageNum = 1; //default page to be shown
}

//call database class (handle connections)
$db = new dbConnection();
$engine = $db->getEngine();
//call other classes
$display = new dispClass();
$perm = new restrictClass();
$search = new searchClass();

//variables
$nrows = 20;
$offset = ($pageNum - 1) * $nrows; //counting the offset 

echo "<table class=main align=center>";
echo "<tr><td colspan=2 style='background-color:#B5EAAA;' class=list>".strtoupper($table)."</td></tr>";
$numRows = $display->FKlist($table,$offset,$user_id);
echo "</table>";
//if there is no restriction we have to get the number of records in the table
if($numRows == 0){
	$numRows = $display->maxRows($table, $filter, $user_id);	
}

//get the last page according to the number of rows displayed in the page
$maxPage = ceil($numRows/$nrows);
// print the link to access each page
$self = $_SERVER['PHP_SELF'];
// creating previous and next link
// plus the link to go straight to
// the first and last page

if ($pageNum > 1){
   $page  = $pageNum - 1;
   $prev  = " <a href=$self?table=$table&page=$page>[Prev]</a> ";//\"$self?table=$table&nrows=$nrows&order=$order&colOrder=$colOrder&page=$page\">[Prev]</a> ";
   $first = " <a href=$self?table=$table&page=1>[First Page]</a> "; //\"$self?table=$table&nrows=$nrows&order=$order&colOrder=$colOrder&page=1\">[First Page]</a> ";
} else {
   $prev  = '&nbsp;'; // we're on page one, don't print previous link
   $first = '&nbsp;'; // nor the first page link
}
	
if ($pageNum < $maxPage){
   $page = $pageNum + 1;
   $next = " <a href=$self?table=$table&page=$page>[Next]</a> ";
   $last = " <a href=$self?table=$table&page=$maxPage>[Last Page]</a> ";
} else {
   $next = '&nbsp;'; // we're on the last page, don't print next link
   $last = '&nbsp;'; // nor the last page link
}

echo "<table align=center class=list>";
echo "<tr><td>".$first.$prev." Showing page $pageNum of $maxPage pages ".$next.$last."</td></tr>"; 
echo "</table>";




?>