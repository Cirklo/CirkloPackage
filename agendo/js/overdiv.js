//<!-- Copyright 2006,2007 Bontrager Connection, LLC
// http://bontragerconnection.com/ and http://www.willmaster.com/
// Version: July 28, 2007
var cX = 0; var cY = 0; var rX = 0; var rY = 0;
function UpdateCursorPosition(e){ cX = e.pageX; cY = e.pageY;}
function UpdateCursorPositionDocAll(e){ cX = event.clientX; cY = event.clientY;}
if(document.all) { document.onmousemove = UpdateCursorPositionDocAll; }
else { document.onmousemove = UpdateCursorPosition; }

function AssignPosition(d) {
	if(self.pageYOffset) {
		rX = self.pageXOffset;
		rY = self.pageYOffset;
		}
	else if(document.documentElement && document.documentElement.scrollTop) {
		rX = document.documentElement.scrollLeft;
		rY = document.documentElement.scrollTop;
		}
	else if(document.body) {
		rX = document.body.scrollLeft;
		rY = document.body.scrollTop;
		}
	if(document.all) {
		cX += rX; 
		cY += rY;
		}
	d.style.left = (cX+10) + "px";
	d.style.top = (cY+10) + "px";
}




function HideContent(d) {
    document.getElementById(d).style.display = "none";
}




function ShowContent(d,entry_id) {
    var dd = document.getElementById(d);
    AssignPosition(dd);
    if (window.XMLHttpRequest){ 
        xmlhttp=new XMLHttpRequest();
    } else {
        alert("Your browser does not support XMLHTTP!");
        exit;
    }
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.readyState==4) {
            var str=xmlhttp.responseText;
            dd.innerHTML=str;
        }
    }
    
    
    url="../agendo/ajax.php?type=" + d + "&value=" + entry_id; // title has user_id info
    xmlhttp.open("GET",url,true);
    xmlhttp.send(null);
    dd.style.display = "block";
}
