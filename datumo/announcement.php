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

<title>Announcement</title>

<!-- BEGIN Navigation bar CSS - This is where the magic happens -->
<link rel="stylesheet" href="css/main.css">
<!-- END CSS -->
</head>
<body>
<?php 

//PHP includes
require_once "__dbConnect.php";

//classes
$conn=new dbConnection();

//http variables
if(isset($_GET['announcement_id'])){	$announcement_id=$_GET['announcement_id'];}

$query="SELECT * FROM announcement WHERE announcement_id=$announcement_id";
$sql=$conn->query($query);
$row=$sql->fetch();

echo "<div style='text-align:justify; line-height:18px;'>";
echo "<b>$row[4]</b>";
echo "<h3>$row[1]</h3>";
echo "<hr>";
echo $row[2];
echo "</div>";


?>