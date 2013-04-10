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
	obj=document.getElementById('user_idm');

	if (!obj || obj.value=='') return;
	
	var confirmAction = confirm("Are you sure you want to recover " + obj.value + "'s password?");
	if(confirmAction){
		
		url="../agendo/ajax.php?type=newpwd&value=" + obj.value; // title has user_id info
		
		$.post(url,
				function(serverData){
					showMessage(serverData);
				}
			)
			.error(
				function(error) {
					showMessage(error.responseText, true);
				}
			)
		;
	}
}
