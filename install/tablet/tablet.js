window.onload = function (){resizeMe('all');}
window.onresize = function (){resizeMe('all');}

// $(
	// function(){
		// $(".resource").tipTip({fadeIn: 0, delay: 0});
		// $(".resourceInUse").tipTip({fadeIn: 0, delay: 0});
	// }
// );

//*********************************************************************
function resizeMe(elementId) {
	var element = document.getElementById(elementId);
	if(element != null){
		var height = String(window.innerHeight-5) + 'px';
		// var height = String(document.body.offsetHeight) + 'px';
		var width = String(window.innerWidth-5) + 'px';
		// var width = String(document.body.offsetWidth) + 'px';
		element.style.height = height;
		element.style.width = width;
	}
}

function returnButton(buttonId){
	var currentInput = document.getElementById('pin');
	currentInput.value += buttonId;
}

function clearPin(){
	document.getElementById('pin').value = '';
}

function loginLogout(functionName, resource){
	pin = document.getElementById('pin').value;
	document.getElementById('pin').value = '';
	$.post('tablet.php?resource=' + resource, {pin: pin, functionName: functionName},
		function(data){
			if(data.success){
				window.location = '../tabletIndex.php?message=' + data.message;
			}
			else{
				showMessage(data.message, true);
			}
		}
		, 'json')
		.error(
			function(error){
				showMessage(error.responseText, true);
			}
		)
	;
}
//*********************************************************************
function createCookie(name, value, days) {
	var date = new Date();
	date.setTime(date.getTime() + (days*24*60*60*1000));
	var expires = "; expires=" + date.toGMTString();
	document.cookie = name + "=" + value + expires + "; path=/";
}

function removeCookie(cookieName){
	createCookie(cookieName, '', -1);
	showMessage('Cookie removed');
}

function setCookie(cookieName, elementId){
	cookieValue = document.getElementById(elementId).value;
	if(cookieValue != ''){
		createCookie(cookieName, cookieValue, 365);
		window.location = "../tabletIndex.php?message=Cookie was created";
	}
	else{
		showMessage('Please pick a valid option', true);
	}
}
//*********************************************************************
// Checks if the resource if being used and warns the user, if not, redirects the user to the phpFile
function resourceClick(resource, action){
	window.location = 'tablet/tablet.php?resource=' + resource + '&action=' + action;
}
//*********************************************************************