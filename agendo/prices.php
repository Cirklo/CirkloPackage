<?php
	require_once("commonCode.php");
	
	echo "<table>";
	$sql = "select resource_name, resourcetype_name, pricetype_name, price_value from resource, resourcetype, pricetype, price where price_resource = resource_id and resource_type = resourcetype_id and price_type = pricetype_id order by resource_name";
	$res = dbHelp::mysql_query2($sql) or die ($sql);
	$name = '';
	$columns = '';
	while($arr = dbHelp::mysql_row2($res)){
		echo "<tr>";
		if($name == $arr[0]){
			$columns = "<td></td><td></td>";
		}
		else{
			$columns = "	<td>
								".$arr[0]."
							</td>
							<td>
								".$arr[1]."
							</td>";
		}
		echo "<td>";
		echo $arr[2];
		echo "</td>";
		echo "<td>";
		echo $arr[3];
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
?>