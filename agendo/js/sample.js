function sampleInsert(resource){
	var sampleName = document.getElementById('sampleName').value;
	if(sampleName == ''){
		showMessage('Please insert a sample name');
	}
	else{
		$.post(
			"../agendo/sampleHandling.php?resource=" + resource
			,{sampleName: sampleName}
			,function(serverData){
				alert(serverData);
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

// Sends the user login and pass from the input boxes if they are filled
// if user is valid shows the sample insert div
function sampleInsertShowDivAndCheckUser(resource){
	// Nedeed because some idiot (me) put the same id for the field in weekview.php and commonCode.php (was it needed though?)
	var objForm = document.getElementById('entrymanage');
	var userLogin = objForm.user_id.value;
	var userPass = objForm.user_passwd.value;
	objForm.user_id.value = "";
	objForm.user_passwd.value = "";
	// var userPass = document.getElementById('user_passwd').value;
	$.post(
		"../agendo/sampleHandling.php"
		,{action:"sampleInsertHtml", userLogin:userLogin, userPass:userPass, resource:resource}
		,function(serverData){
			if(serverData.success){
				// var divToShow = document.getElementById('sampleInterfaceDiv');
				var divToShow = $('#sampleInterfaceDiv');
				// divToShow.style.display = 'table';
				divToShow.css("top", (($(window).height() - divToShow.outerHeight()) / 2) + 
                                                $(window).scrollTop() + "px");
				divToShow.css("left", (($(window).width() - divToShow.outerWidth()) / 2) + 
                                                $(window).scrollLeft() + "px");
				alert($(window).scrollTop());
				// divToShow.innerHTML = serverData.html;
				divToShow.html(serverData.html);
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