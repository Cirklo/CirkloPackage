function updateAssiduityDivs(serverData){
	if(serverData.isError){
		showMessage(serverData.message, true);
	}
	else{
		var assidVisualArray = serverData.data['assidVisualArray'];
		var entriesStatusArray = serverData.data['entriesStatusArray'];
		var totalEntries = serverData.data['totalEntries'];
		var element;
		for(i in assidVisualArray){
			document.getElementById(i).style.display = 'table-row';
		}

		for(key in entriesStatusArray){
			element = document.getElementById(key);
			// alert(key);
			// $(element).animate({width: serverData.divData[key].width + "px"});
			// element.title = serverData.divData[key].title;
		}
		$('#assiduityUserName').html(serverData.username);
	}
	
	// lineBrkTr = document.getElementById("lineBrkTr");
	// titleBrkTr = document.getElementById("titleBrkTr");
	// confirmedDiv = document.getElementById("confirmedTr");
	// unconfirmedDiv = document.getElementById("unconfirmedTr");
	// deletedDiv = document.getElementById("deletedTr");
	
	// confirmedDiv.style.display = 'table-row';
	// unconfirmedDiv.style.display = 'table-row';
	// deletedDiv.style.display = 'table-row';
}