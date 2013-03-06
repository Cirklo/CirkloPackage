var userLogin = "";
var userPass = "";
var currentResource = "";
var entries = new Array();

// Sends the user login and pass from the input boxes if they are filled
// if user is valid shows the item insert div
function itemInsertShowDivAndCheckUser(resource, action, entry, usePostEntries){
	usePostEntries = usePostEntries || false;
	
	// used to put all the entries in an array
	if(entry){
		entries[entries.length] = entry;
	}
	
	currentResource = resource;
	// Needed because some idiot (me) put the same id for the field in weekview.php and commonCode.php (maybe it was necessary, i hope...)
	var objForm = document.getElementById('entrymanage');
	userLogin = objForm.user_id.value;
	userPass = objForm.user_passwd.value;
	objForm.user_id.value = "";
	objForm.user_passwd.value = "";

	$.post(
		"../agendo/itemHandling.php"
		,{action: action, userLogin: userLogin, userPass: userPass, resource: currentResource, 'entries[]': entries, usePostEntries: JSON.stringify(usePostEntries)}
		,function(serverData){
			if(!serverData.isError){
				var divToShow = $('#itemInterfaceDiv');
				divToShow.html(serverData.html);
				divToShow.css("top", (($(window).height() - divToShow.outerHeight()) / 2) + $(window).scrollTop() + "px");
				divToShow.css("left", (($(window).width() - divToShow.outerWidth()) / 2) + $(window).scrollLeft() + "px");
				divToShow.show();
			}
			else{
				showMessage(serverData.message, true);
			}
		}
		,"json")
		.error(
			function(errorData){
				showMessage(errorData.responseText, true);
			}
		)
	;
}

function closeitemInsertDiv(delEntries){
	if(delEntries && entries){
		var deleteThemAll = confirm('Do you want to close this screen and delete the added entry(and associated entries)?');
		if(deleteThemAll){
			for(var i=0; i<entries.length-1; i++){
				deleteEntry(entries[i], true);
			}
			deleteEntry(entries[entries.length-1], false);
		}
		else{
			return;
		}
	}

	var divToShow = $('#itemInterfaceDiv');
	divToShow.html("");
	divToShow.hide();
	entries = new Array();
	userLogin = "";
	userPass = "";
	getCalendar();
}

function deleteEntry(entry, dontShowErrorMessage){
	$.get(
		"../agendo/process.php"
		, {resource: currentResource, user_id: userLogin, user_passwd: userPass, action: 'del', entry: entry}
		, function(serverData){
			if(!dontShowErrorMessage || serverData.isError){
				showMessage(serverData.message, serverData.isError);
			}
			getCalendar();
		}
		, "json")
	.error(
		function(errorData){
			showMessage(errorData.responseText, true);
		}
	);
}

function itemInsertOrRemove(remove){
	remove = remove || false;
	var items = new Array();
	var jsonValue = {};
	var project = null;
	if(remove){
		var list = document.getElementById('submittedItems');
		for(var i=list.options.length-1; i>=0; i--){
			if(list.options[i].selected){
				// Convert the JSON string back to an object
				jsonValue = JSON.parse(list.options[i].value);
				if(jsonValue.state == 2){ // The blocked/InUse state
					showMessage('The item ' + list.options[i].text + " is scheduled for sequencing and can\'t be removed", true);
					return;
				}
				// items will store the ids of the items
				items[items.length] = jsonValue.id;
			}
		}
	}
	else{
		var proj_list = document.getElementById('projectList');
		if(proj_list && proj_list.length > 0){
			project = document.getElementById('projectList').options[document.getElementById('projectList').selectedIndex].value;
		}
		
		var itemName = document.getElementById('itemName').value;
		if(itemName != ''){
			// items will store the name of the item, just one item in this case
			items[items.length] = itemName;
			document.getElementById('itemName').value = '';
		}
	}
	
	if(items.length <= 0){
		showMessage('No items were supplied', true);
		return;
	}
	
	var url = "../agendo/itemHandling.php";
	if(document.getElementById('asUserList') != null){
		var asUser = document.getElementById('asUserList').options[document.getElementById('asUserList').selectedIndex].value;
		url += "?asUser=" + asUser;
	}
	
	$.post(
		url
		,{action: 'itemInsertOrRemove', 'items[]': items, userLogin: userLogin, userPass: userPass, resource: currentResource, remove: remove, project: project}
		,function(serverData){
			if(!serverData.isError){
				if(asUser != null){
					fillSubmittedListFromUser();
				}
				else{
					fillItemsListOptions(serverData.selectOptions);
				}
			}
			showMessage(serverData.message, serverData.isError);
		}
		,"json")
		.error(
			function(errorData){
				showMessage(errorData.responseText, true);
			}
		)
	;
}

