<?php


class genMsg {
	
	function announcement ($resource){
		$sql = "SELECT announcement_date, announcement_title, announcement_message, announcement_end_date FROM announcement WHERE announcement_object=$resource AND announcement_end_date>now()";
		$res = dbHelp::mysql_query2($sql) or die ($sql); //mysql_error().$sql);
		if(dbHelp::mysql_numrows2($res) > 0){
			while($row = dbHelp::mysql_fetch_row2($res)){
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