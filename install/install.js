$(function(){
	$(".helpClass").tipTip();
	// document.getElementById('makeHtConnect').disabled = false;
});

function showYellowMessage(msg){
	// $.jnotify(msg);
	document.getElementById('successError').innerHTML = msg;
}

function checkMail(address){
	$.post('install.php', {functionName: 'checkMail', address: address},
		function(phpMessage){
			showYellowMessage(phpMessage);
		}
	);
}

// comment this
// function back(){
	// backBool = confirm('Going back will delete the changes you made, are you sure?');
	// if(backBool){
		// document.getElementById('firstScreen').style.display='table';
		// document.getElementById('databaseData').style.display='none'
		// document.getElementById('makeHtConnect').disabled = false;
		// $.post('install.php', {functionName: 'back', path: path},
			// function(serverData){
				// showYellowMessage(serverData.message);
			// }
			// , 'json'
		// );
	// }
// }

detectedErrorOnJS = false;
messageToShow = '';
aContinents = [];
function postMe(functionName){
	data = [];
	
	if(functionName == 'makeHtConnect'){
		data = getHtConnectData();
	}
	else if(functionName == 'applySql'){
		data = getDBData();
	}
	
	if(detectedErrorOnJS){
		showYellowMessage(messageToShow);
	}
	else{
		showYellowMessage("Importing data from the script, please wait.");
		document.getElementById('applySql').disabled = true;
		document.getElementById('makeHtConnect').disabled = true;
		// document.getElementById('back').disabled = true;
	
		$.post	('install.php', {functionName: functionName, 'data[]': data},
				function(serverData){
					// document.getElementById('back').disabled = false;
					document.getElementById('makeHtConnect').disabled = false;
					document.getElementById('applySql').disabled = false;
					extraText = '';

					if(!serverData.isError){
						if(functionName == 'makeHtConnect'){
							document.getElementById('firstScreen').style.display='none';
							document.getElementById('databaseData').style.display='table';
							
							selectBox = document.getElementById('countries');
							selectBox.length = 0;
							for(var position in serverData.countries){
								newSelectElement = document.createElement('option');
								newSelectElement.value = position;
								newSelectElement.text = serverData.countries[position];
								try{
									selectBox.add(newSelectElement, null);
								}
								catch(ex){
									selectBox.add(newSelectElement);// IE only
								}
							}
							
							selectBox = document.getElementById('timezoneContinents');
							selectBox.length = 0;
							var tempContinent = '';
							var tempArray = [];
							var subCity;
							for(var position in serverData.timezones){
								newSelectElement = document.createElement('option');
								timezoneArray = serverData.timezones[position].split("/");
								
								subCity = "";
								if(timezoneArray[0] == tempContinent){
									tempArray = aContinents[tempContinent];
									if(timezoneArray[1] != null){
										if(timezoneArray[2] != null){
											subCity = "/" + timezoneArray[2];
										}
										tempArray[tempArray.length] = timezoneArray[1] + subCity;
										aContinents[tempContinent] = tempArray;
									}
								}
								else{
									tempContinent = timezoneArray[0];
									if(timezoneArray[1] != null){
										tempArray = [];
										if(timezoneArray[2] != null){
											subCity = "/" + timezoneArray[2];
										}
										tempArray[0] = timezoneArray[1] + subCity;
										aContinents[tempContinent] = tempArray;
									}
									newSelectElement.value = timezoneArray[0];
									newSelectElement.text = timezoneArray[0];
									try{
										selectBox.add(newSelectElement, null);
									}
									catch(ex){
										selectBox.add(newSelectElement);// IE only
									}
								}
							}
							getTimezones(serverData.timezones[0].split("/")[0]);
						}
						else if (functionName == 'applySql'){
							extraText = "<br><a href='../" + document.getElementById('path').value + "/index.php?class=0'>Click here to start</a>";
							document.getElementById('applySql').disabled = true;
						}
					}
					showYellowMessage(serverData.message.replace("\n", "<br>") + extraText);
				}, 'json'
			)
			.error(
				function(error) {
					showYellowMessage("error-" + error.responseText + "-error", true);
				}
			)
		;
	}
}

country = 1;
function getCountry(value){
	country = value;
}

function getTimezones(value){
	citiesArray = aContinents[value];
	selectBox = document.getElementById('timezoneCities');
	selectBox.options.length = 0;
	while (selectBox.firstChild) {
		selectBox.removeChild(selectBox.firstChild);
	}
	// alert(selectBox.length);
	var tempGroup = null;
	for(position in citiesArray){
		content = citiesArray[position].split('/');
		if(content.length > 1){
			if(tempGroup == null){
				tempGroup = document.createElement('optgroup');
				tempGroup.id = "city" + content[0];
				tempGroup.label = content[0];
				try{
					selectBox.add(tempGroup, null);
				}
				catch(ex){
					selectBox.add(tempGroup);// IE only
				}
			}
			newSelectElement = document.createElement('option');
			newSelectElement.value = content[0] + "/" + content[1];
			newSelectElement.text = content[1];
			tempGroup.appendChild(newSelectElement);
		}
		else{
			tempGroup = null;
			newSelectElement = document.createElement('option');
			newSelectElement.value = content[0];
			newSelectElement.text = content[0];
			try{
				selectBox.add(newSelectElement, null);
			}
			catch(ex){
				selectBox.add(newSelectElement);// IE only
			}
		}
	}
}

