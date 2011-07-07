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

function submitUser(phpFilePath,resource,user,pass,loginToDatumo) {
    formObj=document.getElementById('edituser');
	passCrypted = false;

	if(user==null){
		if (checkfield(formObj.user_idm)) return;
		if (checkfield(formObj.user_passwd)) return;
		user = formObj.user_idm.value = formObj.user_idm.title;
		pass = formObj.user_passwd.value;
	}
	else{
		passCrypted = true;
	}
	
	$.post("index.php", {functionName:'logIn', login:user, pass:pass, passCrypted:passCrypted},
			function(data){
				if(data.success){
					phpFilePath = phpFilePath + "?resource="+resource;
					if(loginToDatumo){
						phpFilePath = "../datumo/admin.php";
					}
					window.location = phpFilePath;
				}
				else{
					showMessage(data.msg);
				}
			}
			,"json"
		)
		.error(
			function(error) {
				showMessage(error.responseText);
			}
		)
	;
}

function showMessage(msg, isError){
	isError = isError || false; // sets isError as false by default, javascript is primitive and doesnt allow something like isError = false in the parameters
	extra = '';
	
	// what should happen is its an error message
	if(isError){
		extra = 'error';
	}
	
	// alert(msg);
	$.jnotify(msg, extra);
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