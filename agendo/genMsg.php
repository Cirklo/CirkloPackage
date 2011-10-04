<?php


class genMsg {
	
	function announcement ($resource){
		$sql = "SELECT announcement_date, announcement_title, announcement_message, announcement_end_date FROM announcement WHERE announcement_object=$resource AND announcement_end_date > ".dbHelp::now();
		// $sql = "SELECT announcement_date, announcement_title, announcement_message, announcement_end_date FROM announcement WHERE announcement_object=$resource AND announcement_end_date>now()";
		$res = dbHelp::query($sql) or die ($sql); //mysql_error().$sql);
		if(dbHelp::numberOfRows($res) > 0){
			while($row = dbHelp::fetchRowByIndex($res)){
				echo "<b>".$row[0]."</b>: ".$row[1]."<br>";
				echo $row[2]."<br>";
				echo "<b>Available until: </b>".$row[3]."<br><br>";
			}
		} else {
			echo "No announcements for this resource!";
		}
	}

}


?>