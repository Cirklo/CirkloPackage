<?php
require_once("../session.php");
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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../css/main.css" rel="stylesheet" type="text/css">
<link href="../css/autoSuggest.css" rel="stylesheet" type="text/css">
<link href="../css/CalendarControl.css" rel="stylesheet" type="text/css">
<link href="../css/tipTip.css" rel="stylesheet" type="text/css">
<link href="../css/styles.css" rel="stylesheet" type="text/css">
<link href="../css/navbar.css" rel="stylesheet" type="text/css">

<!-- END Navigation bar CSS -->

<!-- BEGIN JavaScript -->

<script type="text/javascript" src="../js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="../js/jquery.init.js"></script>
<script type="text/javascript" src="../js/jquery.tipTip.js"></script>
<script type="text/javascript" src="js/jquery.basket.js"></script>
<script type="text/javascript" src="../js/CalendarControl.js"></script>
<script type="text/javascript" src="../js/filters.js"></script>
<script type="text/javascript" src="../js/functions.js"></script>
<script type="text/javascript" src="../js/ajax.js"></script>

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
error_reporting(1);

//includes
require_once "../__dbConnect.php";
require_once "../dispClass.php";
require_once "../configClass.php";
require_once "../resClass.php";
require_once "../menu.php";
require_once "../plotAux.php";

//call database class (handle connections)
$conn=new dbConnection();
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
	echo "<h1>Datumo Administration Area</h1>";
	//navigation bar display
	echo "<nav class=navigation>";
		echo "<ul class=dropdown id=menu>";
			echo "<li><a href=../index.php>Home</a>";
			echo "<li><a>Reports</a>";
				echo "<ul class=dropdown>"; 
					//need to create a class to handle reports, treeviews and plots. Only show the available one to this user
					$reports=$menu->getReports();
					echo "<li class=rtarrow><a>My reports</a>";
						echo "<ul>";
						//loop through all available reports
						foreach($reports as $key=>$value){
							echo "<li><a href=javascript:void(0) onclick=window.open('../report.php?report=$key','_blank','height=550px,width=850px,scrollbars=yes');>".$value."</a></li>";
						}
							//echo "<li><a href=#>Report 1</a></li>";
						echo "</ul>";
					echo "</li>";
					echo "<li><a href=javascript:void(0) onclick=window.open('../genReport.php','mywindow','height=500px,width=500px,scrollbars=yes')>Create report</a></li>";
					$treeviews=$menu->getTreeviews();
					echo "<li class='rtarrow'><a>Treeviews</a>";
						echo "<ul>";
						//loop through all available treeview reports
						foreach($treeviews as $key=>$value){
							echo "<li><a href=javascript:void(0) onclick=window.open('../treeview.php?tree=$key','_blank','height=550px,width=550px,scrollbars=yes');>$value</a></li>";
						}	
						echo "</ul>";
					echo "</li>";
					$plots=$menu->getPlots();
					echo "<li class='rtarrow'><a>Plots</a>";
						echo "<ul>";
						//loop through all plots
						foreach($plots as $key=>$value){
							echo "<li><a href=javascript:void(0) onclick=window.open('../plot.php?plot_id=$key','_blank','width=820px,height=550px,menubar=yes')>$value</a></li>";
						}
						echo "</ul>";
					echo "</li>";
					//display export to Excel option if the current table is a view
					if(isset($table) and ($display->checkTableType($table) or $level==0))
						echo "<li><a href=../excel.php?table=$table title='Export data to xls file'>Export to Excel</a></li>";
				echo "</ul>";
			echo "</li>";
			echo "<li><a href=http://www.cirklo.org/datumo_help.php target=_blank>Help</a></li>";
			echo "<li><a href=javascript:void(0) onclick=window.open('../helpdesk.php','_blank','height=400px,width=365px,resizable=no,menubar=no')>Helpdesk</a>";
			echo "<li><a>About</a>";
				echo "<ul class=dropdown>";
					echo "<li><a href=http://www.cirklo.org/datumo.php target=_blank>Datumo</a></li>";
					echo "<li><a href=http://www.cirklo.org target=_blank>Cirklo</a></li>";
				echo "</ul>"; 
			echo "</li>";
			//log in and out information
			echo "<li class=login>You are logged as $login! ";
			echo "<a href=../session.php?logout style='color:#f7c439;text-decoration:underline;'>Sign out</a></li>";
			//External links
			echo "<li class=external>";
			echo "<a href='http://www.facebook.com/pages/Cirklo/152674671417637' target=_blank><img src=../pics/fb.png width=30px border=0 title='Visit our Facebook page'>";
			echo "&nbsp;&nbsp;";
			echo "<a href='http://www.youtube.com/user/agendocirklo' target=_blank><img src=../pics/youtube.png width=30px border=0 title='Feature videos'></a>";
			echo "</li>";
		echo "</ul>";
		
	echo "</nav>";
