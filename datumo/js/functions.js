
/**
 * @author João Lagarto
 * @description function to hide/unhide divs
 * @param id
 */
var browser=navigator.appVersion;
if(browser.indexOf("Chrome")==-1) browser="";
else browser="Chrome";

function countchars(id){
	var txt = $("#"+id).val(); 
	$("#noChars").val(txt.length);
	
}

function cleanForm(form){
	var CurForm = eval("document."+form);
	for(var i=0;i<(CurForm.length-1);i++){
		$(CurForm[i]).val("");
	}
}

function showColumnComments(){
	if($(".columnComments").css("display")=="block"){
		$(".columnComments").css("display","none");
	} else {
		$(".columnComments").css("display","block");
	}
}

function showhide(id){
	var obj = document.getElementById(id);
	var allobj = document.getElementsByTagName("div");
	if(obj.style.display == "block")
		obj.style.display = "none";
	else{
		//need to create a method that hides all divs from the same class
		//hide the remaining divs
		for (var i=0; i<allobj.length-1;i++){
			$(".regular").css("display","none");
			$(".comments").css("display","none");
			$(".details").css("display","none");
		}
		obj.style.display = "block";
	}
}

/**
 * @author João Lagarto / Nuno Moreno
 * @description handle multiple form submit (update and delete)
 */
//initialize datatype expressions
var iChars = "!#$%^[]\';{}|";
var iCharsINT = "0123456789";
var iCharsREAL = ".0123456789";

function checkfields(action,objName,nrows, order, colOrder,search,page){
	//number of rows to be updated
	var count = noChanges(nrows);
	if(count!=0) var resp=confirm("You are about to "+action+" "+count+" record(s). Proceed?");
	else {
		alert("No records to "+action+"!");
		return;
	}
	if(!resp) return;
	for(var k=0;k<nrows;k++){
		try {
			var cb = document.getElementById("cb"+k).checked;
		} catch (err){				
			break;
		}
		if(cb){
			var CurForm=eval("document.tableman"+k);
			if(action=="update"){
				for(var i=0;i<CurForm.length;i++){
					//field validation datatype
					if(CurForm[i].lang!='__fk'){
						if(CurForm[i].alt=="NO" && CurForm[i].value=="" && i!=0){
							CurForm[i].focus();
	                        alert("Field cannot be null!");
	                        return;
						}
						//Field characters validation
						switch(CurForm[i].lang){
						case "character varying":
						case "varchar":
							for (var j = 0; j < CurForm[i].value.length; j++) {
			                    if (iChars.indexOf(CurForm[i].value.charAt(j)) != -1) {
			                        CurForm[i].focus();
			                        alert("Field " + CurForm[i].name + " contains special characters. \n These are not allowed.\n Please remove them and try again.");
			                        return;
			                    }
			                }
							break;
						case "double":
						case "double precision":
							for (var j = 0; j < CurForm[i].value.length; j++) {
			                    if (iCharsREAL.indexOf(CurForm[i].value.charAt(j)) == -1) {
			                        CurForm[i].focus();
			                        alert("Field " + CurForm[i].name + " contains special characters. \n These are not allowed.\n Please remove them and try again.");
			                        return;
			                    }
			                }
							break;
						case "int":
						case "integer":
							for (var j = 0; j < CurForm[i].value.length; j++) {
			                    if (iCharsINT.indexOf(CurForm[i].value.charAt(j)) == -1) {
			                        CurForm[i].focus();
			                        alert("Field " + CurForm[i].name + " contains special characters. \n These are not allowed.\n Please remove them and try again.");
			                        return;
			                    }
							}
							break;
						}
					} 
				}
				var arrval=new Array;
				for (var j=0;j<CurForm.length;j++){
					if(CurForm[j].lang=='__fk'){
						if(CurForm[j].alt==""){
							//ajax request
							url="ajax.php?val=" + CurForm[j].value + "&var=" + CurForm[j].id;
						    var str = ajaxRequest(url);
						    //if foreign key is null
						    if(str == ""){
						    	CurForm[j].focus();
						    	alert("Field cannot be null! Please use the autocomplete tool");
						    	return;
						    } else {
						    	arrval[CurForm[j].id]=str;	
						    }
						} else {
							arrval[CurForm[j].id]=CurForm[j].alt;
						}
					}
				}
				//write to textbox Foreign key values
				for (var j=0;j<CurForm.length;j++){
					if(CurForm[j].lang=='__fk'){
						CurForm[j].value=arrval[CurForm[j].id];
					}
				}
			}		
			url="manager.php?table="+objName+"&nrows=20&action="+action+"&order="+order+"&colOrder="+colOrder+"&search="+search+"&page="+page;
			for (i=0;i<CurForm.length;i++) {   CurForm[i].disabled=false;}
			CurForm.action = url;
			objForm = eval("document.table");
			try{
				CurForm.submit();
				//alert(browser);
				if(browser!="Chrome"){
					filter('table',objName,'',order,colOrder,page,action);
				}
				
			} catch (err){
				alert("Form not submitted!"+err);
			}
		}
		
	}
}