function fillItemsListOptions(selectOptions){
	var submittedItems = document.getElementById('submittedItems');
	// if(selectOptions.length != 0){ // doesnt work because selectOptions is an object and it doesnt have length! damn it JS!!! why you so bad??
		// Clean the contents of the list to refill
		submittedItems.options.length = 0;
	// }
	
	var jsonValue;
	var newOption;
	if(document.getElementById('lockedItems')){ // Means this is the manager's interface, not very elegant but no need for extra vars
		var theLockedList = document.getElementById('lockedItems');
		theLockedList.options.length = 0;
		
		for(var i in selectOptions){
			newOption = getNewOptionFrom(i, selectOptions[i].name, selectOptions[i].state);
			if(selectOptions[i].state == 1){
				submittedItems.options[submittedItems.options.length] = newOption;
			}
			else{
				theLockedList.options[theLockedList.options.length] = newOption;
			}
		}
	}
	else{
		for(var i in selectOptions){
			newOption = getNewOptionFrom(i, selectOptions[i].name, selectOptions[i].state);
			newOption.setAttribute("class", "optionState" + selectOptions[i].state);
			submittedItems.options[submittedItems.options.length] = newOption;
		}
	}
}

function getNewOptionFrom(id, name, state){
	var jsonValue = {}; // gah not this again.... Effin javascript and its distinction between and array and object
	jsonValue['id'] = id;
	jsonValue['state'] = state;
	newOption = new Option(name, JSON.stringify(jsonValue), false, false);
	newOption.title = name;
	return newOption;
}

function saveItemList(){
	var items = {};
	items['locked'] = new Array();
	items['submitted'] = new Array();
	
	$("#lockedItems option").each(function(){
		items['locked'][items['locked'].length] = this.value;
	});

	$("#submittedItems option").each(function(){
		// items['submitted'][items['submitted'].length] = submittedItems[i].value;
		items['submitted'][items['submitted'].length] = this.value;
	});

	// This wont work on chrome because god knows why, had the same for the submitted list...
	// var lockedItems = document.getElementById('lockedItems').options;
	// for(var i in lockedItems){
		// if(lockedItems[i].value){
			// items['locked'][items['locked'].length] = lockedItems[i].value;
		// }
	// }
	
	if(entries.length != 0){
		if(lockedItems.length == 0){
			showMessage("The locked item list can't be empty.", true);
			return;
		}
		$.post(
			"../agendo/itemHandling.php"
			// , {'entries[]': entries, 'items[]': items, action: 'associateEntriesAndItems', userLogin: userLogin, userPass: userPass, resource: resource}
			, {action: 'associateEntriesAndItems', 'entries[]': entries, items: JSON.stringify(items), userLogin: userLogin, userPass: userPass, resource: currentResource}
			, function(serverData){
				showMessage(serverData.message, serverData.isError);
			}
			, "json"
		)
		.error(
			function(errorData){
				showMessage(errorData.responseText, true);
			}
		);
	}
	else{
		showMessage("No entries selected.", true);
	}
}

function swapAll(from, to){
	fromList = document.getElementById(from).options;
	toList = document.getElementById(to).options;
	while(fromList.length > 0){
		toList[toList.length] = fromList[0];
	}
}

