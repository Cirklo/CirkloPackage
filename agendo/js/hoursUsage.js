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
				search: 'search'
				, userCheck: Number($('#userCheck').attr('checked'))
				, resourceCheck: Number($('#resourceCheck').attr('checked'))
				, entryCheck: Number($('#entryCheck').attr('checked'))
				, beginDate: $('#beginDateText').val()
				, endDate: $('#endDateText').val()
			},
			function(serverData){
				if(serverData.success){
					$('#resultsTable').html(serverData.tableData);
				}
				else{
					showMessage(serverData.message, true);
				}
			}
			, "json")
			.error(
				function(error){
					showMessage(error.responseText, true);
				}
			)
		;
	}
	else{
		showMessage('Please pick both dates');
	}
}

// Selects/unselects all email check boxes
function selectUnselectAll(){
	var selectAllChecked = document.getElementById('allEmailsCheck').checked;
	var elemArray = document.getElementById('resultsTable').getElementsByClassName('emailChecks');
	
	for(i in elemArray){
		if(elemArray[i].id != null && elemArray[i].value){ // fixed for chrome
			elemArray[i].checked = selectAllChecked;
		}
	}
}

function email(){
	var clone;
	var total = 0;
	var managers = {}; // object, not an array, arrays => integer indexes, objects can be associative arrays
	var totals = {}; // object, not an array, arrays => integer indexes, objects can be associative arrays
	var sizes
	var elemArray = document.getElementById('resultsTable').getElementsByClassName('emailChecks');
	// Gets the checked departments to email
	for(var i in elemArray){
		if(elemArray[i].id != null && elemArray[i].value && elemArray[i].checked){ // fixed for chrome
			element = $("#" + elemArray[i].value + 'Table');
			clone = element.clone().wrap('<table>').parent();
			clone.find("#" + elemArray[i].value + '-EmailCheck').attr("disabled", true);
			currentManager = element.attr("summary");
			if(managers[currentManager] == null){
				// Needs to be an array, otherwise it wouldnt be possible to get the length property (yep javascript is THAT bad!!)
				managers[currentManager] = new Array();
				totals[currentManager] = 0;
			}
			managers[currentManager][managers[currentManager].length] = clone.html();
			totals[currentManager] += Number(clone.find("#" + elemArray[i].value + 'SubTotal').attr("name"));
		}
	}

	// Convert multidimensional array to a string
	managers = JSON.stringify(managers);
	
	$.post(
		"hoursUsage.php", 
		{
			emailManagers: 'emailManagers'
			, 'managers': managers
			, totals: totals
		},
		function(serverData){
			showMessage(serverData.message, !serverData.success);
		}
		, "json")
		.error(
			function(error){
				showMessage(error.responseText, true);
			}
		)
	;
}

// Return an array with all the information regarding a department
function getDepartmentData(department){
	var departmentData = new Array();
	var linesAndRows = new Array();
	
	
	var subtotal = document.getElementById(department + 'SubTotal').name;
	departmentData['subTotal'] = subTotal;
	
	return departmentData;
}