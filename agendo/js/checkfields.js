/*
function clickit(id,action,table,nrows,user_id, order, page, limit, ninserts) {
    var iChars = "!#$%^&=[]\';{}|\"<>?";
    var iCharsINT = "0123456789";
    var iCharsREAL = ".0123456789";
    var CurForm=eval("document.tableman"+id);
    // alert(CurForm.length);
	
    if (action!='delete') {
        for (i=0;i<CurForm.length;i++) {
			var str = CurForm[i].lang;
            if (CurForm[i].value=='' &&  str.indexOf('__extkey') != -1){
                if(str.indexOf('not_null') != -1){
				CurForm[i].focus();
				alert ("Field " + CurForm[i].name + " required!");
				return;
			}
			} else if (CurForm[i].value=='' &&  str.indexOf('__extkey') == -1){
				if(str.indexOf('not_null') != -1){
					CurForm[i].focus();
					alert ("Field " + CurForm[i].name + " required!");
					return;
				}
			}
			if (CurForm[i].title=='string') {
                for (var k = 0; k < CurForm[i].value.length; k++) {
                    if (iChars.indexOf(CurForm[i].value.charAt(k)) != -1) {
                        CurForm[i].focus();
                        alert("Field " + CurForm[i].name + " Containts special characters. \n These are not allowed.\n Please remove them and try again.");
                        return;
                    }
                }
            }
            if (CurForm[i].title=='real') {
                for (var k = 0; k < CurForm[i].value.length; k++) {
                    if (iCharsREAL.indexOf(CurForm[i].value.charAt(k)) == -1) {
                        CurForm[i].focus();
                        alert("Field " + CurForm[i].name + " Containts non numerial characters. \n These are not allowed.\n Please remove them and try again.");
                        return;
                    }
                }
            }
            if (CurForm[i].title=='int') {
                for (var k = 0; k < CurForm[i].value.length; k++) {
                    if (iCharsINT.indexOf(CurForm[i].value.charAt(k)) == -1) {
                        CurForm[i].focus();
                        alert("Field " + CurForm[i].name + " Containts non numerical characters. \n These are not allowed.\n Please remove them and try again.");
                        return;
                    }
                }
            }
        }
    }
    if(action != 'multiple')
		var resp=confirm('Sure you want to ' + action + ' this record?');
    else //action == 'multiple'
		var resp=confirm('Sure you want to add multiple records?');
    
    if (resp) {
		if (action != 'delete')
		{
			var xmlhttp,url;
	      	for (i = 0; i < CurForm.length; i++)
		    {
				var val = CurForm[i].lang;
				if (CurForm[i].value != '' && val.indexOf('__extkey') != -1) 
				{
					if (window.XMLHttpRequest)
					{// code for IE7+, Firefox, Chrome, Opera, Safari
						xmlhttp=new XMLHttpRequest();
					}
					else
					{// code for IE6, IE5
						xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
					}
					
					url="../agendo/response.php?val=" + CurForm[i].value + "&id=" + CurForm[i].id;
					xmlhttp.open("GET", url, false);
					xmlhttp.send(null);
					
					var str = xmlhttp.responseText;
					CurForm[i].value = str;
				}
			}
        }
		if(action == 'multiple'){
			CurForm.action='../agendo/manager.php?table='+ table + '&action=' + action + '&nrows=' + nrows + '&col=' + table + '_id&order=' + order + '&page=' + page + '&limit=' + limit + '&n=' + ninserts; 
		}else 
			CurForm.action='../agendo/manager.php?table='+ table + '&action=' + action + '&nrows=' + nrows + '&col=' + table + '_id&order=' + order + '&page=' + page + '&limit=' + limit + '&id=' + id;  		
		for (i=0;i<CurForm.length;i++) {
			CurForm[i].disabled=false;
		}
        CurForm.submit();
    } 
}
*/

/*
function checkFill(id, action, table, nrows, order, user_id, page, limit, ninserts){
    var changedRows = new Array();
    for(var i = 0; i < document.forms.length; i++){
    	var CurForm = document.forms[i];
	var FormName = CurForm.name;
	if(FormName.substring(0,8) == 'tableman' && FormName.length > 8){
	    if(CurForm.elements[0].checked == true){
		changedRows.push(FormName);
	    }
	}
    }
        
    if(changedRows.length <= 1) //single action
	clickit(id,action,table,nrows,user_id, order, page, limit, ninserts);
    else{    //multiple action
	var resp=confirm('You are about to ' + action + ' ' + changedRows.length + ' records. Sure you want to proceed?');
	if(resp){
	    document.body.style.cursor = 'wait';
	    //alert(changedRows.length);
	    for(var j=0; j < changedRows.length; j++){
		for(var delay = 0; delay < 10000000; delay++){} //delay loop to sinchronize with php
		CurForm = eval("document." + changedRows[j]);
		for(var i=0; i<CurForm.length;i++){
		    var xmlhttp,url;
	      	    var val = CurForm[i].lang;
		    if (CurForm[i].value != '' && val.indexOf('__extkey') != -1) {
			    if (window.XMLHttpRequest)
			    {// code for IE7+, Firefox, Chrome, Opera, Safari
				    xmlhttp=new XMLHttpRequest();
			    }
			    else
			    {// code for IE6, IE5
				    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			    }
			    url="../agendo/response.php?val=" + CurForm[i].value + "&id=" + CurForm[i].id;
			    xmlhttp.open("GET", url, false);
			    xmlhttp.send(null);
			    var str = xmlhttp.responseText;
			    CurForm[i].value = str;
		    }
		}
		CurForm.action='../agendo/manager.php?table='+ table + '&action=' + action + '&nrows=' + nrows + '&col=' + table + '_id&order=' + order + '&page=' + page + '&limit=' + limit + '&id=' + id;  		
		for (i=0;i<CurForm.length;i++) CurForm[i].disabled=false;
		CurForm.submit();
	    }
	}
    }
    
}
*/

function validate_form(){
    var CurForm = eval("document.application");
    var val = document.getElementById('Department').value;
    var counter = 0;
    for (var i = 0; i < CurForm.length; i++){
	if(val != '0'){
            if(CurForm[i].value == '' && CurForm[i].id != 'GEDepartment'){
                CurForm[i].focus();
                alert("You need to fill all fields to submit request!");
                return;
            }
        } else {
            if(CurForm[i].value == ''){
                CurForm[i].focus();
                alert("You need to fill all fields to submit request!");
                return;
            } 
        }
    }
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
	xmlhttp=new XMLHttpRequest();
    } else {// code for IE6, IE5
	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    url="ajaxdpt.php?user=" + CurForm[8].value + "&fn=" + CurForm[0].value + "&ln=" + CurForm[1].value;
    xmlhttp.open("GET", url, false);
    xmlhttp.send(null);
    var str = xmlhttp.responseText;
    // if(str.length == 3){ //OK
    if(str == 'OK'){ //OK
	CurForm.action = "applyform.php";
	CurForm.submit();
    } else { //Already registered
	alert("You are already registered in agendo. If you wish to ask permission to use a resource please submit the 'New permission' form available at http://calendar.igc.gulbenkian.pt");
	return;	
    }
}

function showhide(id){
   var obj = document.getElementById(id);
   if(obj.style.display == "block")
       obj.style.display = "none";
   else
       obj.style.display = "block";
}