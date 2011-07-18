window.onload = function (){resizeMe();}
window.onresize = function (){resizeMe();}

function resizeMe() {
	var element = document.getElementById('all');
	var height = String(window.innerHeight-5) + 'px';
	// var height = String(document.body.offsetHeight) + 'px';
	var width = String(window.innerWidth-5) + 'px';
	// var width = String(document.body.offsetWidth) + 'px';
	element.style.height = height;
	element.style.width = width;
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

function returnButton(buttonId){
	var currentInput = document.getElementById('pin');
	currentInput.value += buttonId;
}

var pin = '';
var resource = '';
var dateenter = '';
var entry = '';
function clearPin(){
	pin = '';
	document.getElementById('pin').value = '';
	// document.getElementById('userLabel').value = 'No user is using this resource now';
	document.getElementById('userLabel').innerHTML = 'No user is using this resource now';
	resizeMe();
}

function userEnter(resource){
	// var url = "tablet.php?pin=" + document.getElementById('pin').value + '&resource=' + resource;
	// $.get(url, {}, function(data){enterOrExit(data)});
	
	pin = document.getElementById('pin').value;
	resource = resource;
	$.post('tablet.php?resource=' + resource, {pin: pin}, function(data){enterOrExit(data,resource)});
}

function enableDisableButtons(trueOrFalse){
	for(i=0;i<10;i++)
		document.getElementById(String(i)).disabled = trueOrFalse;
	document.getElementById('clearButton').disabled = trueOrFalse;
}

function enterOrExit(data,resource){
	var bigButtonClass;
	var stateClass;
	var enterButtonValue;
	var message = data.split('\n')
	var postToServer = false;

	if(message[0] == 'true'){
		enterButtonValue = 'Exit';
		bigButtonClass = 'redBigButton';
		stateClass = 'redStateMessage';
		enableDisableButtons(true);
		dateenter = message[2];
		entry = message[3];
	}
	else{
		enterButtonValue = 'Enter';
		bigButtonClass = 'bigButton';
		stateClass = 'stateMessage';
		enableDisableButtons(false);
		postToServer = true;
	}

	clearPin();
	document.getElementById('enterExit').className = bigButtonClass;
	document.getElementById('enterExit').value = enterButtonValue;
	document.getElementById('userLabel').className = stateClass;
	// document.getElementById('userLabel').value = message[1];
	document.getElementById('userLabel').innerHTML = message[1];
	if(postToServer){
		// $.post('tablet.php?resource=' + resource, {pin: pin, dateenter: message[2], entry: message[3]});
		$.post('tablet.php?resource=' + resource, {pin: pin, dateenter: dateenter, entry: entry});
		// alert(resource+'---'+pin+'---'+message[2]+'---'+message[3]);
	}
}
