$(function() {
	$('#beginDateText').datepick({dateFormat: 'dd/mm/yyyy'}); 
	$('#endDateText').datepick({dateFormat: 'dd/mm/yyyy'});
	var oTable;
	if(oTable = document.getElementById('teste')){
		oTable = $('#teste').dataTable({
			"bJQueryUI": true
			,"sPaginationType": "full_numbers"
			,'aLengthMenu': [[10, 20, 50, -1], [10, 20, 50, "All"]]
			,"iDisplayLength": -1
			,"fnFooterCallback": function(nFoot, aData, iStart, iEnd, aiDisplay){
				var columns_to_change={7: '', 8: '', 9: '', 10: ''};
				var functionName;
				var total;
				for(var j in columns_to_change) {                                   
					// var selected_column= columns_to_change[j];
					endResult=0;
					functionName = columns_to_change[j];
					if(functionName == ''){
						functionName = 'regularSum';
					}
					
					for(var i=iStart;i<iEnd;i++){ 
						// total=total+parseInt(aData[aiDisplay[i]][selected_column]);
						
						endResult = window[functionName](aData[aiDisplay[i]][j], endResult);
					}
					$($(nFoot).children().get(j)).html(endResult);
				}
				// nFoot.getEtlementsByTagName('th')[0].innerHTML = "Starting index is "+iStart;
			}
			// ,"aoColumns":[
				// { "sType": "string" }
				// ,{ "sType": "string" }
				// ,{ "sType": "string" }
				// ,{ "sType": "string" }
				// ,{ "sType": "string" }
				// ,{ "sType": "string" }
				// ,{ "sType": "string" }
				// ,{ "sType": "string" }
				// ,{ "sType": "string" }
				// ,{ "sType": "string" }
				// ,{ "sType": "string" }
			// ]
			
			// ,"aoColumns": [
				// { "sTitle": "Date"}
				// ,{ "sTitle": "ID" }
				// ,{ "sTitle": "Department"}
				// ,{ "sTitle": "User"}
				// ,{ "sTitle": "Resource" }
				// ,{ "sTitle": "ID2"}
				// ,{ "sTitle": "Project" }
				// ,{ "sTitle": "Value"}
				// ,{ "sTitle": "bla"}
				// ,{ "sTitle": "bla2" }
				// ,{ "sTitle": "bla3"}
			// ]
		});
	}
	
});

function regularSum(value, total){
	return parseFloat(value) + total;
}

function replaceSpecialCharacters(pattern) {
	var specials = new RegExp("[$+?|()\\[\\]{}\\\\]", "g"); // .*+?|()[]{}\$
	return pattern.replace(specials, "\\$&");
}

// Sends the checkBoxes states to the server and gets the appropriate table data
function sendChecksAndDate(action, departments, changeLocation){
	changeLocation = typeof changeLocation !== 'undefined' ? changeLocation : true; // default value for this parameter, javascript....sheesh
	if($('#beginDateText').val() != '' && $('#endDateText').val() != ''){
		// var dateFrom = new Date($('#beginDateText').val());
		// var dateTo = new Date($('#endDateText').val());
		// if(dateTo < dateFrom){
			// showMessage('\'From date\' is after \'To date\'', true);
			// return;
		// }
		
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
		
		if($('#projectCheck').attr('checked')){
			urlPath += "&projectCheck";
		}
		
		if(document.getElementById('adminRadio') && document.getElementById('adminRadio').checked == true){
			urlPath += "&userLevel=admin";
		}
		else if(document.getElementById('piRadio') && document.getElementById('piRadio').checked == true){
			urlPath += "&userLevel=pi";
		}
		else if(document.getElementById('respRadio') && document.getElementById('respRadio').checked == true){
			urlPath += "&userLevel=resp";
		}
		
		urlPath += "&beginDate=" + encodeURIComponent($('#beginDateText').val());
		urlPath += "&endDate=" + encodeURIComponent($('#endDateText').val());
		
		// urlPath += "&departments=" + JSON.stringify(getSelectedDepartments(getSelectedDepartments());
		urlPath += "&departments=" + JSON.stringify(departments);
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

// Gets the selected departments
function getSelectedDepartments(){
	var elemArray = document.getElementById('resultsDiv').getElementsByClassName('departmentChecks');
	var departments = new Array();
	for(var i in elemArray){
		if(elemArray[i].id != null && elemArray[i].value != null){ // fixed for chrome
			if(elemArray[i].checked){
				departments[departments.length] = elemArray[i].value;
			}
		}
	}
	
	return departments;
}

function generateReport(){
	var departments = getSelectedDepartments();
	sendChecksAndDate('generateReport', departments);
}

var noDepartmentsMsg = "Please select at least one department";
function emailDepartments(){
	var departments = getSelectedDepartments();
	if(departments.length == 0){
		showMessage(noDepartmentsMsg, true);
		return;
	}
	
	var confirmEmail = confirm('Are you sure you wish to email all the selected departments?');
	if(confirmEmail){
		sendChecksAndDate('emailDepartments', departments, false);
	}
}

function downloadFile(){
	var departments = getSelectedDepartments();
	if(departments.length == 0){
		showMessage(noDepartmentsMsg, true);
		return;
	}
	
	sendChecksAndDate('downloadFile', departments);
}