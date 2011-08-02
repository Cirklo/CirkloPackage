<?php
require_once("session.php");
$user_id = startSession();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<link rel='stylesheet' type='text/css' href='css/fullcalendar.css' />
<link rel='stylesheet' type='text/css' href='css/fullcalendar.print.css' media='print'/>
<script type='text/javascript' src='js/jquery-1.5.1.js'></script>
<script type='text/javascript' src='js/jquery.cal.js'></script>
<script type='text/javascript' src='js/jquery-ui-1.8.9.custom.min.js'></script>
<script type='text/javascript' src='js/fullcalendar.js'></script>
<script type='text/javascript' src='js/gcal.js'></script>
<script type='text/javascript'>

	$(document).ready(function() {
		
		$('#calendar').fullCalendar({
			editable: false,
			events: "calendar_feed.php?regular",
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'

			},
			defaultView: 'agendaWeek',
			eventClick: function(event) {
				resp=confirm("Do you want to export this entry to your personal calendar?");
				if(resp){
					//SEND EMAIL WITH THE .ICS ATTACHED
					$.get("calendar_feed.php?export",{
						events:event
					},
					function(data){
						alert(data);
					});
				}
			},
			
			loading: function(bool) {
				if (bool) $('#loading').show();
				else	$('#loading').hide();
				
			}
			
		});
		
	});

</script>
<style type='text/css'>

	body {
		font-family:Verdana, Geneva, sans-serif; 
		font-size:12px; 
		line-height:20px; 
		color:#545353;
		text-align:center;
		}
		
	#loading {
		position: absolute;
		top: 5px;
		right: 5px;
		}

	#calendar {
		width: 800px;
		margin-left:20px;
		margin-right: auto;
		float:left;
		}
		
	#options{
		position:relative;
		top:0px;
		left:0px;
		float:left;
		border:0px solid;
		text-align:left;
		padding-top:15px;
		overflow:hidden;
		}

</style>
</head>
<body>
<?php 

require_once "__dbConnect.php";

//call database class
$conn=new dbConnection();


echo "<div id='options' style='text-align:left'>";
echo "<a href=admin.php>Return to main menu</a>";
echo "<br><br>";
echo "Select resource(s) to display<br>";
$query="SELECT DISTINCT resource_id, resource_name, color_code
	FROM entry, resource, color
	WHERE entry_resource=resource_id
	AND resource_color=color_id
	AND (entry_user=$user_id 
	OR resource_resp=$user_id)";
try{
	$sql=$conn->query($query);
	//loop through all results
	for($i=0;$row=$sql->fetch();$i++){
		echo "<li style='list-style:none;text-align:left;'><input type=checkbox name=$row[0] id=$row[0]>";
		echo "<font color=#$row[2]>$row[1]</font>";
		echo "</li>";	
	}
	echo "<br>";
	echo "<input type=button name=calRes id=calRes value='Apply changes'>";
} catch(Exception $e){
	echo "Unable to perform query";
}
echo "<br><br>";
//is this user somekind of lab manager
try{
	$sql=$conn->query("SELECT department_id, department_name FROM department WHERE department_manager=$user_id");
	if($sql->rowCount()>0){
		echo "View all entries for your department(s)<br>";
		echo "<input type=button id=managerView name=managerView value='Manager View'>";
	}
} catch (Exception $e){
	//do nothing
}
echo "<br><br>";
echo "</div>";
echo "<div id='loading' style='display:none'>loading...</div>";
echo "<div id='calendar'></div>";

?>
</body>
</html>
