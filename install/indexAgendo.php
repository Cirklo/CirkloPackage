<?php
	
	/**
	 * 
	 * @author Nuno Moreno, Joao Lagarto, Pedro Pires
	 * @abstract Agendo homepage. Display most used resources
	 */

	session_start();
	$pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
	$_SESSION['path'] = "../".$pathOfIndex[sizeof($pathOfIndex)-1];
	require_once("../agendo/commonCode.php");
	initSession();
	require_once("../agendo/functions.php");
	// logIn();
	
	if(isset($_POST['functionName'])){
		call_user_func(cleanValue($_POST['functionName']));
		exit;
	}
	importJs();
?>

<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link href="../agendo/css/intro.css" rel="stylesheet" type="text/css" media="screen" />
<link href="../agendo/css/common.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="../agendo/js/ajax.js"></script>
<script type="text/javascript">
function noenter() {
  return !(window.event && window.event.keyCode == 13);
}

// Checks if a field is filled, if its empty returns true
function checkfield(field) {   
    if (field.value=='') {
        field.focus();
        alert("Field " + field.name + " required!");
        //window.location.reload();
        // exit;
        return true;
    }
    return false;
}
</script>

<html xmlns="http://www.w3.org/1999/xhtml">
<body>
<?php

// echo "<div id='logo' class='logo' style='background:url(".$_SESSION['path']."/pics/header.png) no-repeat left top;'>";
// agendo Resource Schedule
// echo "<div id='logo' class='logo'>";
echo "<table id='master' style='width:800' align=center>";
	echo "<tr><td>";
		echo "<table class='logo' style='height:100%' border=0>";
			echo "<tr>";
				$imgWidth=200;
				echo "<td style='font-size:11px;text-align:left;padding:0px;margin:0px' width='".$imgWidth."'>";
				echo "<img width='".$imgWidth."' style='cursor:pointer;' id=agendo title='agendo' src=pics/agendo.png onmouseover=\"this.src='pics/agendo_.png'\" onmouseout=\"this.src='pics/agendo.png'\" onclick=\"javascript:window.open('https://agendo.cirklo.org')\" />";
				echo "</td>";
				
				echo "<td align='right' >";
				echo "<table style='height:100%;width:100%'>";
					echo "<tr valign='top'>";
						echo "<td align='right'>";
						loggedInAs('index', 'null');
						echo "</td>";
					echo "</tr>";
					
					$sqlInst = "SELECT configParams_name, configParams_value from configParams where configParams_name='institute'";
					$resInst = dbHelp::query($sqlInst);
					$arrInst = dbHelp::fetchRowByIndex($resInst);
					echo "<tr>";
						echo "<td align='left' style='padding-left:10px;padding-bottom:14px;vertical-align:bottom;'>";	
						echo "<font size=5px style='color:#F7C439'><b>".$arrInst[1]."</b></font><br>";
						echo "<font size=4x style='color:#F7C439'>Resource Scheduler</font>";
						echo "</td>";	
					echo "</tr>";
				echo "</table>";
				echo "</td>";	
			echo "</tr>";
			
			echo "<tr>";
				echo "<td align='center'>";
				echo "<font color=#FFF size=1.5px>Powered by <a href='http://www.cirklo.org' style='color:#F7C439' target='_blank'>Cirklo</a></font>";
				echo "</td>";
				
				echo "<td align='right'>";
				echoUserVideosResourceHelpLinks();
				echo "</td>";
			echo "</tr>";
		echo "</table>";
			
		// echo "</td></tr>";
	// echo "</div>";

	// Videos div
	echoVideosDiv();

	// Resources div
	echoResourcesDiv();

	// Group View
	require_once('../agendo/monitoring.php');
	
	// User/management div
	echoUserDiv('index', 'null');