/**
 * @author João Lagarto / Nuno Moreno
 * @description check all checkboxes in the page
 */

function checkall(id,nrows){
	for(var i=0;i<nrows;i++){
		try {
			var cb = document.getElementById("cb"+i).checked;
		} catch (err){
			return;
		}
		if(document.getElementById(id).checked){
			document.getElementById("cb"+i).checked = true;
		} else {
			document.getElementById("cb"+i).checked = false;
		}
	}
}


/**
 * @author João Lagarto / Nuno Moreno
 * @description checked boxes counter
 */

function noChanges(nrows){
	var count=0;
	for(var k=0;k<nrows;k++){
		try{ 
			if(document.getElementById("cb"+k).checked) count++;
		} catch(err) {
			break;
		}	
	}
	return count;
}

/**
 * @author João Lagarto / Nuno Moreno
 * @description function to handle insert clones
 * @extra Multiple insert handling
 *  
 */

//counter initializer -> variable created for control purposes
var noInserts=1;
function checkMultiple(type, id){
	if(type == 'sum'){	
		if(noInserts==10) {
			alert("No more fields allowed!");
			return;
		}
		var val = document.getElementById('multiple').value++;
		noInserts++;
		cloneMe(id, val,true);
	} else {
		noInserts--;
		deleteMe(id);
	}	
}

/**
 * @author João Lagarto / Nuno Moreno
 * @description function to check insert fields and redirect to php class
 */

function multiAdd(objName){
	//check for the number of available insert forms
	var len=document.forms.length;
	var resp=confirm("You are about to insert "+noInserts+" new record(s). Proceed?");
	if(!resp) return;
	//loop through all insert forms
	for(var i=(len-noInserts); i<len;i++){
		var CurForm = document.forms[i];
		for (var j=0;j<CurForm.length;j++){
			//is this a foreign key? if not proceed to datatype check
//			alert(CurForm[j].id);
			if(CurForm[j].lang!='__fk'){
				//check if field is null
				if(CurForm[j].alt=="NO" && CurForm[j].value=="" && j!=0){
					CurForm[j].focus();
	                alert("Field cannot be null!");
	                return;
				}
				switch(CurForm[j].lang){
				case "character varying":
				case "varchar":
					for (var k = 0; k < CurForm[j].value.length; k++) {
	                    if (iChars.indexOf(CurForm[j].value.charAt(k)) != -1) {
	                        CurForm[j].focus();
	                        alert("Field " + CurForm[j].name + " contains special characters. \n These are not allowed.\n Please remove them and try again.");
	                        return;
	                    }
	                }
					break;
				case "double":
				case "double precision":
					for (var k = 0; k < CurForm[j].value.length; k++) {
	                    if (iCharsREAL.indexOf(CurForm[j].value.charAt(k)) == -1) {
	                        CurForm[j].focus();
	                        alert("Field " + CurForm[j].name + " contains special characters. \n These are not allowed.\n Please remove them and try again.");
	                        return;
	                    }
	                }
					break;
				case "int":
				case "integer":
					for (var k = 0; k < CurForm[j].value.length; k++) {
	                    if (iCharsINT.indexOf(CurForm[j].value.charAt(k)) == -1) {
	                        CurForm[j].focus();
	                        alert("Field " + CurForm[j].name + " contains special characters. \n These are not allowed.\n Please remove them and try again.");
	                        return;
	                    }
	                }
					break;
				}
			}
		}
		
		//how am I going to use to protect the autosuggest tool?
		//Two cycles don't seem to be a very good way to do this
		var arrval=new Array;
		for (var j=0;j<CurForm.length;j++){
			if(CurForm[j].lang=='__fk'){
				if(CurForm[j].alt==""){
					//ajax request
					url="ajax.php?val=" + CurForm[j].value + "&var=" + CurForm[j].id;
				    var str = ajaxRequest(url);
				    //if foreign key is null
				    if(str == ""){
				    	CurForm[j].focus();
				    	alert("Field cannot be null! Please use the autocomplete tool");
				    	return;
				    } else {
				    	arrval[CurForm[j].id]=str;	
				    }
				} else {
					arrval[CurForm[j].id]=CurForm[j].alt;
				}
			}
		}
		//write to textbox Foreign key values
		for (var j=0;j<CurForm.length;j++){
			CurForm[j].disabled=false;	
			if(CurForm[j].lang=='__fk'){
				CurForm[j].value=arrval[CurForm[j].id];
			}
		}
		url="manager.php?table="+objName+"&nrows=20&action=insert&comeFromAction=insert";
		CurForm.action = url;
		try{
			CurForm.submit();
			//if(browser!="Chrome")	filter('table',objName,'','','');
		} catch (err){
			alert("Form not submitted!"+err);
		}
	}	
}


