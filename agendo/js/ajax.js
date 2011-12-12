function ajaxEquiDD(objTagOri,objNameDest) {
var xmlhttp,url;
objTagDest=document.getElementById(objNameDest);

while (objTagDest.firstChild) {objTagDest.removeChild(objTagDest.firstChild);}

    if (window.XMLHttpRequest){ 
        xmlhttp=new XMLHttpRequest();
    } else {
        alert("Your browser does not support XMLHTTP!");
        exit;
    }
    optionItem = document.createElement('option');
    objTagDest.appendChild(optionItem);
    optionItem.value='';
    optionItem.appendChild(document.createTextNode('Select Resource...'));
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.readyState==4) {
            var str=xmlhttp.responseText;
            var a=new Array();
            var b=new Array();
            a=str.split("<name>");
            for (i=1;i<a.length;i++) {
                optionItem = document.createElement('option');
                b=a[i].split("<value>");
                optionItem.value=b[1];
                optionItem.appendChild(document.createTextNode(b[0]));
                objTagDest.appendChild(optionItem);
            }
        }
        
    }
    
    //alert(Page + objTagOri.value);
    xmlhttp.open("GET","ajax.php?type=resource&value=" +objTagOri.value,true);
    xmlhttp.send(null);
}

function ajaxUser(obj) {
var xmlhttp,url;
    if (obj.value=='') return;
    if (window.XMLHttpRequest){ 
        xmlhttp=new XMLHttpRequest();
    } else {
        alert("Your browser does not support XMLHTTP!");
        exit;
    }
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.readyState==4) {
            var str=xmlhttp.responseText.split("|");
            obj.value=str[1].replace(/^\s+|\s+$/g,"");
            obj.title=str[1];
        }
    }
    
    //alert(Page + objTagOri.value);
    url="../agendo/ajax.php?type=user&value=" + obj.value;
    xmlhttp.open("GET",url,true);
    xmlhttp.send(null);
}

function ajaxRecoverPWD() {
var xmlhttp,url;
obj=document.getElementById('user_idm');
    if (obj.value=='') return;
    if (window.XMLHttpRequest){ 
        xmlhttp=new XMLHttpRequest();
    } else {
        alert("Your browser does not support XMLHTTP!");
        exit;
    }
    
	var str = "An error occurred while changing your password.";
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.readyState==4) {
            str=xmlhttp.responseText;
            // document.getElementById('msg').innerHTML=str;
            // showfade('msg',2000);
			str = str.replace(/(\r\n|\n|\r)/gm," ");
			// alert(str);
        }
    }
    
    //alert(Page + objTagOri.value);
    url="../agendo/ajax.php?type=newpwd&value=" + obj.title; // title has user_id info
    xmlhttp.open("GET",url,true);
    xmlhttp.send(null);
    str = 'Your new password was sent.';
	// alert('Your new password was sent.');
	showMessage(str);
}
