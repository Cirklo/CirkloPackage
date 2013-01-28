$(document).ready(
	function(){
		// Browser detection
		if(!detect()){
			// showMessage("This browser may be incompatible with Agendo.", true);
			window.location = "../agendo/errorPage.php";
		}

		$("#resourceSearch").focus(
			function(){
				// if(document.getElementById('resourceSearch') != null){
					$("#resourceSearch").autocomplete({
						source: "../agendo/commonCode.php?autocomplete",
						minLength: 2,
						select: function(event, ui) {
									window.location = "weekview.php?resource=" + ui.item.id;
								},
						dataType: "json"
					});
				// }
			}
		);
	}
);

function getEnter(e, phpFile, resource){
	if (e.keyCode == 13){
		submitUser(phpFile + '.php', resource, null, null);
	}	
}

function go (objIMG) {
	groupViewOk = typeof(groupViewOk) != 'undefined' ? groupViewOk : false;
    s=objIMG.src;
	if((name = objIMG.id) == 'group' && document.getElementById('groupdiv') == null){
		showMessage('User needs to be logged or be in the calendar view to have a resource associated.');
		return;
	}
		
    objDIV=document.getElementById(objIMG.id + 'div');
    if (s.substring(s.length-5,s.length)!="_.png"){
		// document.getElementById(name).src = 'pics/' + name + '.png'
        // document.getElementById('video').src='pics/video.png';
        document.getElementById('help').src='pics/ask.png';
        document.getElementById('resources').src='pics/resource.png';
        document.getElementById('user').src='pics/user.png';
        document.getElementById('group').src='pics/group.png';
        objIMG.src=s.substring(0,s.length-4) + "_.png";
        
        // document.getElementById(name + 'div').style.display='none';
		if((groupElement = document.getElementById('groupdiv')) != null){
			groupElement.style.display='none';
		}
        document.getElementById('userdiv').style.display='none';
        document.getElementById('resourcesdiv').style.display='none';
        document.getElementById('videodiv').style.display='none';

		var offset = $(objIMG).offset();
		if(name == 'group'){
			// objDIV.style.display = 'table';
			getTableData();
			// alert(bla);

			// $('#weekdaysResources').width($('#weekdaysResources').width() + 5);
			// $(".usageDataShow").tipTip({activation: 'click', fadeIn: 0, delay: 0});
			// objDIV.style.left = String(offset.left - objDIV.offsetWidth/2 + objIMG.offsetWidth/2) + "px";
			// objDIV.style.right = "50px";
			// objDIV.style.margin = "auto";
		}
		else{
			objDIV.style.display = "block";
			objDIV.style.left = String(offset.left - objDIV.offsetWidth/2 + objIMG.offsetWidth/2) + "px";
		}
		
		// objDIV.style.left = String(objDIV.style.left - objDIV.offsetWidth/2 + objIMG.offsetWidth/2) + "px";
    } else {
        objIMG.src=s.substring(0,s.length-5) + ".png";
        objDIV.style.display = "none";   
    }
}

// used in application.php
var email = '';
// var userInfo = new Array();
function getUserInfo(){
	// return userInfo;
	return email;
}


// hmm the complexity is strong with this one
function textIsNumeric(textToCheck){
	for(var i = 0; i < textToCheck.length; i++){
		if(!textIsNumericAux(textToCheck.charAt(i))){
			return false
		}
	}
	return true;
}

function textIsNumericAux(charToCheck){
	allowedChars = '0123456789';
	for(var i = 0; i < allowedChars.length; i++){
		if(allowedChars.charAt(i) == charToCheck){
			return true;
		}
	}
	return false;
}