function getdetails(id, table, val, bool){
	if(bool){ //treeview
		url="details.php?table=" + table + "&value=" + val + "&id="+id+"&bool";
	} else { //manager
		url="details.php?table=" + table + "&value=" + val + "&id="+id;
	}
	var str = ajaxRequest(url);		    
	document.getElementById(id).innerHTML = str;
}

function copy(id){
	var CurForm=eval("document.tableman"+id);
	var targerForm=eval("document.tableman");
	for (var i = 1; i < CurForm.length; i++){
	    targerForm.elements[i].value = CurForm.elements[i].value;
	}
}

function selectRow(id){
	document.getElementById("cb"+id).checked=true;
}

function redirect(objName){
	window.open("list.php?table="+objName,"_blank","width=350px,height=400px,scrollbars=yes");
}

function sendMsg(user){
	var len = $("#noChars").val();
	if(len>=200){
		alert("Maximum number of characters exceeded! (max. 200 chars)");
		return;
	}
	var userto = $("#touser").val();
	//if the index is 0 then the message is sent to everyone
	if($("#touser").index()==0) userto = user;
	var subject = $("#subject").val();
	var msg = $("#msgArea").val();
	if(subject=="" || msg==""){
		alert("You must enter all fields to proceed!");
		return;
	} else {
		url = "ajaxMsg.php?user="+user+"&to="+userto+"&subject="+subject+"&msg="+msg+"&nmsg="+nmsg;
		alert(url);
		var str = ajaxRequest(url);
		alert(str);
	}
}

function refresh(){
	var nmsg = $("#nmsg").val();
	if(!isNumber(nmsg)){
		alert("Please enter a valid number!");
		return;
	}
	
	if(nmsg < 1 || nmsg >20){
		alert("You can only display between 1 and 20 messages!");
		return;
	}
	window.location = "admin.php?nmsg="+nmsg;
}

function isNumber(n) {
	  return !isNaN(parseFloat(n)) && isFinite(n);
	}

function dynReport(form, id){
	var CurForm=eval("document."+form);
	//Loop through all form members to verify if the fields are null or not
	for(var i=0;i<(CurForm.length-1);i++){
		if(CurForm[i].value==""){
			alert("You must enter all parameters to generate the report!");
			CurForm[i].focus();
			return;
		}
	}
	//set action for the current FORM and tells the script this is a dynamic form
	CurForm.action = "report.php?report="+id+"&d";
	CurForm.target="_blank";
	CurForm.submit();
}


