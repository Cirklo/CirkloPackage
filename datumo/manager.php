<?php
require_once("session.php");
$user_id = startSession();
?>

<!doctype html>  
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>

<!-- BEGIN Meta tags -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<title>Datumo administration area</title>

<!-- BEGIN Navigation bar CSS - This is where the magic happens -->
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/autoSuggest.css">
<link rel="stylesheet" href="css/CalendarControl.css">
<link rel="stylesheet" href="css/tipTip.css">
<link rel="stylesheet" href="css/navbar.css">
<link rel="stylesheet" href="css/jquery.jnotify.css">
<!-- END Navigation bar CSS -->

<!-- BEGIN JavaScript -->
<script type="text/javascript" src="js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.14.custom.js"></script>
<script type="text/javascript" src="js/jquery.init.js"></script>
<script type="text/javascript" src="js/jquery.tipTip.js"></script>
<script type="text/javascript" src="js/jquery.jnotify.js"></script>
<script type="text/javascript" src="js/jquery.action.js"></script>
<script type="text/javascript" src="js/CalendarControl.js"></script>
<script type="text/javascript" src="js/filters.js"></script>
<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript" src="js/cloneFieldset.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="requisitions/js/jquery.basket.js"></script>
<script type="text/javascript">

function updQtt(oper, row){
	var value = Number($("#quantity_"+row).val());
	switch (oper){
	case "sum":
		value++;
		break;
	case "sub":
		if(value==1)return;
		value--;
		break;
	}
	$("#quantity_"+row).val(value);
}

$(document).ready(function(){
	$("#del").click(function(){
		$.action({
			action:"delete"
			});
	});

	$("#upd").click(function(){
		$.action({
			action:"update"
			});
	});

	$("#insert").click(function(){
		$.action({
			action:"insert"
			});
	});
	
});

</script>
<!-- END JavaScript -->
</head>
<?php 

//php includes
require_once "__dbConnect.php";
require_once "dispClass.php";
require_once "queryClass.php";
require_once "resClass.php";
require_once "searchClass.php";
require_once "configClass.php";
require_once "module.php";
require_once "functions.php";
require_once "menu.php";
require_once "plotAux.php";

//php classes
$conn=new dbConnection();
$engine = $conn->getEngine();
$display = new dispClass();
$perm = new restrictClass();
$search = new searchClass();
$config = new configClass();
$menu= new menu($user_id);

//HTTP variables
if(isset($_GET['report']))	$report = 1;
if(isset($_GET['table']))	$table = $_GET['table']; 	//chosen table
if(isset($_GET['nrows']))	$nrows = $_GET['nrows']; 	//number of rows to be displayed per page
if(isset($_GET['no']))		$no = $_GET['no']; 		 	//number of elements from advanced filter
if(isset($_GET['action']))	$action = $_GET['action']; 	//database query
if(isset($_GET['order']))	$order = $_GET['order'];	//results' order
if(isset($_GET['search'])){	//which filter does it come from?
	$stype = $_GET['search'];	
} else  {
	$stype = "";
}
if(isset($_GET['colOrder'])){	//set the attribute chosen to be ordered
	$colOrder = $_GET['colOrder'];
	if($colOrder=='') $colOrder = $table."_id";
} else {
	$colOrder = $table."_id"; //set the first attribute to be ordered
}
if(isset($_GET['page'])) { //page to be shown
	$pageNum = $_GET['page'];	
} else {
	$pageNum = 1; //default page to be shown
}

//get user info
$perm->userInfo($user_id);
$login=$perm->getUserLogin();
$level=$perm->getUserLevel();

//HACK to fill the advanced filter 
if($stype==2)	echo "<body onload=getSearchVars('$table')>";