// firstName = lastName = email = login = '';
function submitUser(phpFilePath,resource,user,pass,loginToDatumo) {
	formObj=document.getElementById('edituser');
	passCrypted = false;
	document.body.style.cursor = 'wait';
	if(user==null){
		if (checkfield(formObj.user_idm)) return;
		if (checkfield(formObj.user_passwd)) return;
		// user = formObj.user_idm.value = formObj.user_idm.title;
		user = formObj.user_idm.value;
		pass = formObj.user_passwd.value;
	}
	else{
		passCrypted = true;
	}
	
	$.post("index.php", {functionName:'logIn', login:user, pass:pass, passCrypted:passCrypted},
			function(data){
				if(data.success){
					//****imap******
					if(data.makeUser != null && data.makeUser){
						// userInfo['login'] = user;
						// userInfo['pass'] = pass;
						// userInfo['email'] = data.email;
						email = data.email;
						window.open('../agendo/application.php?makeUser', 'NewUser', 'width=400,height=400');
					}
					//**************
					else{
						if(resource != null){
							phpFilePath = phpFilePath + "?resource="+resource;
						}
						else{
							phpFilePath = phpFilePath;
						}
						// if(loginToDatumo){
							// phpFilePath = "../datumo/";
						// }
						window.location = phpFilePath;
					}
				}
				else{
					showMessage(data.msg, true);
				}
			}
			,"json"
		)
		.error(
			function(error) {
				showMessage(error.responseText, true);
				document.body.style.cursor = 'default';
			}
		)
		.complete(
			function(){
				document.body.style.cursor = 'default';
			}
		)
	;
}

function logOff(phpFilePath, resource){
	$.post(phpFilePath, {functionName:'logOff'},"json")
		.error(function(error){showMessage(error, true);})
		.complete(function(){
				if(resource != null){
					window.location = phpFilePath + "?resource="+resource;
				}
				else{
					window.location = phpFilePath;
				}
			}
		)
	;
}

function ajaxRequest(url){
	if (window.XMLHttpRequest){ 
        xmlhttp=new XMLHttpRequest();
    } else {
        alert("Your browser does not support XMLHTTP!");
        exit;
    }
	xmlhttp.open("GET",url,false);
    xmlhttp.send(null);
    var str=xmlhttp.responseText;
    return str;
}

function showMessage(msg, isError){
	isError = isError || false; // sets isError as false by default, javascript is primitive and doesnt allow something like isError = false in the parameters

	// if(isError){
		// $(document).ready(function(){
				// $.jnotify(msg, 'error', true);
			// }
		// );
	// }
	// else{
		// $(document).ready(function(){
				// $.jnotify(msg);
			// }
		// );
	// }
	if(typeof msg !== "undefined" && msg != null && msg !== ''){ // wont do anything if the message isnt set or is empty
		if(isError){ // what should happen is its an error message
			$(document).ready(
				function(){
					jError(msg,
						{
							autoHide : false // added in v2.0
							// ,clickOverlay : true // added in v2.0
							,ShowOverlay : false
						}
					);
					document.body.style.cursor = 'default';
				}
			);
		}
		else{ // regular message
			$(document).ready(function(){
					jSuccess(msg,
						{
							clickOverlay : true // added in v2.0
							,TimeShown : 2000
							// ,MinWidth : 250
							// ,ShowTimeEffect : 200
							// ,HideTimeEffect : 200
							// ,LongTrip :20
							// ,HorizontalPosition : 'center'
							// ,VerticalPosition : 'top'
							,ShowOverlay : false
							// ,ColorOverlay : '#000'
							// ,OpacityOverlay : 0.3
						}
					);
				}
			);
		}
	}
	
}

function getOrPost(url, mainFunction, argsToSend, type, completedFunction, completedArgs, errorFunction, errorArgs){
	type = type || 'post';
	mainFunction = mainFunction || false;
	argsToSend = argsToSend || false;
	completedFunction = completedFunction || false;
	completedArgs = completedArgs || false;
	errorFunction = errorFunction || false;
	errorArgs = errorArgs || false;
	
	// $.ajax(
	$.post(
		url
		,{action: mainFunction, args: argsToSend}
		,function(serverData){
			if(!serverData.isError && mainFunction){
				window[mainFunction](serverData);
			}
			showMessage(serverData.message, serverData.isError);
		}
		, "json"
	)
	.error(
		function(error) {
			if(errorFunction){
				window[errorFunction](errorArgs);
			}
			showMessage(error.responseText, true);
		}
	)
	.complete(
		function(serverData){
			if(completedFunction){
				window[functionName](completedArgs);
			}
		}
	);
}

function isObjEmpty(obj){
	for(var i in obj){
		return false; 
	}
	return true;
}