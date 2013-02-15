var oTable;
var filter_reset_text = 'Click on the links below to filter';
var filter_display_array = {};
var filter_post_array = {};

$(function() {
	// $('#beginDateText').datepick({dateFormat: 'dd/mm/yyyy'}); 
	// $('#endDateText').datepick({dateFormat: 'dd/mm/yyyy'});
	$('#beginDateText').datepicker({dateFormat: 'dd/mm/yy'}); 
	$('#endDateText').datepicker({dateFormat: 'dd/mm/yy'});
	$('#filterText').text(filter_reset_text);
	
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
			,"sAjaxSource": "hoursUsage.php"
			,"fnServerData": function ( sSource, aoData, fnCallback ) {
				aoData.push( { "name": "action", "value": 'generateJson' });
				aoData.push( { "name": "searchField", "value": $('#searchField').val() });
				aoData.push( { "name": "beginDate", "value": $('#beginDateText').val() });
				aoData.push( { "name": "endDate", "value": $('#endDateText').val() });
				aoData.push( { "name": "filters", "value": JSON.stringify(filter_post_array) });
				
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
				var columns_to_change = {
					// 5: ['usageSum', 'usageEndResult']
					5: ['regularSum', 'regularEndResult']
					,8: ['regularSum', 'regularEndResult']
					,9: ['regularSum', 'regularEndResult']
					,10: ['regularSum', 'regularEndResult']
				};
				var functionName;
				var total;
				for(var j in columns_to_change){                                   
					end_result = 0;
					functionName = columns_to_change[j][0];
					for(var i in aData){
						end_result = window[functionName](aData[i][j], end_result);
					}

					functionName = columns_to_change[j][1];
					$($(nFoot).children().get(j)).html(window[functionName](end_result));
				}
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
			,"aoColumns": [
				{ "sWidth": "20%"}
				,{ "sWidth": "10%"}
				,{ "sWidth": "15%"}
				,{ "sWidth": "10%"}
				,{ "sWidth": "15%"}
				,{ "sWidth": "5%"}
				,{ "sWidth": "5%"}
				,{ "sWidth": "5%"}
				,{ "sWidth": "5%"}
				,{ "sWidth": "5%"}
				,{ "sWidth": "5%"}
			]
		});
		
			
	}

});

// column footer functions *****************************************************

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
	var hours = end_result[0] || 0;
	var minutes = end_result[1] || 0;
	
	return parseInt(hours + minutes/60) + "h : " + minutes%60 + "m";
}

function regularSum(value, total){
	return parseFloat(value) + total;
}

function regularEndResult(end_result){
	return end_result;
}

// *****************************************************************************

function synchInfo(e){
	if (e.keyCode == 13) {
		oTable.fnReloadAjax();
		return false;
	}
	return true;
}


function filterRefresh(){
	var element = document.getElementById('filterText');
	
	if(isObjEmpty(filter_display_array)){
		element.innerHTML = filter_reset_text;
	}
	else{
		element.innerHTML = "Remove filter: ";
			
		// element.innerHTML += filterArray.join(', ');
		var separator = ', ';
		var text_to_add = "";
		for(var i in filter_display_array){
			text_to_add += "<a class='link' onclick='removeFromFilter(" + i + ");'>" + filter_display_array[i] + "</a>, ";
		}
		text_to_add = text_to_add.substring(0, text_to_add.length - separator.length);
		element.innerHTML += text_to_add;
	}
}

function filter(data_id, value, column_index){
	var element = document.getElementById('filterText');
	filter_post_array[column_index] = data_id;
	filter_display_array[column_index] = value;
	filterRefresh();
	showMessage('Filter added');
	oTable.fnReloadAjax();
}

function resetFilter(){
	filter_post_array = {};
	filter_display_array = {};
	$('#filterText').text(filter_reset_text);
	showMessage('Filter has been reset');
}

function removeFromFilter(column_index){
	delete filter_post_array[column_index];
	delete filter_display_array[column_index];
	filterRefresh();
	showMessage('Filter has been removed');
	oTable.fnReloadAjax();
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