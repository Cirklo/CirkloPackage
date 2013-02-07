var oTable;

$(function() {
	$('#beginDateText').datepick({dateFormat: 'dd/mm/yyyy'}); 
	$('#endDateText').datepick({dateFormat: 'dd/mm/yyyy'});
	
	if(oTable = $('#datatable')){
		$.fn.dataTableExt.oApi.fnReloadAjax = function ( oSettings, sNewSource, fnCallback, bStandingRedraw ){
			if ( typeof sNewSource != 'undefined' && sNewSource != null ) {
				oSettings.sAjaxSource = sNewSource;
			}
		 
			// Server-side processing should just call fnDraw
			if ( oSettings.oFeatures.bServerSide ) {
				this.fnDraw();
				return;
			}
		 
			this.oApi._fnProcessingDisplay(oSettings, true);
			var that = this;
			var iStart = oSettings._iDisplayStart;
			var aData = [];
		  
			this.oApi._fnServerParams( oSettings, aData );
			  
			oSettings.fnServerData.call( oSettings.oInstance, oSettings.sAjaxSource, aData, function(json) {
				/* Clear the old information from the table */
				that.oApi._fnClearTable( oSettings );
				  
				/* Got the data - add it to the table */
				var aData =  (oSettings.sAjaxDataProp !== "") ?
					that.oApi._fnGetObjectDataFn( oSettings.sAjaxDataProp )( json ) : json;
				  
				for ( var i=0 ; i<aData.length ; i++ ){
					that.oApi._fnAddData( oSettings, aData[i] );
				}
				  
				oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
				  
				if ( typeof bStandingRedraw != 'undefined' && bStandingRedraw === true ){
					oSettings._iDisplayStart = iStart;
					that.fnDraw( false );
				}
				else{
					that.fnDraw();
				}
				  
				that.oApi._fnProcessingDisplay( oSettings, false );
				  
				/* Callback user function - for event handlers etc */
				if ( typeof fnCallback == 'function' && fnCallback != null ){
					fnCallback( oSettings );
				}
			}, oSettings );
		};		
	
		oTable.dataTable({
			"bJQueryUI": true
			,"bFilter": false
			,"sPaginationType": "full_numbers"
			,'aLengthMenu': [[10, 20, 50, 100, 200, -1], [10, 20, 50, 100, 200, 'All']]
			// ,"iDisplayLength": 100
			,"iDisplayStart": 0
			,"bProcessing": true
			,"bServerSide": true
			,"sServerMethod": "POST"
			,"sAjaxSource": "hourUsageSQL.php"
			,"fnServerData": function ( sSource, aoData, fnCallback ) {
				aoData.push( { "name": "action", "value": 'generateJson' });
				aoData.push( { "name": "searchField", "value": $('#searchField').val() });
				aoData.push( { "name": "beginDate", "value": $('#beginDateText').val() });
				aoData.push( { "name": "endDate", "value": $('#endDateText').val() });
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
			// ,"fnFooterCallback": function(nFoot, aData, iStart, iEnd, aiDisplay){
				// var columns_to_change = {
					// 4: ['usageSum', 'usageEndResult']
					// ,6: ['regularSum','regularEndResult']
					// ,7: ['regularSum', 'regularEndResult']
					// ,9: ['regularSum', 'regularEndResult']
				// };
				// var functionName;
				// var total;
				// for(var j in columns_to_change){                                   
					// end_result = 0;
					// functionName = columns_to_change[j][0];
					// for(var i in aData){
						// end_result = window[functionName](aData[i][j], end_result);
					// }

					// functionName = columns_to_change[j][1];
					// $($(nFoot).children().get(j)).html(window[functionName](end_result));
				// }
			// }
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
				// ,{ "sTitle": "ID"}
				// ,{ "sTitle": "Department"}
				// ,{ "sTitle": "User"}
				// ,{ "sTitle": "Resource" }
				// ,{ "sTitle": "ID2"}
				// ,{ "sTitle": "Project" }
				// ,{ "sTitle": "Value"}
				// ,{ "sTitle": "bla"}
				// ,{ "sTitle": "bla2"}
				// ,{ "sTitle": "bla3"}
			// ]
		});
		
			
	}

});

function usageSum(value, total){
	if(total == 0){
		total = new Array(0, 0);
	}
	var valueArray = value.match(/(\d+)\s*h :\,?\s*(\d+)\s*m?/);
	total[0] += parseInt(valueArray[1]);
	total[1] += parseInt(valueArray[2]);
	return total;
}

function usageEndResult(end_result){
	return parseInt(end_result[0] + end_result[1]/60) + "h : " + end_result[1]%60 + "m";
}

function regularSum(value, total){
	return parseFloat(value) + total;
}

function regularEndResult(end_result){
	return end_result;
}

function synchInfo(e){
	if (e.keyCode == 13) {
		oTable.fnReloadAjax();
		return false;
	}
	return true;
}
		
// Sends the checkBoxes states to the server and gets the appropriate table data
function sendChecksAndDate(action, departments, changeLocation){
	changeLocation = typeof changeLocation !== 'undefined' ? changeLocation : true; // default value for this parameter, javascript....sheesh
	// if($('#beginDateText').val() != '' && $('#endDateText').val() != ''){
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
	// }
	// else{
		// showMessage('Please pick both dates');
	// }
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