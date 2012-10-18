<?php 
//set current working directory
// session_start();
// $pathOfIndex = $_SERVER['SCRIPT_NAME'];
// $pathOfIndex = substr($pathOfIndex,0,strlen($pathOfIndex)-strlen("/index.php"));
// $_SESSION['path']=$pathOfIndex;

	session_start();
	$pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
	$_SESSION['path'] = "../".$pathOfIndex[sizeof($pathOfIndex)-1];

?>

<link href="../datumo/css/index.css" media="screen" rel="stylesheet" type="text/css" />
<link href="../datumo/css/tipTip.css" rel="stylesheet" type="text/css">
<link href="../datumo/css/jquery.alert.css" rel="stylesheet" type="text/css">
<link href="../datumo/css/jquery.jnotify.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="../datumo/js/jquery-1.4.4.js"></script>
<script type="text/javascript" src="../datumo/js/jquery.jnotify.js"></script>
<script type="text/javascript" src="../datumo/js/jquery.init.js"></script>
<script type="text/javascript" src="../datumo/js/jquery.cookie.js.js"></script>
<script type="text/javascript" src="../datumo/js/jquery.tipTip.js"></script>
<script type="text/javascript" src="../datumo/js/jquery.alert.js"></script>

<?php

//includes
require_once ".htconnect.php";

//call class
$conn=new dbConnection();

//page title
echo "<title>Datumo 2.0 :: Open Source Database Management</title>";
echo "<div class=main>";
//initialize header
echo "<div class=header>";
//initialize title
echo "<div class=title>";
echo "<div class=logo><img src=../datumo/pics/cirklo.png></div>"; //div to display logo
echo "<div class=desc>Collecting and developing open source tools for lab management optimization</div>"; //div to display cirklo's description
//end title
echo "</div>";
//initialize login div
echo "<div class=login>";
echo "<hr>";
//initialize table that includes login form
echo "<table border=0 align=right>";
//Username input field
echo "<tr><td>Username</td><td><input type=text name=user_login id=user_login></td></tr>";
//Password input field
echo "<tr><td>Password</td><td><input type=password name=user_passwd id=user_passwd></td></tr>";
//Recover password and Login button
echo "<tr><td colspan=2 style='text-align:right'><a href=javascript:void(0) onclick=$(document).recoverPwd() class=exp>Recover password</a>&nbsp;&nbsp;<input type=button id=login value=Login onclick=$(document).login();></td></tr>";
echo "</table>";
//initialize div to display alert message
echo "<div id=errorNotify style='position:relative;width:200px;float:left'></div>";
//end login
echo "</div>";
//initialize contacts div
echo "<div class=contacts>";
echo "<hr>";
$info_email="info@cirklo.org";
$bugs_email="bugs@cirklo.org";
$support_email="support@cirklo.org";
$website="www.cirklo.org";
echo "<font color='#FFFFFF'>CONTACT INFORMATION</font><br>";
echo "website: <a href=http://$website>$website</a><br>";
echo "info: <a href=mailto:$info_email>$info_email</a><br>";
echo "support: <a href=mailto:$support_email>$support_email</a><br>";
echo "bugs: <a href=mailto:$bugs_email>$bugs_email</a><br>";
echo "<hr>";
//end contacts
echo "</div>";
//initialize external links
echo "<div class=outlinks>";
//facebook -> update page
echo "<span class=extlinks><a href='http://www.facebook.com/pages/edit/?id=152674671417637&sk=basic#!/pages/Cirklo/152674671417637' target=_blank><img src=../datumo/pics/fb.png width=60px height=60 border=0 title='Visit our Facebook page'></a></span>";
//twitter -> Why the hell do we need this?
echo "<span class=extlinks><a href='http://www.twitter.com/cirklo2010' target=_blank><img src=../datumo/pics/twitter.png width=60px height=60 border=0 title='Follow us at Twitter'></a></span>";
//You tube feature videos
echo "<span class=extlinks><a href='http://www.youtube.com' target=_blank><img src=../datumo/pics/youtube.png width=60px height=60 border=0 title='Feature videos'></a></span>";
//add links to external sites
echo "</div>";
//end header
echo "</div>";
//initialize news/announcements
echo "<div class=article>";
//initialize title
echo "<span class=title>News</span>";
echo "<br><br>";
//query to get announcements from the database
$sql = $conn->prepare("SELECT * FROM announcement WHERE announcement_end_date>NOW() ORDER BY announcement_date DESC");
try{
	$sql->execute();
	if($sql->rowCount()>0){
		//loop through all results
		for($i=0;$row=$sql->fetch();$i++){
			//display title
			echo "<span class=news_title>$row[3], $row[1]</span>";
			echo "<br><br>";
			//display message
			echo "<span class=news_desc>".$row[2]."</span>";
			echo "<br>";
			//display due date
			echo "Available until: $row[4]";
			//dont display ruler if it is the last result
			if($i<$sql->rowCount()-1) echo "<hr>";
		}
	} else { //no results to display
		echo "<span class=news_desc>No announcements to display at the time</span>";
	}
} catch(Exception $e){
	echo $e->getMessage();
}
//end news/announcements
echo "</div>";
//end main container
echo "</div>";

//initialize footer
echo "<div class=footer>";
echo "<hr>";
echo "Copyright © Cirklo 2011";
//end footer
echo "</div>";


?>