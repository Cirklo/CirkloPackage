var userLogin = "";
var userPass = "";
// Sends the user login and pass from the input boxes if they are filled
// if user is valid shows the sample insert div
function sampleInsertShowDivAndCheckUser(resource){
	// Needed because some idiot (me) put the same id for the field in weekview.php and commonCode.php (maybe it was necessary and i cant remember... i hope...)
	var objForm = document.getElementById('entrymanage');
	userLogin = objForm.user_id.value;
	userPass = objForm.user_passwd.value;
	objForm.user_id.value = "";
	objForm.user_passwd.value = "";
	// var userPass = document.getElementById('user_passwd').value;
	$.post(
		"../agendo/sampleHandling.php"
		,{action: "sampleInsertHtml", userLogin: userLogin, userPass: userPass, resource: resource}
		,function(serverData){
			if(serverData.success){
				var divToShow = $('#sampleInterfaceDiv');
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

function closeSampleInsertDiv(){
	var divToShow = $('#sampleInterfaceDiv');
	divToShow.html("");
	divToShow.hide();
}

function sampleInsertOrRemove(resource, remove){
	remove = remove || false;
	var samples = new Array();
	if(remove){
		var list = document.getElementById('submittedSamples');
		for(var i=list.options.length-1; i>=0; i--){
			if(list.options[i].selected){
				// samples will store the ids of the samples
				samples[samples.length] = list.options[i].value;
			}
		}
	}
	else{
		var sampleName = document.getElementById('sampleName').value;
		if(sampleName == ''){
			showMessage('Please insert a sample name');
			return;
		}
		// samples will store the name of the sample, just one sample in this case
		samples[samples.length] = sampleName;
	}
	
	$.post(
		"../agendo/sampleHandling.php"
		,{action: 'sampleInsert', samples: samples, userLogin: userLogin, userPass: userPass, resource: resource, remove: remove}
		,function(serverData){
			if(serverData.success){
				$('#submittedSamples').html(serverData.selectOptions);
			}
			showMessage(serverData.message, !serverData.success);
		}
		,"json")
		.error(
			function(errorData){
				showMessage(errorData.responseText, true);
			}
		)
	;
}

