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
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript">

function imageValidation(){
	if($("#resource").val()==0){
		alert("You must select one resource to proceed");
		return;
	}
	if($("#file").val()==""){
		alert("You must enter a valid path to proceed");
		return;
	}
	CurForm=eval("document.upload");
	CurForm.action="functions.php?type=0";
	CurForm.submit();
}

</script>
<!-- END JavaScript -->
</head>
<body>

<?php

/** @author João Lagarto
 * @copyright João Lagarto 2010
 * @version Requisition System 2.0
 * @license EUPL
 * @abstract Script to handle baskets depending on the basket type
 */

//php includes
require_once "__dbConnect.php";
require_once "dispClass.php";
require_once "resClass.php";
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
$config = new configClass();
$menu= new menu($user_id);

//get user info
$perm->userInfo($user_id);
$login=$perm->getUserLogin();
$level=$perm->getUserLevel();

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



//STARTING HTML LAYOUT
echo "<section id=section>";
echo "<div class=sidebar lang=exp>";
$config->checkPlugins($level);
$config->compat();
echo "</div>";

echo "<div class=main lang=exp>";



$perm->userInfo($user_id);
if($perm->getUserLevel()==2){
	echo "<font color=#FF0000>You don't have permission to access this resource</font>";
	exit();
}
//display error if it exists
if(isset($_GET['error']))	echo "<font color=#FF0000>".$_GET['error']."</font>";
if(isset($_GET['success']))	echo "<font color=#00FF00>Image successfully uploaded</font>";
echo "<table>";
echo "<tr><td>";
echo "<form name=upload enctype=multipart/form-data method=post>";
//content goes here
//select all resource in the database
$query="SELECT resource_id, resource_name FROM resource";
$sql=$conn->query($query);
echo "Resource<br>";
echo "<select name=resource id=resource>";
echo "<option value=0 selected>Select a resource...</option>";
for($i=0;$row=$sql->fetch();$i++){
	echo "<option value=$row[0]>$row[1]</option>";
}
echo "</select>";
echo "<br><br>";
//upload options
$max_file_size=1000000;
echo "<input type=hidden name=MAX_FILE_SIZE value='$max_file_size'>";
echo "<label for=file>Image to upload (image name must not include spaces)</label><br>";
echo "<input id=file type=file name=file size=40>";
echo "<br><br>";
echo "<input id=submit type=submit name=submit value=Submit onclick=imageValidation()>";
echo "</form>";
echo "</td></tr>";
echo "</table>";

echo "</div>";
echo "</section>";
?>

</body>
</html>