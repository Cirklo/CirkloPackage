<?php

//PHP includes
require_once "session.php";
$user_id=startSession();
require_once "__dbConnect.php";


if(isset($_GET['type'])){
	$type=$_GET['type'];
	switch ($type){
		case 0:
			//build plot
			plot();
			break;
		
	}
}


function plot(){
	//http vars
	if(isset($_GET['plot_id'])){
		$plot_id=$_GET['plot_id'];
	}
	
	//call database class
	$conn=new dbConnection();

	//query to retrieve plot information
	$query="SELECT * FROM plot WHERE plot_id=$plot_id";
	$sql=$conn->query($query);
	$row=$sql->fetch();
	
	//plot settings
	$json->title=$row["plot_title"];
	$json->type=$row["plot_type"];
		
	//info to build query to get plot data 
	$x_axis=$row["plot_x_axis"];	
	$y_axis=$row["plot_y_axis"];
	$table=$row["plot_table"];
	
	//retrieving data to build plot
	$query="SELECT $x_axis, $y_axis FROM $table";
	$sql=$conn->query($query);
		
	//loop through every row of the table
	for($i=0;$row=$sql->fetch();$i++){
		$json->value[]=array($row[0],(int)$row[1]);	//store each pair in order to build a big array
	}
	
	//set it to json format
	$arr=json_encode($json);
	echo $arr;	//output to javascript
}

function checkPlot($objName){
	//call database class
	$conn=new dbConnection();
	
	//check if there is any plot related with this table
	$query="SELECT plot_id,plot_title FROM plot WHERE plot_table='$objName'";
	$sql=$conn->query($query);
	if($sql->rowCount()){	//there is at least one plot
		echo "<tr><td><br></td></tr>";
		echo "<tr><td><a href=javascript:void(0) title='Plots available'>Plots</a>";
		echo "<div class=sidebar id=plotDiv>";
		for($i=0;$row=$sql->fetch();$i++){
			echo "<a href=javascript:void(0) onclick=window.open('plot.php?plot_id=$row[0]','_blank','width=820px,height=550px,menubar=yes')>$row[1]</a><br>";
		}
		echo "</div>";
		echo "</td></tr>";
	}
	
}


?>