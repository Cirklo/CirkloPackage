<?php
	require_once("commonCode.php");
	
	$resource = '2';
	$sql = '';
	echo "<link href='css/similar.css' rel='stylesheet' type='text/css' />";

	echo "<table id='content'>";
		echo "<tr>";
			echo "<td>";
			echo "<p>Filters</p>";
			echo "</td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td>";
				echo "<label>";
					echo "<input id='equipCheck' type='checkbox' />";
					echo "<a> Equipment type</a>";
				echo "</label>";
			echo "</td>";
		echo "</tr>";
			
		echo "<tr>";
			echo "<td>";
				echo "<label>";
					echo "<input id='respCheck' type='checkbox' />";
					echo "<a> Responsible</a>";
				echo "</label>";
			echo "</td>";
		echo "</tr>";
			
		echo "<tr>";
			echo "<td>";
				echo "<label>";
					echo "<input id='userCheck' type='checkbox' />";
					echo "<a> User</a>";
				echo "</label>";
			echo "</td>";
		echo "</tr>";
	echo "</table>";
	
	
	// $similars = array();
	// try{
		// $similars = getSimilars($resource, $similars);
		// foreach($similars as $row){
			// echo $row[0]."-".$row[1];
			// echo "<br>";
		// }
	// }
	// catch(Exception $e){
		// echo $e->getMessage();
	// }
	
	// function getSimilars($resource, $similars){
		// $sql = "
			// select 
				// similarresources_similar, 
				// resource_name 
			// from 
				// resource, 
				// similarresources
			// where 
				// similarresources_resource = ".$resource." and
				// resource_id = similarresources_similar
		// ";
		// $res = dbHelp::query($sql);
		// while($row = dbHelp::fetchRowByIndex($res)){
			// $tempResource = $row[0];
			// if(!isset($similars[$tempResource])){
				// $similars[] = $row;
				// $similars = getSimilars($tempResource, $similars);
			// }
		// }
		// return $similars;
	// }
?>