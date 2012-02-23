$(function() {
	$('#beginDateText').datepick({dateFormat: 'dd/mm/yyyy'}); 
	$('#endDateText').datepick({dateFormat: 'dd/mm/yyyy'}); 
});

// Sends the checkBoxes states to the server and gets the appropriate table data
function sendChecksAndGetResult(){
	if($('#beginDateText').val() != '' && $('#endDateText').val() != ''){
		$.post(
			"hoursUsage.php", 
			{
				userCheck: Number($('#userCheck').attr('checked'))
				, resourceCheck: Number($('#resourceCheck').attr('checked'))
				, entryCheck: Number($('#entryCheck').attr('checked'))
				, beginDate: $('#beginDateText').val()
				, endDate: $('#endDateText').val()
			},
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

// Selects/unselects all email check boxes
function selectUnselectAll(){
	var selectAllChecked = document.getElementById('allEmailsCheck').checked;
	var elemArray = document.getElementById('resultsTable').getElementsByClassName('emailChecks');
	
	for(i in elemArray){
		if(elemArray[i].id != null && elemArray[i].value){ // fix for chrome
			elemArray[i].checked = selectAllChecked;
		}
	}
}

function email(){
	var departmentsArray = new Array();
	var elemArray = document.getElementById('resultsTable').getElementsByClassName('emailChecks');
	
	// Gets the selected departments to email
	for(i in elemArray){
		if(elemArray[i].id != null && elemArray[i].value && elemArray[i].checked){ // fix for chrome
			departmentsArray.push(elemArray[i].value);
		}
	}

	var subTotal;
	// Iterates through the several departments (the ones checked)
	for(i in departmentsArray){
		subTotal = document.getElementById(departmentsArray[i] + 'SubTotal').name;
		alert(subTotal);
		// alert(departmentsArray[i]);
	}
}

// Return an array with all the information regarding a department
function getDepartmentData(department){
	var departmentData = new Array();
	var linesAndRows = new Array();
	
	
	var subtotal = document.getElementById(department + 'SubTotal').name;
	departmentData['subTotal'] = subTotal;
	
	return departmentData;
}