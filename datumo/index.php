<?php
require_once "session.php";
$user_id = startSession();
//echo $_SESSION['path'];
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
<!-- END JavaScript -->
</head>
<body>
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
require_once "pub.php";

//php classes
$conn=new dbConnection();
$engine = $conn->getEngine();
$display = new dispClass();
$perm = new restrictClass();
$search = new searchClass();
$config = new configClass();
$menu= new menu($user_id);

//get user info
$perm->userInfo($user_id);
$login=$perm->getUserLogin();
$level=$perm->getUserLevel();

/******************************************************BEGIN OF HEADER******************************************************/
echo "<header>";
	echo "<h1>Datumo Administration Area</h1>";
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
					//display export to Excel option if the current table is a view
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
//set table types
$type = array(); 
$type[0] = "BASE TABLE";
$type[1] = "VIEW";  
//loop for all tables and views
$tables = array();
$table_type = array();
//get tables to which this user has access. Get masks as well
$tables = $perm->tableAccess($user_id);
//get tables type (BASE TABLES or VIEW)
$tableSettings=$display->tableview($tables[0]);
//set table type array
$table_type=$tableSettings[0];
//set table comments array
$table_description=$tableSettings[1];
//count number of tables and views
$table_type_count=array_count_values($table_type);
//get icon picture
$maskPic=$display->getMaskPic();

//STARTING HTML LAYOUT
echo "<section id=section>";
echo "<div class=sidebar lang=exp>";
$config->checkPlugins($level);
$config->compat();
echo "</div>";

echo "<div class=main lang=exp>";
echo "<table style='float:left'><tr>";
for($j=0;$j<sizeof($type);$j++){
	echo "<td valign=top>";
	echo "<table border=0 align=left>";
	echo "<tr><td>$title[$j]</td></tr>";
	//are there any table or View available?
	if(!isset($table_type_count[$type[$j]])) {
		echo "<tr><td width=250px>No entries available</td></tr>";
		//break;
	}
	for($i=0; $i<sizeof($tables[0]); $i++){
		//set table
		$objName=$tables[0][$i];
		//set table mask
		$tableMask=$tables[1][$i];
		//verify if there is any VIEW or TABLE to be displayed and proceed accordingly	
		if($table_type[$i]==$type[$j]){
			//display table or view name (or mask if it exists)
			echo "<tr><td>";
			//search for an associated mask
			echo "<input type=button name=$objName id=$objName value='$tableMask' class=callTables onclick=window.open('manager.php?table=$objName&nrows=20','_self') title='".$table_description[$i]."' style='background-image:url($maskPic[$i]);'>";	
			//disabling admin area search. It takes too long to build the search fields due to the entensive information schema queries
//			echo "<td><a href=javascript:void(0)>Search</a>";
//			//regular search div
//			echo "<div id='".$objName."_div' class=regular>";
//			$display->fields($objName,$i,'admin');
//			echo "</div>";
			echo "</td>";
			echo "<td>";
			//Is there any table with quick search queries?	
			if($search->qsearchFind($objName)){
				echo "<a href=javascript:void(0)>Quick search</a>";	
				//quick search div
				echo "<div id='quicksearch_".$objName."' class=regular>";
				echo "<table border=0>";
				echo "<form name=qsearch$i method=post>";
				echo "<tr><td><b>Search</b>&nbsp;&nbsp;<input type=text class=reg name=qsearch$objName id=qsearch$objName>&nbsp;&nbsp;<input type=image src=pics/magnifier.png onclick=qSubmit('".$objName."',$i)></td></tr>";
				echo "</form>";
				echo "<tr><td><b>Results to be displayed</b>&nbsp;&nbsp;<input type=text class=reg name=qsearchNrows_$i id=qsearchNrows_$i value=100 size=1></td></tr>";
				echo "</table>";
				echo "</div>";
			}
			echo "</td>";
			echo "</tr>";
		}
	}
	echo "</table>";
	echo "</td>";
}

echo "</tr></table>";

//display announcements only to internal users
if($level!=3){
	echo "<div class=announcements id=announcements lang=exp>";
	$display->displayMessage();
	echo "</div>";
}	

//Do we have publicity in this page??
$pub=new pubHandler();
pageViews("");
echo "</div>";
echo "</section>";


/****************************************************END OF CONTENT*********************************************************/




?>

</body>
</html>
