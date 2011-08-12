<?php 

//php includes
require_once "session.php";
startSession();

//get https vars in order to build the plot
if(isset($_GET['plot_id'])){
	$plot_id=$_GET['plot_id'];
} 
?>
<!DOCTYPE html>
<html>
<head>
    <title>
    Plot
</title>
    <script src="js/jquery-1.5.1.js" type="text/javascript"></script>
    <script src="js/jquery.jqChart.min.js" type="text/javascript"></script>
    <!--[if IE]><script lang="javascript" type="text/javascript" src="js/excanvas.js"></script><![endif]-->    
    <script lang="javascript" type="text/javascript">
        function plot(plot_id) {
            $.getJSON("plotAux.php",{
                type:0,
                plot_id:plot_id
                }, function(data){
		            $('#jqChart').jqChart({
		                title: { 
			                text: data.title 
				        },
		                series: [
		                            {
		                                type: data.type,	//set data type:column or spline
		                                //title: data.desc,
		                                data: data.value	//write data to plot
		                            }
		                        ]
		            });
            });
        };
    </script>
</head>

<body onload="plot(<?php echo $plot_id;?>);">
    <div>
        <div id="jqChart" style="min-width:800px;height:500px;" />
    </div>
</body>
</html>


