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

function sendUserList(userId){
	userLogins = new Array();
	toList = document.getElementById("toSelect").options;
	if(toList.length > 0){
		var confirmed = confirm("Are you sure you want to generate a random password for all these users?");
		if(confirmed){
			for(var i=0; i<toList.length; i++){
				userLogins[i] = toList[i].value;
			}
			
			if(userLogins.length > 0){
				$.post(
					"massPassRenewal.php", 
					{userId: userId, userLogins: userLogins},
					function(serverData){
						showMessage(serverData.message, serverData.isError);
					},
					"json")
				;
			}
		}
	}
}