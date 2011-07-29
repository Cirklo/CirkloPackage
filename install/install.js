$(function(){
	$(".helpClass").tipTip();
	// document.getElementById('makeHtConnect').disabled = false;
});

function showMessage(msg){
	// $.jnotify(msg);
	document.getElementById('successError').innerHTML = msg;
}

function checkMail(address){
	$.post('install.php', {functionName: 'checkMail', address: address},
		function(phpMessage){
			showMessage(phpMessage);
		}
	);
}

function back(){
	backBool = confirm('Going back will delete the changes you made, are you sure?');
	if(backBool){
		document.getElementById('firstScreen').style.display='table';
		document.getElementById('databaseData').style.display='none'
		document.getElementById('makeHtConnect').disabled = false;
		$.post('install.php', {functionName: 'back'},
			function(phpMessage){
				showMessage(phpMessage);
			}
		);
	}
}

// needs to be the same as the one on the index.php
separator = 'IRSEPARATOR';
systemSeparator = 'IRSYSTEMSEPARATOR';
detectedErrorOnJS = false;
messageToShow = '';
function postMe(functionName){
	data = [];
	
	if(functionName == 'makeHtConnect'){
		data = getHtConnectData();
	}
	else if(functionName == 'applySql'){
		data = getDBData();
	}
	
	if(detectedErrorOnJS){
		showMessage(messageToShow);
	}
	else{
		showMessage("Importing data from the script, please wait.");
		document.getElementById('applySql').disabled = true;
		document.getElementById('makeHtConnect').disabled = true;
		document.getElementById('back').disabled = true;
	
		$.post	('install.php', {functionName: functionName, 'data[]': data},
				function(serverData){
					document.getElementById('back').disabled = false;
					document.getElementById('makeHtConnect').disabled = false;
					document.getElementById('applySql').disabled = false;
					extraText = '';

					if(serverData.success){
						if(functionName == 'makeHtConnect'){
							document.getElementById('firstScreen').style.display='none';
							document.getElementById('databaseData').style.display='table';
							
							selectBox = document.getElementById('countries');
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
						}
						else if (functionName == 'applySql'){
							extraText = "<br><a href='../" + document.getElementById('path').value + "/index.php?class=0'>Click here to start</a>";
							document.getElementById('applySql').disabled = true;
						}
					}
					showMessage(serverData.message.replace("\n", "<br>") + extraText);
				}, 'json'
			)
			.error(
				function(error) {
					showMessage(error.responseText);
				}
			)
		;
	}
}

country = 1;
function getCountry(value){
	country = value;
}

software = 'datumo';
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
	// sendEmailChecked = 	document.getElementById('sendEmailChecked').checked;
	
	dataArray = [adminId, adminPass, adminFirst, adminLast, adminPhone, adminExt, adminMobile, adminMail,
	        instituteName, instituteShort, instituteUrl, instituteMail, institutePass, instituteHost, institutePort,
			instituteSecure, instituteAuth, instituteAddress, institutePhone, country, department, software];
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