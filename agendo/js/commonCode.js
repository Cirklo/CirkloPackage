$(document).ready(function(){
$("#resourceSearch").focus(function(){
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
});
});

function go (objIMG) {
    s=objIMG.src;
    objDIV=document.getElementById(objIMG.id + 'div');
    
    if (s.substring(s.length-5,s.length)!="_.png"){
        document.getElementById('video').src='pics/video.png';
        document.getElementById('help').src='pics/ask.png';
        document.getElementById('resources').src='pics/resource.png';
        document.getElementById('user').src='pics/user.png';
        objIMG.src=s.substring(0,s.length-4) + "_.png";
        
        document.getElementById('userdiv').style.display='none';
        document.getElementById('resourcesdiv').style.display='none';
        document.getElementById('videodiv').style.display='none';
        objDIV.style.display = "block";   
		objDIV.style.left = objIMG.x - objDIV.offsetWidth/2 + objIMG.offsetWidth/2;
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
						phpFilePath = phpFilePath + "?resource="+resource;
						if(loginToDatumo){
							// phpFilePath = "../datumo/index.php";
							phpFilePath = "../datumo/";
						}
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
		.error(function(error){showMessage(error);})
		.complete(function(){window.location = phpFilePath + "?resource="+resource;})
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
	extra = '';
	
	// what should happen is its an error message
	if(isError){
		$(document).ready(function(){
				$.jnotify(msg, 'error', true);
			}
		);
	}
	else{
		$(document).ready(function(){
				$.jnotify(msg);
			}
		);
	}
	
	// alert(msg);
	// $(document).ready(function(){
			// $.jnotify(msg, extra);
		// }
	// );
}