/******************************************************BEGIN OF HEADER******************************************************/
echo "<header>";
	echo "<h1>Datumo Administration Area: ".strtoupper($table)."</h1>";
	//navigation bar display
	echo "<nav class=navigation>";
		echo "<ul class=dropdown id=menu>";
			echo "<li><a href=index.php>Home</a>";
			echo "<li><a>Reports</a>";
				echo "<ul class=dropdown>"; 
					//need to create a class to handle reports, treeviews and plots. Only show the available one to this user
					$reports=$menu->getReports();
					echo "<li class=rtarrow><a>My reports</a>";
						echo "<ul>";
						//loop through all available reports
						foreach($reports as $key=>$value){
							echo "<li><a href=javascript:void(0) onclick=window.open('report.php?report=$key','_blank','height=550px,width=850px,scrollbars=yes');>".$value."</a></li>";
						}
							//echo "<li><a href=#>Report 1</a></li>";
						echo "</ul>";
					echo "</li>";
					echo "<li><a href=javascript:void(0) onclick=window.open('".$conn->getFolder()."/genReport.php','mywindow','height=500px,width=500px,scrollbars=yes')>Create report</a></li>";
					$treeviews=$menu->getTreeviews();
					echo "<li class='rtarrow'><a>Treeviews</a>";
						echo "<ul>";
						//loop through all available treeview reports
						foreach($treeviews as $key=>$value){
							echo "<li><a href=javascript:void(0) onclick=window.open('treeview.php?tree=$key','_blank','height=550px,width=550px,scrollbars=yes');>$value</a></li>";
						}	
						echo "</ul>";
					echo "</li>";
					$plots=$menu->getPlots();
					echo "<li class='rtarrow'><a>Plots</a>";
						echo "<ul>";
						//loop through all plots
						foreach($plots as $key=>$value){
							echo "<li><a href=javascript:void(0) onclick=window.open('plot.php?plot_id=$key','_blank','width=820px,height=550px,menubar=yes')>$value</a></li>";
						}
						echo "</ul>";
					echo "</li>";
					//display export to Excel option if the current table is a view or if user is a system administrator
					if(isset($table) and ($display->checkTableType($table) or $level==0))
						echo "<li><a href=excel.php?table=$table title='Export data to xls file'>Export to Excel</a></li>";
				echo "</ul>";
			echo "</li>";
			echo "<li><a href=http://www.cirklo.org/datumo_help.php target=_blank>Help</a></li>";
			echo "<li><a href=javascript:void(0) onclick=window.open('helpdesk.php','_blank','height=400px,width=365px,resizable=no,menubar=no')>Helpdesk</a>";
			echo "<li><a>About</a>";
				echo "<ul class=dropdown>";
					echo "<li><a href=http://www.cirklo.org/datumo.php target=_blank>Datumo</a></li>";
					echo "<li><a href=http://www.cirklo.org target=_blank>Cirklo</a></li>";
				echo "</ul>"; 
			echo "</li>";
			//log in and out information
			echo "<li class=login>You are logged as $login! ";
			echo "<a href=session.php?logout style='color:#f7c439;text-decoration:underline;'>Sign out</a></li>";
			//External links
			echo "<li class=external>";
			echo "<a href='http://www.facebook.com/pages/Cirklo/152674671417637' target=_blank><img src=pics/fb.png width=30px border=0 title='Visit our Facebook page'>";
			echo "&nbsp;&nbsp;";
			echo "<a href='http://www.youtube.com/user/agendocirklo' target=_blank><img src=pics/youtube.png width=30px border=0 title='Feature videos'></a>";
			echo "</li>";
		echo "</ul>";
		
	echo "</nav>";
echo "</header>";
/********************************************END OF HEADER / CONTENT GOES NEXT**********************************************/
//print_r($reports);
//get information schema info
$display->tableHeaders($table);

//other variables
$r=false;
$offset = ($pageNum - 1) * $nrows; //counting the offset 


//check for action variable after applying a filter
if(isset($_GET['comeFromAction']) and $_GET['comeFromAction']!="false"){ //if exists call javascript with action notification
	$comeFromAction=$_GET['comeFromAction'];
	if($comeFromAction[strlen($comeFromAction)-1]=="e"){
		$comeFromAction.="d";
	} else {
		$comeFromAction.="ed";
	}
	echo "<script type='text/javascript'>";
	echo "$.jnotify('Record(s) $comeFromAction');";
	echo "</script>";
}

//recover variables from filter to construct query (it relies on 3 elements)
if(isset($_GET['filter'])){
	$filter=true;
	//exit();
} else {
	foreach($_POST as $key=>$value){
		if($action) break; //Chrome/Firefox?
		//echo $value;
		if($value!=""){ $display->__set($key, $value); }
		
	}
	$filter=false;
}

if(!isset($report)){
	if($stype==3){ //if it comes from quick search
		$display->qSearchQueryBuilder($table,$nrows);	
		$numRows = $nrows;
	} else {
		$display->queryBuilder($user_id, $table, $nrows, $filter, $offset,$order, $colOrder);
		//get the number of rows in the table
		$numRows = $display->maxRows($table, $filter, $user_id);
	}
}	
//exit();
//get the last page according to the number of rows displayed in the page
$maxPage = ceil($numRows/$nrows);
// print the link to access each page
$self = $_SERVER['PHP_SELF'];
// creating previous and next link
// plus the link to go straight to
// the first and last page

if ($pageNum > 1){
   $page  = $pageNum - 1;
   $prev  = " <a href=javascript:void(0) class=exp onclick=submit('$stype','$table',$nrows,'$order','$colOrder',$page)>[Prev]</a> ";//\"$self?table=$table&nrows=$nrows&order=$order&colOrder=$colOrder&page=$page\">[Prev]</a> ";
   $first = " <a href=javascript:void(0) class=exp onclick=submit('$stype','$table',$nrows,'$order','$colOrder',1)>[First Page]</a> "; //\"$self?table=$table&nrows=$nrows&order=$order&colOrder=$colOrder&page=1\">[First Page]</a> ";
} else {
   $prev  = '&nbsp;'; // we're on page one, don't print previous link
   $first = '&nbsp;'; // nor the first page link
}
	