software = 'datumo';
path = "";
function getHtConnectData(){
	dbEngine = 	document.getElementById('dbEngine').value;
	dbName = 	document.getElementById('dbName').value;
	dbHost = 	document.getElementById('dbHost').value;
	dbUser = 	document.getElementById('dbUser').value;
	dbPass = 	document.getElementById('dbPass').value;
	path =	 	document.getElementById('path').value;
	// makeDB = 	document.getElementById('makeDB').checked;
	
	// Change to radio button possibly?
	children = document.getElementById('software').getElementsByTagName('*');
	for(i=0;i<children.length;i++){
		if(children[i].type == 'checkbox')
			if(children[i].checked == true)
				software = children[i].id;
	}
	// dataArray = [dbEngine, dbName, dbUser, dbPass, path, makeDB];
	dataArray = [dbEngine, dbName, dbHost, dbUser, dbPass, path, true];
	
	// Error checking for htConnect data
	exceptArray = [4, 6];
	if(checkEmptyExcept(dataArray, exceptArray)){
		detectedErrorOnJS = true;
		messageToShow = 'Please fill all the fields';
	}
	else{
		detectedErrorOnJS = false;
		messageToShow = '';
	}
	// **********************************

	// return [dbEngine, dbName, dbUser, dbPass, path, makeDB];
	return dataArray;
}

function getDBData(){
	adminId = 		document.getElementById('adminId').value;
	adminPass = 	document.getElementById('adminPass').value;
	adminFirst = 	document.getElementById('adminFirst').value;
	adminLast = 	document.getElementById('adminLast').value;
	adminPhone = 	document.getElementById('adminPhone').value;
	adminExt = 		document.getElementById('adminExt').value;
	adminMobile = 	document.getElementById('adminMobile').value;
	adminMail = 	document.getElementById('adminMail').value;
	
	instituteName = 	document.getElementById('instituteName').value;
	instituteShort = 	document.getElementById('instituteShort').value;
	instituteUrl = 		document.getElementById('instituteUrl').value;
	instituteMail = 	document.getElementById('instituteMail').value;
	institutePass = 	document.getElementById('institutePass').value;
	instituteHost = 	document.getElementById('instituteHost').value;
	institutePort = 	document.getElementById('institutePort').value;
	instituteSecure = 	document.getElementById('instituteSecure').value;
	instituteAuth = 	document.getElementById('instituteAuth').value;
	instituteAddress = 	document.getElementById('instituteAddress').value;
	institutePhone = 	document.getElementById('institutePhone').value;
	department = 		document.getElementById('department').value;
	
	timezoneContinent =	document.getElementById('timezoneContinents');
	timezoneCity = 		document.getElementById('timezoneCities');
	if(timezoneCity.selectedIndex != -1)
		timezone = timezoneContinent.options[timezoneContinent.selectedIndex].value + "/" + timezoneCity.options[timezoneCity.selectedIndex].value;
	else
		timezone = timezoneContinent.options[timezoneContinent.selectedIndex].value;
	// sendEmailChecked = 	document.getElementById('sendEmailChecked').checked;
	
	dataArray = [adminId, adminPass, adminFirst, adminLast, adminPhone, adminExt, adminMobile, adminMail,
	        instituteName, instituteShort, instituteUrl, instituteMail, institutePass, instituteHost, institutePort,
			instituteSecure, instituteAuth, instituteAddress, institutePhone, country, department, software, timezone];
			// instituteSecure, instituteAuth, instituteAddress, institutePhone, country, department, software, sendEmailChecked];
	
	// Error checking for dbdata
	if(checkEmptyExcept(dataArray, [dataArray.length-1])){
		detectedErrorOnJS = true;
		messageToShow = 'Please fill all the fields';
	}
	else{
		detectedErrorOnJS = false;
		messageToShow = '';
	}
	// **********************************
	
	return dataArray;
}

// Returns true if, from an array of values, there are empty values (except an array of positions)
function checkEmptyExcept(values, ignoreThese){
	for(i=0; i<values.length; i++){
		check = true;
		
		for(j=0; j<ignoreThese.length; j++){
			if(i == ignoreThese[j]){
				check = false
				break;
			}
		}
		
		if(check && values[i] == ''){
			return true;
		}
	}
	return false;
}