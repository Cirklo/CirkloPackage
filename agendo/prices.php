<?php
	session_start();
	// $pathOfIndex = explode('\\',str_replace('/', '\\', getcwd()));
	// $_SESSION['path'] = "../".$pathOfIndex[sizeof($pathOfIndex)-1];
	require_once("commonCode.php");
	try{
		echo "<link href='../agendo/css/intro.css' rel='stylesheet' type='text/css' />";
		echo "<table style='margin:auto;'>";
		// $sql = "select resource_name, type_name, pricetype_name, price_value from resource, type, pricetype, price where price_resource = resource_id and resource_type = type_id and price_type = pricetype_id order by resource_name";
		$sql = "SELECT resource_name, resourcetype_name, pricetype_name, price_value FROM resource, resourcetype, pricetype, price WHERE price_resource = resource_id AND resource_type = resourcetype_id AND price_type = pricetype_id ORDER BY resource_name, pricetype_name";
		$res = dbHelp::mysql_query2($sql);
		$name = '';
		if(dbHelp::mysql_numrows2($res) > 0){
			echo "<tr>";
				echo "<td>";
				echo "Resource";
				echo "</td>";
				
				echo "<td>";
				echo "Resource Type";
				echo "</td>";
				
				echo "<td>";
				echo "Type";
				echo "</td>";
				
				echo "<td>";
				echo "Price/Hour";
				echo "</td>";
			echo "</tr>";

			while($arr = dbHelp::mysql_fetch_row2($res)){
				echo "<tr>";
				if($name == $arr[0]){
					echo "<td></td><td></td>";
				}
				else{
					echo "<tr>";
						echo "<td colspan=4><hr style='width:100%'></td></tr>";
						$name = $arr[0];
						echo "<td>".$arr[0]."</td><td>".$arr[1]."	</td>";
				}
					echo "<td>";
					echo $arr[2];
					echo "</td>";
					
					echo "<td>";
					echo $arr[3]." Euros";
					echo "</td>";
					
				echo "</tr>";
			}
		}
		else{
			echo "<tr><td>";
			echo "<text style='text-color:white;'>There are no results</text>";
			echo "</td></tr>";
		}
		echo "</table>";
	}
	catch(Exception $e){
		showMsg($e->getMessage(), false, true);
	}
?>