$class = null;
// Shows a specific resource
if (isset($_GET['class'])) {
    $class = (int)($_GET['class']);
    // $sql="SELECT sum(entry_slots*resource_resolution) e,resource_name, resource_id from entry, resource where resource_id=entry_resource and entry_status in (1,2) group by resource_name,resource_id order by e desc";
	$extra = "and resource_type='".$class."' order by resource_name";
    if ($class==0){
        // $sql="select 1,resource_name,resource_id from resource order by resource_name";
        $extra = "order by resource_name";
	}
	$sql="select resource_name,resourcetype_name, resstatus_name, resource_id from resource, resstatus, resourcetype where resource_type = resourcetype_id and resource_status = resstatus_id and resstatus_id not in (0, 2) ".$extra;
    // $limit='';
    // $datefilter='';
// Shows most used resources filtered by a month of use
} else {
    $sql="SELECT sum(entry_slots*resource_resolution) e,resource_name, resource_id from entry, resource where resource_id=entry_resource and resstatus_id not in (0, 2) and entry_status in (1,2) group by resource_name,resource_id order by e desc limit 10";
    // $datefilter=" and entry_datetime between ".dbHelp::date_sub('now()', '1', 'month')." and now()";
    $datefilter=" and entry_datetime between ".dbHelp::date_sub(dbHelp::now(), '1', 'month')." and ".dbHelp::now();
}

	// echo "<div class=logo>";
	echo "<table class=equilist>";
		$smallScript = "style='cursor:pointer' onclick='showMessage(\"Please sign in to be able to access resources.\")'";
		if(!isset($class)){
			echo "<tr>";
				echo "<td class=title_>Most Used Resources</td>";
				echo "<td class=title_ >Share</td>";
			echo "</tr>";
			$sql="SELECT sum(entry_slots*resource_resolution) e, resource_name, resource_id from entry, resource where resource_id = entry_resource and resource_status not in (0, 2) and entry_status in (1,2) ".$datefilter." group by resource_name order by e desc limit 15";
			$res=dbHelp::query($sql) or die ($sql);
			for($i=0;$arr=dbHelp::fetchRowByIndex($res);$i++){
				if ($i==0) $max = $arr[0];
				echo "<tr>";
					$varFlag = "href=weekview.php?resource=" . $arr[2];
					// $smallScript = "style='cursor:pointer' onclick='$.jnotify(\"Please sign in to be able to access resources.\")'";
					if(!secureIpSessionLogin()){
						$varFlag = $smallScript;
					}

					echo "<th ><a ".$varFlag." class='asd'>".$arr[1]."</a></th>";
					echo "<td style='height:18px;'>";
					echo "<img src=pics/scale.gif height=100% width=".($arr[0]/$max*100)."%'/>";
					echo "</td>";
				echo "</tr>";
			}
		}
		else {
			echo "<tr>";
				echo "<td class=title_>All Resources</td>";
				echo "<td class=title_>Resource Type</td>";
				echo "<td class=title_ >Permission Type</td>";
			echo "</tr>";
			$resResource=dbHelp::query($sql);
			while($arrResource=dbHelp::fetchRowByIndex($resResource)){
				$varFlag = "href=weekview.php?resource=" . $arrResource[3];
				// $smallScript = "style='cursor:pointer' onclick='$.jnotify(\"Please sign in to be able to access resources.\")'";
				if(!secureIpSessionLogin()){
					$varFlag = $smallScript;
				}

				// echo "<th><a href=weekview.php?resource=" . $arrResource[3]. " class='asd'>" . $arrResource[0] .  "</a></th>\n";
				echo "<th><a ".$varFlag." class='asd'>" . $arrResource[0] .  "</a></th>\n";
				echo "<td>".$arrResource[1]."</td>\n";
				echo "<td style='height:18px;'>".$arrResource[2]."</td>\n";
				echo "</tr>";
			}
		}
	echo "</table>";//</div>";

		echo "<table style='width:100%'>";
			echo "<tr>";
				echo "<td align=left valign=top>";
					// echo "<a href='http://www.facebook.com/pages/Cirklo/152674671417637?ref=ts'>";
						// echo "<img src=pics/facebook_logo.png border=0>";
					// echo "</a>";
					// echo "<a href='http://twitter.com/cirklo2010'>";
						// echo "<img src=pics/twitter_logo.png border=0>";
					// echo "</a>";
				echo "<div>";
					echo "<span ><a href='http://www.facebook.com/pages/Cirklo/152674671417637' target=_blank><img src=pics/fb.png width=45px  border=0 title='Visit our Facebook page'></a></span>";
					echo "&nbsp;";
					//twitter -> Why the hell do we need this?
					echo "<span ><a href='http://www.twitter.com/cirklo2010' target=_blank><img src=pics/twitter.png width=45px border=0 title='Follow us at Twitter'></a></span>";
					echo "&nbsp;";
					echo "&nbsp;";
					//You tube feature videos
					echo "<span ><a href='http://www.youtube.com/user/agendocirklo' target=_blank><img src=pics/youtube.png width=45px border=0 title='Feature videos'></a></span>";
				echo "</div>"; 			
				echo "<font color=#FFFFFF size=1.5px>Tested on</font>";
				echo "<a href=http://www.mozilla-europe.org style='text-decoration:none'><font color=#FFFFFF size=1.5px> Firefox, </font></a>";
				echo "<a href=http://www.google.com/chrome style='text-decoration:none'><font color=#FFFFFF size=1.5px> Chrome, </font></a><br>";
				echo "<a href=http://www.apple.com/safari style='text-decoration:none'><font color=#FFFFFF size=1.5px> Safari, </font></a>";
				echo "<a href=http://www.apple.com/ios style='text-decoration:none'><font color=#f6961a size=1.5px> iOS, </font></a>";
				echo "<a href=http://www.android.com style='text-decoration:none'><font color=#f6961a size=1.5px> Android</font></a><br>";
				$resVersion=dbHelp::query("select configParams_value from configParams where configParams_name = 'AgendoVersion'");
				$arrVersion = dbHelp::fetchRowByIndex($resVersion);
				echo "<font color=#F7C439 size=1.5px>Agendo version ".$arrVersion[0]."</font>";
				echo "</td>";
				echo "<td>";
					echo "<p style='text-align:right'>A user cannot damage any resource or, through his/her inaction, allow somebody else to damage it<br>";
					echo "A user must follow any given instructions from the staff, except if such instructions conflicts with the First Law<br>";
					echo "A user must take care of her/his project related items unless this conflicts with the First and Second Laws";
					echo "<p>Adapted from \"I, Robot\" (Isaac Asimov)";
				echo "</td>";
			echo "</tr>";
		echo "</table>";

	echo "</td></tr>";
echo "</table>";
?>