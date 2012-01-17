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
	permLevel = document.getElementById("permLevelSelect").options[document.getElementById("permLevelSelect").selectedIndex].value;
	training = document.getElementById("trainingSelect").options[document.getElementById("trainingSelect").selectedIndex].value;

	userLogins = new Array();
	resources = new Array();
	
	for(var i=0; i<resourcesList.length; i++){
		if(resourcesList[i].selected){
			resources[resources.length] = resourcesList[i].value;
		}
	}
			
	if(toList.length > 0 && resources.length > 0){
		var confirmed = confirm("Are you sure you want to give this access level for the selected resources for all these users?");
		if(confirmed){
			for(var i=0; i<toList.length; i++){
				userLogins[i] = toList[i].value;
			}
			
			if(userLogins.length > 0){
				$.post(
					"givePermission.php", 
					{userLogins: userLogins, resources: resources, permLevel: permLevel, training: training},
					function(serverData){
						showMessage(serverData.msg, serverData.error);
					},
					"json")
				;
			}
		}
	}
}