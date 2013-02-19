var oTable;
var filter_reset_text = 'Click on the links below to filter';
var filter_display_array = {};
var filter_post_array = {};
var defaultSearchText = 'Search...';
var lastSearch = "";

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
			// ,"sServerMethod": "POST"
			,"sAjaxSource": "hoursUsage.php"
			,"fnServerData": function ( sSource, aoData, fnCallback ) {
				aoData.push( { "name": "action", "value": 'generateJson' });
				aoData.push( { "name": "beginDate", "value": $('#beginDateText').val() });
				aoData.push( { "name": "endDate", "value": $('#endDateText').val() });
				aoData.push( { "name": "filters", "value": JSON.stringify(filter_post_array) });
				aoData.push( { "name": "userLevel", "value": getUserLevel() });
				
				lastSearch = getLastSearch();
				aoData.push( { "name": "searchField", "value": lastSearch });
				
				document.body.style.cursor = 'wait';
				document.getElementById('searchButton').disabled = true;
				// $.post(
				$.get(
					sSource
					,aoData
					,function(serverData){
						showMessage(serverData.message, serverData.isError);
					}
					,'json'
				)
				.success(
					fnCallback
				)
				.complete(
					function(){
						document.body.style.cursor = 'default';
						document.getElementById('searchButton').disabled = false;
					}
				);
			}
			,"fnFooterCallback": function(nFoot, aData, iStart, iEnd, aiDisplay){
				var columns_to_change = {
					// 5: ['usageSum', 'usageEndResult']
					6: ['regularSum', 'regularEndResult']
					,9: ['regularSum', 'regularEndResult']
					,10: ['regularSum', 'regularEndResult']
					,11: ['regularSum', 'regularEndResult']
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
				{}
				,{}
				,{}
				,{"sWidth": "100px"}
				,{"sWidth": "70px"}
				,{"sWidth": "120px"}
				,{"sWidth": "60px"}
				,{"sWidth": "60px"}
				,{"sWidth": "60px"}
				,{"sWidth": "60px"}
				,{"sWidth": "60px"}
				,{"sWidth": "60px"}
			]
		});
		
		putDefaultMessage();
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
	return Math.round(end_result*100)/100;
}

// *****************************************************************************
function refresh_table(){
	lastSearch = $('#searchField').val();
	oTable.fnReloadAjax();
}

function synchInfo(e){
	if(e.keyCode == 13){
		refresh_table();
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
	refresh_table();
}
		
function clearField(){
	var element = $('#searchField');
	if(element.val() == defaultSearchText){
		element.attr('class', 'searchFont');
		element.val('');
	}
}

function putDefaultMessage(){
	var element = $('#searchField');
	if(element.val() == ''){
		element.attr('class', 'searchMessageFont');
		element.val(defaultSearchText);
	}
}

function download_csv(){
	var tableSettings = oTable.fnSettings();
	
	var getData =
		"action=downloadCsv"
		+ "&beginDate=" + $('#beginDateText').val()
		+ "&endDate=" + $('#endDateText').val()
		+ "&filters=" + JSON.stringify(filter_post_array)
		+ "&searchField=" + getLastSearch()
		+ "&iDisplayStart=" + tableSettings._iDisplayStart
		+ "&iDisplayLength=" + tableSettings._iDisplayLength
		+ "&iSortCol_0=" + tableSettings.aaSorting[0][0]
		+ "&sSortDir_0=" + tableSettings.aaSorting[0][1]
		+ "&userLevel=" + getUserLevel()
	;
	
	window.location = "hoursUsage.php?" + getData;
}

function getUserLevel(){
	var userLevels = $('input[name=privilegesRadio]:checked', '#userLevelTable');
	var userLevel = null;
	if(userLevels){
		userLevel = userLevels.val();
	}
	
	return userLevel;
}

function getLastSearch(){
	if(lastSearch == defaultSearchText){
		lastSearch = "";
	}
	return lastSearch;
}