function swapSelected(from, to){
	fromList = document.getElementById(from).options;
	toList = document.getElementById(to).options;
	for(var i=0; i<fromList.length; i++){
		if(fromList[i].selected){
			toList[toList.length] = fromList[i];
			i--;
		}
	}
}

function editEntryItems(resource){
	if(resource && selectedEntry){
		itemInsertShowDivAndCheckUser(resource, "itemManagementHtml", selectedEntry, true);
	}
}

function emailUsersFromItems(){
	var lockedItems = document.getElementById('lockedItems').options;
	var selectedList = new Array();
	var UnSelectedList = new Array(); // used in case the user doesnt select any item, then all items are sent
	var jsonValue = {};
	
	if(lockedItems.length == 0){
		showMessage("There are no items available", true);
		return;
	}
	
	for(var i=0; i<lockedItems.length; i++){
		if(lockedItems[i].selected){
			jsonValue = JSON.parse(lockedItems[i].value);
			selectedList[selectedList.length] = jsonValue['id'];
		}
		else if(selectedList.length == 0){
			jsonValue = JSON.parse(lockedItems[i].value);
			UnSelectedList[UnSelectedList.length] = jsonValue['id'];
		}
	}
	
	if(selectedList.length == 0){
		selectedList = UnSelectedList;
	}
	
	$.post(
		"../agendo/itemHandling.php"
		, {action: "emailUsersFromItems", 'items[]': selectedList, userLogin: userLogin, userPass: userPass, resource: currentResource}
		, function(serverData){
			if(serverData.isError){
				showMessage(serverData.message, true);
				return;
			}
			
			var mailString = serverData.mailToUsers;
			mailString += "?subject=" + document.getElementById('emailSubject').value;
			mailString += "&body=" + document.getElementById('emailBody').value;
			window.location = "mailto:" + mailString;
		}
		, "json"
	)
	.error(
		function(errorData){
			showMessage(errorData.responseText, true);
		}
	);
}

function newItem(){
	var divToShow = $('#itemInterfaceDiv');
	divToShow.html("");
	divToShow.hide();
	
	itemInsertShowDivAndCheckUser(currentResource, 'itemInsertHtml');	
}

function refreshProjects(projects, default_project){
	var select_list = document.getElementById('projectList');
	
	if(select_list && select_list.options){
		var temp_option;
		var selected;
		select_list.options.length = 0;
		$.each(projects, function(){
			selected = this.id == default_project;
			temp_option = new Option(this.name, this.id, selected, selected);
			select_list.options[select_list.options.length] = temp_option;
		});	
	}
}

function fillSubmittedListFromUser(){
	if(document.getElementById('asUserList') != null){
		var asUser = document.getElementById('asUserList').options[document.getElementById('asUserList').selectedIndex].value;
		$.post(
			"../agendo/itemHandling.php"
			, {action: 'updateSubmittedList', userLogin: userLogin, userPass: userPass, resource: currentResource, asUser: asUser}
			, function(serverData){
				if(serverData.isError){
					showMessage(serverData.message, true);
				}
				else{
					fillItemsListOptions(serverData.selectOptions);
					refreshProjects(serverData.projects, serverData.default_project);
				}
			}
			, "json"
		)
		.error(
			function(errorData){
				showMessage(errorData.responseText, true);
			}
		);
	}
}

function back(){
	var divToShow = $('#itemInterfaceDiv');
	divToShow.html("");
	divToShow.hide();
	
	itemInsertShowDivAndCheckUser(currentResource, 'itemManagementHtml', null, true);
	// itemInsertShowDivAndCheckUser(currentResource, 'itemManagementHtml');
}

function done(){
	if(selectedEntry){
		entries[entries.length] = selectedEntry;
	}
	
	$.post(
		"../agendo/itemHandling.php"
		, {action: 'done', 'entries[]': entries, userLogin: userLogin, userPass: userPass, resource: currentResource}
		, function(serverData){
			showMessage(serverData.message, serverData.isError);
			closeitemInsertDiv();
			getCalendar();
		}
		, "json"
	)
	.error(
		function(errorData){
			showMessage(errorData.responseText, true);
		}
	);
}

