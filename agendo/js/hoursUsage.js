$(function() {
	$('#beginDateText').datepick({dateFormat: 'dd/mm/yyyy'}); 
	$('#endDateText').datepick({dateFormat: 'dd/mm/yyyy'});
	
	// alert(("2 Days, 3 hours, 5 minutes").match(/(\d+)\s*days?\,?\s*(\d+)\s*hours?\,?\s*(\d+)\s*minutes?/i));
	// alert(("3h : 30m").match(/(\d+)\s*h :\,?\s*(\d+)\s*m?/));
	// alert(("20h 30m").match(/\d+, ?\s*\d+/));
	
	var oTable;
	if(oTable = $('#datatable')){
		oTable.dataTable({
			"bJQueryUI": true
			,"sPaginationType": "full_numbers"
			,'aLengthMenu': [[10, 20, 50, 200], [10, 20, 50, 200]]
			,"iDisplayLength": 200
			,"bProcessing": true
			,"bServerSide": true
			,"sServerMethod": "POST"
			,"sAjaxSource": "hoursUsage.php"
			,"fnServerData": function ( sSource, aoData, fnCallback ) {
				aoData.push( { "name": "action", "value": 'generateJson' } );
				$.post(
					sSource
					,aoData
					,function(serverData){
						showMessage(serverData.message, serverData.isError);
					}
					,'json'
				)
				.success(
					fnCallback
				);
			}
			,"fnFooterCallback": function(nFoot, aData, iStart, iEnd, aiDisplay){
				// column to change, iteration function, end result presentation function
				var columns_to_change={
					4: ['usageSum', 'regularEndResult']
					,6: ['regularSum','regularEndResult']
					,7: ['regularSum', 'regularEndResult']
					,9: ['regularSum', 'regularEndResult']
				};
				var functionName;
				var total;
				for(var j in columns_to_change) {                                   
					// var selected_column= columns_to_change[j];
					end_result = 0;
					functionName = columns_to_change[j][0];
					for(var i=iStart; i<iEnd; i++){ 
						end_result = window[functionName](aData[aiDisplay[i]][j], end_result);
					}

					functionName = columns_to_change[j][1];
					$($(nFoot).children().get(j)).html(window[functionName](end_result));
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

function usageSum(value, total){
	if(total = 0){
		total = [0, 0];
	}
	var valueArray = value.match(/(\d+)\s*h :\,?\s*(\d+)\s*m?/);
	total[0] += valueArray[1];
	console.log(total[0] + " " + valueArray[1]);
	total[1] += valueArray[2];
	return total;
}

function usageEndResult(end_result){
	return end_result[0] + "h : " + end_result[1] + "m";
}

function regularSum(value, total){
	return parseFloat(value) + total;
}

function regularEndResult(end_result){
	return end_result;
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