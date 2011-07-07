
<link href="css/monitor.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="js/jquery-1.6.js"></script>
<script type="text/javascript" src="js/monitor.js"></script>
<script type="text/javascript" src="js/jquery.flot.js"></script>
<script type="text/javascript" src="js/jquery.flot.selection.js"></script>
<script type="text/javascript" src="js/jquery.flot.crosshair.js"></script>
<script type="text/javascript" src="js/jquery.flot.threshold.js"></script>


<?php

require_once "../Datumo2.0/.htconnect.php";

//call database class 
$conn=new dbConnection();
//monitored equipment
$query="SELECT DISTINCT resource_id, resource_name FROM resource,equip WHERE resource_id=equip_resourceid ORDER BY resource_name";
//loop through all results of the query...if any
echo "<fieldset class=resource>";
echo "<legend>Ekrano remote monitoring</legend>";
echo "Monitored equipment<br>";
echo "<select name=resource id=resource onchange=resourceSelect()>";
echo "<option id=0 selected>Select a resource...</option>";
foreach($sql=$conn->query($query) as $row){
	echo "<option value=$row[0]>$row[1]</option>";
}
echo "</select>";
echo "<br><br>";
echo "Get measurements from<br>";
echo "<select name=time id=time onchange=getValuesToPlot()>";
echo "<option value=1 selected>Last 24 hours</option>";
echo "<option value=2>2 days</option>";
echo "<option value=3>3 days</option>";
echo "<option value=7>Last week</option>";
echo "</select>";
echo "<br><br>";
echo "<input type=button id=upd name=upd value=Update onclick=getValuesToPlot()>";
echo "<br>";
echo "<div id=resource_display style='float:left;'>";
echo "</div>";
echo "</fieldset>";


?>