if ($pageNum < $maxPage){
   $page = $pageNum + 1;
   $next = " <a href=javascript:void(0) class=exp onclick=submit('$stype','$table',$nrows,'$order','$colOrder',$page)>[Next]</a> ";
   $last = " <a href=javascript:void(0) class=exp onclick=submit('$stype','$table',$nrows,'$order','$colOrder',$maxPage)>[Last Page]</a> ";
} else {
   $next = '&nbsp;'; // we're on the last page, don't print next link
   $last = '&nbsp;'; // nor the last page link
   //set page variable as maxpage
   if($page==$maxPage){
   		$page=$maxPage;
   }
}


//STARTING HTML LAYOUT
echo "<section id=section>";
echo "<div class=sidebar lang=exp>";
$config->checkPlugins($level);
$config->compat();
echo "</div>";

echo "<div class=main lang=exp>";
//display user's restrictions for this table
$perm->tablePermissions($table, $user_id);
//search for available modules for this table
echo "<table>";
echo "<tr>";
$module=new module($table);
echo "<td><a href=javascript:void(0) title='click to view column comments' onclick=showColumnComments()>(view table comments)</a></td>";
echo "</tr>";
echo "</table>";

echo "<table border=0>";
echo "<tr>";
if($perm->getUpdate()) {echo "<td><input type=button name=upd id=upd value=Update></td>";}
if($perm->getDelete()) {echo "<td><input type=button name=del id=del value=Delete></td>";}
if($perm->getUpdate() or $perm->getDelete() or $perm->getInsert()) $r=true;
//set order
//Regular filter
echo "<td><input type=button name=filter_$table id=filter_$table value=Search>";
//echo "<td><a href=javascript:void(0)>Search</a>";
echo "<div id='".$table."_div' class=regular>";
$display->fields($table,"",'manager',$order,$colOrder,$page);
echo "</div>";
echo "</td>";
//Advanced search
echo "<td><input type=button name=adv_$table id=adv_$table value='Advanced Search'>";
echo "<div id='advsearch' class=regular>";
$search->advancedFilter($user_id,$table);
echo "</div>";
echo "</td>";
echo "<td><input type=button name=legend id=legend value=Legend>";
echo "<div id=legendiv class=regular>";
$display->legend($table,$user_id);
echo "</div>";
echo "</td>";
//print page navigation
echo "<td>".$first.$prev." Showing page $pageNum of $maxPage pages ".$next.$last."</td>"; 
echo "<td><b>Jump to page</b> <input type=text size=1 name=newPage id=newPage value=$pageNum><input type=button id=jump value='Go' onclick=submit('$stype','$table',$nrows,'$order','$colOrder',$('#newPage').val())></td>";
echo "</tr>";
echo "</table>";
//display results
echo "<table class=main id=main>";
//are there results to display?
if($numRows>0){
	echo "<tr class=headers>";
	echo "<td colspan=2></td>";
	if($r) echo "<td style='text-align:center'><input type=checkbox id=cb_all name=cb_all onchange=checkall(this.id,$nrows)></td>";
	$display->headers(FALSE, $stype,$table,$nrows,$order,1); //call method to display table headers
	echo "</tr>";
} else {
	echo "<tr class=headers>";
	echo "<td colspan=3></td>";
	$display->headers(TRUE, $stype,$table,$nrows,$order,1); //call method to display table headers
	echo "</tr>";
	echo "No results to display";
}
//display main results
$display->results($table,$r); //call method to display query results
//echo "</table>";
//search for permissions related with new entries in the table
if($perm->getInsert()) {$display->insert($table,$stype,$nrows,$order);}

echo "</td>";
echo "</tr>";
echo "</table>";
echo "</div>";

echo "</section>";

//HACKINGS
echo "<div id=info name=info class=info>";
$display->information($table, $nrows);
echo "</div>";

echo "<input type='hidden' name='multiple' id='multiple' value=1>";
/**************************JAVASCRIPT*******************************/
if($stype==2){
	//Big fu(...) hack to fill the advanced filter after a search
	echo "<script type='text/javascript'>";
	echo "function getSearchVars(objName){";
	echo "document.getElementById('table').value=objName;";
	echo "ajaxEquiDD(document.getElementById('table'),'field');";
	$i=0; //counter
	$j=0; //another counter
	foreach($_POST as $key=>$value){
		$i++;$j++; //increment counters
		if($i==1) { //field
			echo "document.getElementById('$key').value='$value';";//validate this field to foreign key values
			echo "selOperator(document.getElementById('$key').id);";
			echo "var att='$value';";
		}
		if($i==2){
			echo "document.getElementById('$key').value='$value';";
			echo "if(document.getElementById('$key').value == 4) var fk=true;";
			echo "else var fk=false;";
		}
		if($i==3) {
			echo "if(fk==true){";
			//echo "alert('$value');";
			echo "url='ajaxGetAtt.php?id=$value&table='+objName+'&att='+att;";
			echo "var str = ajaxRequest(url);";
			echo "document.getElementById('$key').value=str;";
			echo "} else {";
			echo "document.getElementById('$key').value='$value';}";
			if($j<sizeof($_POST)) echo "checknew('sum', document.getElementById('clone'));";
			$i=0;
		}
	}
	echo "}";
	echo "</script>";
}

/**************************END OF JAVASCRIPT*******************************/


/****************************************************END OF CONTENT*********************************************************/




?>

</body>
</html>
