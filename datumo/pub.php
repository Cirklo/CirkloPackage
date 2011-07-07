<?php
//disable warnings in this page
error_reporting(0);

if(isset($_GET['count'])){
	require_once "session.php";
	countClicks();
}

if(isset($_GET['timer'])){
	require_once "session.php";
	incrementTimer();
}

class pubHandler{
	private $pdo;
	function __construct($resource){
		if(!isset($resource))$resource="";
		
		$this->pdo=new dbConnection();
		//get the current page
		$target_page=basename($_SERVER['SCRIPT_FILENAME']);
		//check if the publicity is ON for this database
		$sql=$this->pdo->query("SELECT configParams_value FROM configParams WHERE configParams_name='publicity'");
		$row=$sql->fetch();
		if($row[0]){
			//set array with all pages available for publicity
			$this->drawDivs($target_page,$resource);
		} else {
			//do nothing
		}
	}
	
	function drawDivs($target_page,$resource){
		switch ($target_page){
			case "admin.php";
				echo "<script type='text/javascript' src='js/jquery.timers.js'></script>";
				echo "<script type='text/javascript' src='js/jquery.pub.js'></script>";
				//parent div -> holds the children inside
				echo "<div lang=exp style='
				float:right;
				position:relative;
				border:0px solid;
				width:302px;
				height:550px;'>";
				//get all divs in defined in the database for this specific page
				$query="SELECT pubpages_position, pubpages_width, pubpages_height, pub_image, pub_outlink, pub_id
				FROM pubpages, pub 
				WHERE pubpages_id=pub_target 
				AND pubpages_name='$target_page'";
				$sql=$this->pdo->query($query);
				//loop through all results
				for($i=0;$row=$sql->fetch();$i++){
					echo "<div lang=exp id=$row[0] style='
						position:relative;
						border:0px solid;
						overflow:hidden;
						width:$row[1];
						height:$row[2];'>";
					echo "<a href=javascript:clickPub('$row[5]','$row[4]');><img src='$row[3]'></a>";
					echo "</div>";
				}
				echo "</div>";
				break;
			case "weekview.php";
				//JS includes
				echo "<script type='text/javascript' src='../datumo/js/jquery-1.5.1.js'></script>";
				echo "<script type='text/javascript' src='../datumo/js/jquery.timers.js'></script>";
				echo "<script type='text/javascript' src='../datumo/js/jquery.pub.js'></script>";
				//left main div -> holds children
				echo "<div style='
					background-color:#F7C439;
					position:absolute;
					top:0px;
					width:130px;
					height:100%;
					border:0px solid;'>";
				$query="SELECT pubpages_position, pubpages_width, pubpages_height, pub_image, pub_outlink, pub_id
				FROM pubpages, pub, pubref, resourcetype
				WHERE pubpages_id=pub_target
				AND pubref_pub=pub_id
				AND resourcetype_id=pubref_reference
				AND resourcetype_id IN (SELECT resource_type FROM resource WHERE resource_id=$resource)
				AND pubpages_name='$target_page'
				AND pubpages_position='pub1'";
				$sql=$this->pdo->query($query);
				//loop through all results
				for($i=0;$row=$sql->fetch();$i++){
					echo "<div lang=exp id=$row[0] style='
						position:relative;
						border:2px solid #FFF;
						overflow:hidden;
						margin:auto;
						margin-bottom:0px;
						width:$row[1];
						height:$row[2];
						text-align:center;
						vertical-align:bottom'>";
					echo "BASDABDJ KLABDJKLA BDSJKALBDASJKLBD ASJKDBAS JKDBAJKLB<br>";
					echo "BASDABDJ KLABDJKLA BDSJKALBDASJKLBD ASJKDBAS JKDBAJKLB<br>";
					echo "BASDABDJ KLABDJKLA BDSJKALBDASJKLBD ASJKDBAS JKDBAJKLB<br>";
					echo "BASDABDJ KLABDJKLA BDSJKALBDASJKLBD ASJKDBAS JKDBAJKLB<br>";
					echo "BASDABDJ KLABDJKLA BDSJKALBDASJKLBD ASJKDBAS JKDBAJKLB<br>";
					echo "BASDABDJ KLABDJKLA BDSJKALBDASJKLBD ASJKDBAS JKDBAJKLB<br>";
					echo "BASDABDJ KLABDJKLA BDSJKALBDASJKLBD ASJKDBAS JKDBAJKLB<br>";
					
					echo "<br>";					
					echo "<a href=javascript:clickPub('$row[5]','$row[4]');><img src='$row[3]' width=128px height=256px></a>";
					echo "</div>";
				}		
				echo "</div>";
				break;
		}
		
	}	
}

function countClicks(){
	require_once "__dbConnect.php";
	$conn=new dbConnection();
	
	if(isset($_GET['pub']))	$pub_id=$_GET['pub'];
	try{
		$conn->query("UPDATE pub SET pub_clicks=pub_clicks+1 WHERE pub_id=$pub_id");
	} catch (Exception $e){
		echo $e->getMessage();
	}
}

function incrementTimer(){
	require_once "__dbConnect.php";
	$conn=new dbConnection();
	if(isset($_GET['path']))	$path=$_GET['path'];
	if(isset($_GET['time']))	$time=$_GET['time'];
	if(isset($_GET['resource']))$resource=$_GET['resource'];
	
	//DATUMO PUB
	if($resource=="")
		$sql="UPDATE pub, pubpages SET pub_time=pub_time+($time/1000) WHERE pubpages_id=pub_target AND pubpages_name='$path'";
	else 
		$sql="UPDATE pub, pubpages, pubref, resourcetype 
			SET pub_time=pub_time+($time/1000) 
			WHERE pubpages_id=pub_target
			AND pubref_pub=pub_id
			AND pubref_reference=resourcetype_id
			AND resourcetype_id IN (SELECT resource_type FROM resource WHERE resource_id=$resource) 
			AND pubpages_name='$path'";
	try{
		$conn->query($sql);
	} catch (Exception $e){
		echo $e->getMessage();
	}
}

function pageViews($resource){
	require_once "__dbConnect.php";
	$conn=new dbConnection();
	//DATUMO PUB
	$path=basename($_SERVER['SCRIPT_FILENAME']);
	if($resource=="")
		$sql="UPDATE pub, pubpages SET pub_pageViews=pub_pageViews+1 WHERE pubpages_id=pub_target AND pubpages_name='$path'";
	else 
		$sql="UPDATE pub, pubpages, pubref, resourcetype 
			SET pub_pageViews=pub_pageViews+1 
			WHERE pubpages_id=pub_target
			AND pubref_pub=pub_id
			AND pubref_reference=resourcetype_id
			AND resourcetype_id IN (SELECT resource_type FROM resource WHERE resource_id=$resource) 
			AND pubpages_name='$path'";
	try{
		$conn->query($sql);
	} catch (Exception $e){
		echo $e->getMessage();
	}
}

?>