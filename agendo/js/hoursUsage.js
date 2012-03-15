$(function() {
	$('#beginDateText').datepick({dateFormat: 'dd/mm/yyyy'}); 
	$('#endDateText').datepick({dateFormat: 'dd/mm/yyyy'}); 
});

// Sends the checkBoxes states to the server and gets the appropriate table data
function sendChecksAndDate(action, changeLocation){
	changeLocation = typeof changeLocation !== 'undefined' ? changeLocation : true; // default value for this parameter, javascript....sheesh
	if($('#beginDateText').val() != '' && $('#endDateText').val() != ''){
		urlPath = "hoursUsage.php?action=" + action;
		
		if($('#userCheck').attr('checked')){
			urlPath += "&userCheck";
		}
		
		if($('#resourceCheck').attr('checked')){
			urlPath += "&resourceCheck";
		}
		
		if($('#entryCheck').attr('checked')){
			urlPath += "&entryCheck";
		}
		
		urlPath += "&beginDate=" + encodeURIComponent($('#beginDateText').val());
		urlPath += "&endDate=" + encodeURIComponent($('#endDateText').val());
		
		urlPath += "&departments=" + JSON.stringify(getSelectedDepartments());
		urlPath += "&changeLocation=" + changeLocation;
		if(changeLocation){
			window.location = urlPath;
		}
		else{
			$.get(
				urlPath,
				function(serverData){
					showMessage(serverData.message, !serverData.success);
				},
				"json")
				.error(
					function(error){
						showMessage(error.responseText, true);
					}
				)
			;
		}
	}
	else{
		showMessage('Please pick both dates');
	}
}

// Selects/unselects all email check boxes
function selectUnselectAll(element){
	var elemArray = document.getElementsByClassName('allCheck');
	for(i in elemArray){
		if(elemArray[i].id != null && elemArray[i].value){ // fixed for chrome
			elemArray[i].checked = element.checked;
		}
	}

	var elemArray = document.getElementById('resultsDiv').getElementsByClassName('departmentChecks');
	for(i in elemArray){
		if(elemArray[i].id != null && elemArray[i].value){ // fixed for chrome
			elemArray[i].checked = element.checked;
		}
	}
}

function email(){
	urlPath = "hoursUsage.php?departments=" + JSON.stringify(getSelectedDepartments());
	
	$.post(
		urlPath, 
		{
			emailManagers: 'emailManagers'
		},
		function(serverData){
			showMessage(serverData.message, !serverData.success);
		},
		"json")
		.error(
			function(error){
				showMessage(error.responseText, true);
			}
		)
	;
}

// Gets the selected departments
function getSelectedDepartments(){
	var elemArray = document.getElementById('resultsDiv').getElementsByClassName('departmentChecks');
	var departments = new Array();
	for(var i in elemArray){
		if(elemArray[i].id != null && elemArray[i].value != null && elemArray[i].checked){ // fixed for chrome
			departments[departments.length] = elemArray[i].value;
		}
	}
	return departments;
}