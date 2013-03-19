var userIds;

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

function sendUserAndResourceList(){
	toList = document.getElementById("toSelect").options;
	resourcesList = document.getElementById("resourcesSelect").options;
	
	var permLevelIndex = 0;
	if(document.getElementById("permLevelSelect").selectedIndex){
		permLevelIndex = document.getElementById("permLevelSelect").selectedIndex;
	}
	var trainingIndex = 0;
	if(document.getElementById("trainingSelect").selectedIndex){
		trainingIndex = document.getElementById("trainingSelect").selectedIndex;
	}
	permLevel = document.getElementById("permLevelSelect").options[permLevelIndex].value;
	training = document.getElementById("trainingSelect").options[trainingIndex].value;
	
	userLogins = new Array();
	resources = new Array();
	resourcesSelectedFlag = false;
	for(var i=0; i<resourcesList.length; i++){
		if(resourcesList[i].selected){
			resources[resources.length] = resourcesList[i].value;
			resourcesSelectedFlag = true;
		}
	}
	
	if(!resourcesSelectedFlag){
		showMessage('Please select at least one resource');
	}		
	else if(toList.length > 0){
		var sendMails = document.getElementById('emailCheck').checked;
		var addedText = "";
		if(sendMails){
			var addedText = "email and ";
		}
		var confirmed = confirm("Are you sure you want to " + addedText + "give this access level for the selected resources for all these users?");
		if(confirmed){
			for(var i=0; i<toList.length; i++){
				userLogins[i] = toList[i].value;
			}
			if(userLogins.length > 0){
				$.post(
					"givePermission.php", 
					{userLogins: userLogins, resources: resources, permLevel: permLevel, training: training, sendMails: sendMails},
					function(serverData){
						showMessage(serverData.message, serverData.isError);
					},
					"json")
				;
			}
		}
	}
	else{
		showMessage('Please select at least one user');
	}
}