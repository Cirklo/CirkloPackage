function updateAssiduityDivs(serverData){
	// if(serverData.isError){
		// showMessage(serverData.message, true);
	// }
	// else{
		var entriesStatusArray = serverData.divData;
		var element;
		for(key in entriesStatusArray){
			element = document.getElementById(key);
			$(element).animate({width: serverData.divData[key].width + "px"});
			element.title = serverData.divData[key].title;
		}
		$('#assiduityUserName').html(serverData.username);
	// }
}