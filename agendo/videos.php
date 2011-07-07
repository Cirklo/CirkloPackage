<?php
	$videoUrl = $_GET['videourl'];
	$width = 800;
	$height = 600;
	$margin = 25;
	
	if(isset($_GET['width']))
		$width = (int)$_GET['width'];

	if(isset($_GET['height']))
		$height = $_GET['height'];

	echo "<table id='video' width='".($width-$margin)."' height='".($height-$margin)."'>";
		echo "<tr>";
		if(isset($videoUrl)){
			try{
				echo "<td align=center>";
					echo "<iframe title='YouTube video player' width='".($width-$margin)."' height='".($height-$margin)."' src='".$videoUrl."' frameborder='0' allowfullscreen>";
					echo "</iframe>";
				echo "</td>";
			}
			catch(Exception $e) {
				echo "<td align=center>";
					echo "Something wrong with the video url - ".$videoUrl;
					echo "<br>";
					echo "Exception -".$e;
				echo "</td>";
			}
		}
		else{
			echo "<td align=center>";
				echo "Url for the video is not set.";
			echo "</td>";
		}
		echo "</tr>";
	echo "</table>";
?>