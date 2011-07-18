	<?php
        $caldb = "requisitions"; //change this if the database has a different name
	$link = mysql_connect("localhost", "root", "equip!admin") or 
die("not connected");
	mysql_query("SET NAMES utf8") or die ("Unable to change encoding:" . mysql_errmsg());
	mysql_select_db($caldb) or die("Could not select database");
        
	
        function database($type){
            switch($type){
                case 0:
                    $db = "information_schema";
                    break;
                case 1:
                    $db = "requisitions"; //change this if the database has a different name
                    break;
            }
            return $db;
        }
        
	?>