// this will stop multiple load functions for the iframe, using onready to create the function wont work because the iframe isnt created yet
var loadFunctionExists = false;
function upload(){
	if(!textIsNumeric(document.getElementById("lineValue").value)){
		showMessage("Row doesn't have a numeric value", true);
		return;
	}

	if(!textIsNumeric(document.getElementById("columnValue").value)){
		showMessage("Column doesn't have a numeric value", true);
		return;
	}

	if(document.getElementById("file").value == ""){
		showMessage("File not specified", true);
		return;
	}

	var confirmMessage = "Are you sure you want to import items from the selected file?";
	if(document.getElementById('emailRespCheck').checked){
		confirmMessage = "Are you sure you want to import items from the selected file and email it as an attachment to the resource manager?";
	}
	
	var confirmUpload = confirm(confirmMessage);
	if(confirmUpload){
		var action = "../agendo/itemHandling.php?action=upload&resource=" + currentResource;
		if(userLogin && userPass){
			action += "&userLogin=" + userLogin + "&userPass=" + userPass;
		}
		
		var project_list = document.getElementById('projectList');
		var project = "";
		if(project_list){
			project = project_list.options[project_list.selectedIndex].value;
		}
		action += "&project=" + project;
		
		var user_list = document.getElementById('asUserList');
		var as_user = ""
		if(user_list){
			as_user = user_list.options[user_list.selectedIndex].value;
		}
		action += "&asUser=" + as_user;
		
		document.forms["uploadFileForm"].action = action;
		document.forms["uploadFileForm"].submit();
		if(loadFunctionExists === false){
			$('#submitIframe').load(
				function(){
					loadFunctionExists = true;
					var responseText = $('#submitIframe').contents().find('body').html();
					$('#submitIframe').contents().find('body').html("");
					
					if(!responseText){
						showMessage("No information from server", true);
						return;
					}
					else{
						var serverDataJson = JSON.parse(responseText);
						showMessage(serverDataJson.message, serverDataJson.isError);
						
						getItems();
					}
				}
			);	
		}
	}
}

function selectAllItems(){
	var check = document.getElementById('selectAllItemsCheck').check;
	selectAllItemsAux(!check);
	document.getElementById('selectAllItemsCheck').check = !check;
}

function selectAllItemsAux(isSelected){
	var submittedItems = document.getElementById('submittedItems').options;
	var jsonValue = {};

	for(var i=0; i<submittedItems.length; i++){
		jsonValue = JSON.parse(submittedItems[i].value);
		if(jsonValue['state'] == 1){
			submittedItems[i].selected = isSelected;
		}
	}
}

function getItems(){
	$.post(
		"../agendo/itemHandling.php"
		,{action: "getItems", userLogin: userLogin, userPass: userPass, resource: currentResource}
		,function(serverData){
			if(!serverData.isError){
				fillItemsListOptions(serverData.items);
			}
			else{
				showMessage(serverData.message, true);
			}
		}
		,"json")
		.error(
			function(errorData){
				showMessage(errorData.responseText, true);
			}
		)
	;
}

function showProject(value){
		if(value){
		var asUser = null;
		var as_user_list = document.getElementById('asUserList');
		if(as_user_list != null){
			asUser = as_user_list.options[as_user_list.selectedIndex].value;
		}
		var id = JSON.parse(value).id;
		$.post(
			"../agendo/itemHandling.php"
			,{action: "getProject", item: id, userLogin: userLogin, userPass: userPass, asUser: asUser}
			,function(serverData){
				if(!serverData.isError){
					$("#projectList option[value='" + serverData.project + "']").attr("selected","selected");
				}
				else{
					showMessage(serverData.message, true);
				}
			}
			,"json")
			.error(
				function(errorData){
					showMessage(errorData.responseText, true);
				}
			)
		;
	}
}

// to be implemented later?
// function associateItemToProj(){
	// var projectList = document.getElementById('projectList');
	// var itemList = document.getElementById('submittedItems');
	
	// if(){
	
	// }
// }