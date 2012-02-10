$(function() {
	$('#beginDateText').datepick({dateFormat: 'dd/mm/yyyy'}); 
	$('#endDateText').datepick({dateFormat: 'dd/mm/yyyy'}); 
});

// Sends the checkBoxes states to the server and gets the appropriate table data
function sendChecksAndGetResult(){
	if($('#beginDateText').val() != '' && $('#endDateText').val() != ''){
		$.post(
			"hoursUsage.php", 
			{userCheck: Number($('#userCheck').attr('checked')), resourceCheck: Number($('#resourceCheck').attr('checked')), beginDate: $('#beginDateText').val(), endDate: $('#endDateText').val()},
			function(serverData){
				if(serverData.success){
					$('#resultsTable').html(serverData.tableData);
				}
			},
			"json")
			.error(
				function(error){
					// Change alert to something else
					alert(error.responseText);
				}
			)
		;
	}
	else{
		// Change alert to something else
		alert('Please pick both dates');
	}
}