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
			for(i in entries){
				$.get(
					"../agendo/process.php"
					// , {resource: resource, user_id: userLogin, user_passwd: userPass, deleteall: '1', action: 'del', entry: entries[i]}
					, {resource: resource, user_id: userLogin, user_passwd: userPass, action: 'del', entry: entries[i]}
					, function(serverData){
						showMessage(serverData.message, serverData.isError);
						getCalendar();
					}
					, "json")
				.error(
					function(errorData){
						showMessage(errorData.responseText, true);
					}
				);
			}
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
}

function itemInsertOrRemove(remove){
	remove = remove || false;
	var items = new Array();
	var jsonValue = {};
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
	
	$.post(
		"../agendo/itemHandling.php"
		,{action: 'itemInsertOrRemove', 'items[]': items, userLogin: userLogin, userPass: userPass, resource: currentResource, remove: remove}
		,function(serverData){
			if(!serverData.isError){
				fillItemsListOptions(serverData.selectOptions);
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
	return newOption;
}

function saveItemList(){
	var items = {};
	items['locked'] = new Array();
	items['submitted'] = new Array();
	
	var lockedItems = document.getElementById('lockedItems').options;
	for(var i in lockedItems){
		if(lockedItems[i].value){
			items['locked'][items['locked'].length] = lockedItems[i].value;
		}
	}
	
	var submittedItems = document.getElementById('submittedItems').options;
	for(var i in submittedItems){
		if(submittedItems[i].value){
			items['submitted'][items['submitted'].length] = submittedItems[i].value;
		}
	}

	if(entries.length != 0){
		if(lockedItems.length == 0){
			showMessage("The locked item list can't be empty.", true);
			return;
		}
		$.post(
			"../agendo/itemHandling.php"
			// , {'entries[]': entries, 'items[]': items, action: 'associateEntriesAndItems', userLogin: userLogin, userPass: userPass, resource: resource}
			, {action: 'associateEntriesAndItems', 'entries[]': entries, items: JSON.stringify(items), userLogin: userLogin, userPass: userPass, resource: resource}
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
		, {action: "emailUsersFromItems", 'items[]': selectedList, userLogin: userLogin, userPass: userPass, resource: resource}
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

function fillSubmittedListFromUser(){
	if(typeof document.getElementById('asUserList').selectedIndex != "undefined"){
		var asUser = document.getElementById('asUserList').options[document.getElementById('asUserList').selectedIndex].value;
		$.post(
			"../agendo/itemHandling.php"
			, {action: 'updateSubmittedList', userLogin: userLogin, userPass: userPass, resource: resource, asUser: asUser}
			, function(serverData){
				if(serverData.isError){
					showMessage(serverData.message, true);
				}
				else{
					fillItemsListOptions(serverData.selectOptions);
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
}