echo "</header>";
/********************************************END OF HEADER / CONTENT GOES NEXT**********************************************/

//STARTING HTML LAYOUT
echo "<section id=section>";
echo "<div class=sidebar lang=exp>";
$config->checkPlugins();
$config->compat();
echo "</div>";
echo "<div class=main lang=exp>";

//form to create requisitions
echo "<form method=post name=newVendor>";
echo "<table>";
echo "<tr><td><font color=#FF0000>ALL fields required</font></td></tr>";
echo "<tr><td colspan=2><b>Supplier data</b></td></tr>";
echo "<tr><td>Name</td><td><input type=text name=vendor_name id=vendor_name lang=yes></td></tr>";
echo "<tr><td>Address</td><td><input type=text name=address id=address lang=yes></td></tr>";
echo "<tr><td>Street</td><td><input type=text name=street id=street lang=yes></td></tr>";
echo "<tr><td>Postal code</td><td><input type=text name=postal_code id=postal_code lang=yes></td></tr>";
echo "<tr><td>VAT reg. No.</td><td><input type=text name=vat id=vat lang=yes></td></tr>";
$sql=$conn->query("SELECT country_id, country_name FROM country WHERE country_id<240");	
//echo $sql->queryString;
echo "<tr><td>Country</td><td>";
echo "<select name=country id=country>";
for($i=0;$row=$sql->fetch();$i++){
	echo "<option id=$row[0]>$row[1]</option>";
}
echo "</select>";
echo "</td></tr>";
echo "<tr><td colspan=2><b>Communication</b></td></tr>";
echo "<tr><td>Name</td><td><input type=text name=com_name id=com_name lang=yes></td></tr>";
echo "<tr><td>Phone</td><td><input type=text name=phone id=phone lang=yes></td></tr>";
echo "<tr><td>Fax</td><td><input type=text name=fax id=fax lang=yes></td></tr>";
echo "<tr><td>Email</td><td><input type=text name=email id=email lang=yes></td></tr>";
echo "<tr><td colspan=2><b>Payment data</b></td></tr>";
echo "<tr><td>NIB</td><td><input type=text name=nib id=nib lang=nat> *</td></tr>";
echo "<tr><td>IBAN</td><td><input type=text name=iban id=iban lang=int> **</td></tr>";
echo "<tr><td>SWIFT code</td><td><input type=text name=swift id=swift lang=int> **</td></tr>";
echo "<tr><td>ABA/routing</td><td><input type=text name=aba id=aba lang=int> **</td></tr>";
echo "<tr><td>Bank name</td><td><input type=text name=bank id=bank lang=yes></td></tr>";
echo "<tr><td>Street</td><td><input type=text name=bank_street id=bank_street lang=yes></td></tr>";
echo "<tr><td><br></td></tr>";
echo "<tr><td><font color=#FF0000>* PT supplier only</font></td></tr>";
echo "<tr><td><font color=#FF0000>** International suppliers only</font></td></tr>";
echo "<tr><td colspan=2><input type=reset value=Clear>   <input type=button name=vendorSubmit id=vendorSubmit value=Submit></td></tr>";
echo "</table>";
//end form
echo "</form>";


echo "</div>";
echo "</section>";

?>
</body>
</html>
