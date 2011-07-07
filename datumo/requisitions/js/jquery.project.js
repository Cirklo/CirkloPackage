
function projectInfo(account_id){	
	$('#projectGraph').html("");
	$.get("ajaxProject.php?graph",{
		account:account_id},
		function(data){
			//return the data
			var str=eval(data);
			//get info to draw graph bars
			$('#projectGraph').jqBarGraph({ 
				 data: arrayOfData, // array of data for your graph
				 height:400,
				 width:300
			 });
			
			$.get("ajaxProject.php?info",{
				account:account_id},
				function(data){
					$("#projectInfo").html(data);
					//$("#accountDetails_"+account_id).html(data);
				});
		}
	);
}
