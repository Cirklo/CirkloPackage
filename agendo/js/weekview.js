// This file was altered by Pedro Pires
var bgcolor1,bgcolor2 ;
var mousedown=0,mousedownTimeout;
var curDate=new Date();
var res_status=1;
var res_maxslots;
var fadeConstant=1300;
var showingMsg = false;
var fadeCount;
var dateToUseInGet = "";
var resourceToUseInGet = "";

// variable used for a patch in the checkfields function
var usingSession;
var detectedUser;
var path = '';
var numericXfield = 'numericXfield';// if changing this, change the style.css class name as well
// var associated to the getCalendar function
var interval;

	var impersonateUser = '';
	$(document).ready(
		function(){
			if(macAddAppletUsed){
				possibleConfirmationText = document.getElementById("possibleConfirmationText");
				if(macIsConfirmed){
					document.getElementById("possibleConfirmationImgOk").style.display = "table";
					possibleConfirmationText.innerHTML = "Confirmation Possible";
				}
				else{
					var macFailReason = "You are not on the confirmation computer or you need the latest version of Java from the link";
					document.getElementById("possibleConfirmationImgNotOk").style.display = "table";
					possibleConfirmationText.innerHTML = "Confirmation Not Possible";
					if(noApplet){
						macFailReason = 'Applets are not recognized. Please download Java\'s latest version from the link.';
					}
					possibleConfirmationText.style.textDecoration = 'underline';
					possibleConfirmationText.href = "http://java.com/en/download/index.jsp";
					possibleConfirmationText.title = macFailReason;
				}
			}
			
			if(document.getElementById('usersList') != null){
				$("#usersList").focus(function(){
					$("#usersList").autocomplete({
						source: "../agendo/commonCode.php?usersList",
						minLength: 2,
						select: function(event, ui) {
									impersonateUser = ui.item.id;
								},
						dataType: "json"
					});
				});
			}
			else{
				impersonateUser = '';
			}
			
			var intervalLength = 180000; // 3 minutes
			interval = setInterval('getCalendar()', intervalLength);
		}
	);

	// Gets the calendar html
	function getCalendar(entry, action){
		if(resourceToUseInGet != '' && dateToUseInGet != ''){
			$.post(
				"weekview.php?resource=" + resourceToUseInGet + "&date=" + dateToUseInGet
				, {functionName: 'getCalendarWeek', entry: entry, action: action}
				, function(serverData){
					if(serverData.success){
						document.getElementById('calendar').innerHTML = serverData.calendar;
					}
					else{
						showMessage(serverData.message, true);
					}
				}
				, "json"
				)
				// This gets messed up because of the message display on this thing... Always shows an empty error string (messageInput value = '' ?)
				// .error(
					// function(error){
						// showMessage(error.responseText, true);
					// }
				// )
			;
		}
	}
	
	
	// Sets the date and resource for use in the getCalendar feature
	function setDateAndResource(resource, date){
		dateToUseInGet = date;
		resourceToUseInGet = resource;
	}
	
	function impersonateCheckChange(check){
		element = document.getElementById('impersonateCheck');

		if(check != null){
			element.checked = check;
		}
		else{
			element.check = !element.check;
		}
		if(!element.checked){
			impersonateUser = '';
			document.getElementById('usersList').value = "";
		}
	}
  
	// function impersonateCheckActive(){
		// document.getElementById('impersonateCheck').checked = true;
	// }
	
    document.onmousedown= function () {
        mousedown=0;
        window.clearInterval(mousedownTimeout);
        mousedownTimeout = window.setInterval(function() { mousedown += 5 }, 5);
        document.getElementById('calendar').style.MozUserSelect="none";
        document.body.onselectstart=function(){return false};
        document.getElementById('calendar').onselectstart==function(){return false};    
    }
    document.onmouseup= function () {
        mousedown = 0;
        window.clearInterval(mousedownTimeout);
        
    }
	
	function setPath(rightPath){
		path = rightPath; // always pick the right path young grasshopper
	}
	
// sets the usingSession global variable with the isUsing value (boolean)
function setUsingSession(isUsing){
	usingSession = isUsing;
}
    
function noenter() {
  return !(window.event && window.event.keyCode == 13); }
  
function button_visibility(add,del,monitor,update,confirm){
    document.getElementById('delButton').disabled=del;
    document.getElementById('addButton').disabled=add;
    document.getElementById('monitorButton').disabled=monitor;
    document.getElementById('updateButton').disabled=update;
	// if it exists
	if(editItemsButton = document.getElementById('editItemsButton')){
		editItemsButton.disabled = del; // disables when the del buttons is disabled and enables in the same way, why? Coz i didnt wanna create a var for this
	}
	
    if (res_status>2 || document.getElementById('update').value!=0){ // in the case there is no need for confirmation button
        document.getElementById('confirmButton').disabled=confirm ;
    } else {
        document.getElementById('confirmButton').disabled=true; 
    }
}

var selectedEntry = null;
function swapColor(obj,tag,action){
    //document.getElementById('datefield').selection.empty();
    objStyle=obj.style;
    oUpdVal=document.getElementById('update');
    oUpdBut=document.getElementById('updateButton');
    if ((mousedown<10) && (tag==0))return;
    if (objStyle.backgroundColor!=bgcolor1) {
        table=document.getElementById('caltable');
        tablesize=table.rows.length;
        // for checking span use table.rows[i].cells[j].rowSpan;
        if (action==1) { // select an existing entry
            // if(oUpdVal && oUpdVal.value!=0){
				// alert('teste');
				// return;
			// }
            clear_table(table);
            document.getElementById('addButton').value='All';
			if(res_status == 6){
				button_visibility(false,false,false,false,true);
			}
			else{
	            button_visibility(false,false,false,false,false);
			}
			selectedEntry = obj.title;
			if (window.XMLHttpRequest){
				xmlhttp=new XMLHttpRequest();
			} else {
				alert("Your browser does not support XMLHTTP!");
				exit;
			}
			url='../agendo/ajax.php?entry=' + obj.title + "&type=DisplayEntryInfo" ;

			xmlhttp.open('GET', url , true);
			xmlhttp.send(null);
			xmlhttp.onreadystatechange = function(){
				if(xmlhttp.readyState==4){
					eval(xmlhttp.responseText);
					//alert(xmlhttp.responseText);         
				}
			}
        // } else {
        } else if(document.getElementById('addButton') != null){
			selectedEntry = null;
            document.getElementById('addButton').value='Add';
			for (i=1;i<tablesize;i++)
				for (j=1;j<table.rows[i].cells.length;j++)
					if (table.rows[i].cells[j].innerHTML!='')
						table.rows[i].cells[j].style.backgroundColor=table.rows[i].cells[j].lang;
			if (document.getElementById('update').value==0)
				button_visibility(false,true,true,true,true);
        }
        objStyle.backgroundColor='#aaaaaa';
        bgcolor1=objStyle.backgroundColor;
        bgcolor2=bgcolor1;
    } else {
        objStyle.backgroundColor=obj.lang;
        //bgcolor2=objStyle.backgroundColor;
        if(action==1){
			button_visibility(true,true,true,true,true);
		}
    }
    document.getElementById('entry').value=0;
    
}

// this function enables and disables end date for repetitions
function activate_date(objInp){ objInp.disabled=(objInp.disabled^ true );} // exclusive or? (disabled=true)^true=> false, (disabled=false)^true=> true ?? how about objInp.disabled = !objInp.disabled ? :P 

function clear_table(table,cleartitle){
    var i,j,bg;
    bg='#ffffff';
    tablesize=table.rows.length;
    for(i=1;i<tablesize;i++){
        for(j=1;j<table.rows[i].cells.length;j++){
            if (table.rows[i].cells[j].innerHTML=='' && cleartitle) table.rows[i].cells[j].title=0;
            if (!cleartitle && table.rows[i].cells[j].style.backgroundColor) table.rows[i].cells[j].style.backgroundColor=table.rows[i].cells[j].lang;
        }
	}
}

function ManageEntries(action,ttime,tresolution) {
    var i,j,k=0,seed=0,add=0,tdatetime,tstartime;
    var bgcolor;
    var code;
    table=document.getElementById('caltable');
    tablesize=table.rows.length;
	
    //getting values from hidden input boxes
    document.getElementById('action').value = action;
    entry=document.getElementById('entry').value;
    tdate=document.getElementById('tdate').value;
    resource=document.getElementById('resource').value;
    update=document.getElementById('update').value;
   
    //code serves for eliminating javascript cache and to add multiple entries
    code = Math.random();
    document.getElementById('code').value = code;
    
    // this is a hack to get a color object
    document.getElementById('code').style.backgroundColor='#aaaaaa';
    bgcolor=document.getElementById('code').style.backgroundColor;
    
    var	impersonateUrl = "";
	if(impersonateUser != ''){
		impersonateUrl = "&impersonate=" + impersonateUser;
	}
    switch(action) {
        case 'del':
			detectedUser = true;
            for(i=1; i<tablesize; i++){
                for(j=1; j<table.rows[i].cells.length; j++){
                    cell = table.rows[i].cells[j];
                    if(cell.title!='0' && (cell.style.backgroundColor==bgcolor)){
                        document.getElementById('entry').value = cell.title;
                        ajaxEntries('GET','../agendo/process.php?deleteall=0&resource=' + resource + impersonateUrl ,true);
                        //alert ("No entries were selected");
                   }
                }
            }
			if(!detectedUser){
				document.getElementById('entry').value = '0';
			}
            if(entry != '0'){
                var resp = confirm('All associated entries will be deleted!');
                if (!(resp)) return;
                ajaxEntries('GET','../agendo/process.php?deleteall=1&resource=' + resource + impersonateUrl,true);
                clear_table(table,false);
           }
			button_visibility(true,true,true,true,true);
			document.getElementById('entry').value = 0;
        break;
        case 'add':
            var arr=new Array(); // creates an array to define cells with rowspan>1
			var getAssistance = document.getElementById('assistance').checked;
			var numberOfEntries = 0;

            if (update>0){ // for the update it has to send the entry
				document.getElementById('entry').value=update;
				document.getElementById('updateButton').value='Update';
				confirmValue = "Confirm";
				if(res_status == 6){ // sequencing type of resource
					confirmValue = "Cancel";
				}
				// document.getElementById('confirmButton').value='Confirm';
				document.getElementById('confirmButton').value = confirmValue;
				// document.getElementById('entry').value=document.getElementById('update').value = "0";
			}
            for(i=1;i<=tablesize;i++){
                arr[i]= new Array();
                for (j=1;j<8;j++) arr[i][j]=0; // fills the array
            }
            for(i=1;i<tablesize;i++){
                for(j=1;j<table.rows[i].cells.length;j++){
                    span=table.rows[i].cells[j].rowSpan;
                    for (x=1;x<=(j+add);x++) add=arr[i][x]+add;
                    if (span>1) for (k=i+1 ;k<i+span;k++) arr[k][j+add]=1; // sets to 1 the fields without
                    add=0; 
                }
                k=0;
            }
			for(i=1;i<tablesize;i++){
                ncells=table.rows[i].cells.length;
                for(j=1;j<8;j++){
                    add=0;
					
					// for checking where grow
                    for (x=1;x<=j;x++){
						add=arr[i][x]+add;
					}
					
                    cell=table.rows[i].cells[j-add];
                    if(cell.style.backgroundColor==bgcolor && cell.title=='0'){ // if the cell is white colored and their title is 0, meaning its an empty entry
                        while(table.rows[i+k].cells[j-add].style.backgroundColor==bgcolor && ((i+k) < tablesize)){
                            add=0;
                            seed=seed+1;
							
							// for checking where grow
                            for(x=1;x<=j;x++){
								add=arr[i+k][x]+add;
							}
							
                            table.rows[i+k].cells[j-add].title=seed; // due to rowspan
                            k=k+1;
                            if ((i+k)==tablesize){
								break;
							}
                            add=0;  
							
							// for checking where grow
                            for (x=1;x<=j;x++){
								add=arr[i+k][x]+add;
							}
                        }
						
                        if(res_maxslots<seed){
                            var resp = confirm('Exceeding daily maximum slots. Only reasonable in off peak time! Continue?');
                            if(!(resp)){
                                window.location.href = 'weekview.php?resource=' + resource + '&date=' + tdate;// remove?
                                return;
                            }
                        }   
						
						tstarttime= parseInt(ttime)+(i-1)*(tresolution);
                        // if (seed==0) exit;
                        if (seed==0) return;
                        var entryDate = new Date(tdate.substring(0,4),parseInt(tdate.substring(4,6),10)-1,parseInt(tdate.substring(6,8),10)+j,Math.floor(tstarttime),Math.round((tstarttime-Math.floor(tstarttime))*60),'00');
                        ajaxEntries('GET','../agendo/process.php?' + 'slots=' + seed + impersonateUrl + '&datetime=' + formatDate(entryDate,"yyyyMMddHHmm") + '&resource=' + resource + '&assistance=' + getAssistance,true);
                        seed=0;
                        k=0;
						
 						button_visibility(true,true,true,true,true);
						document.getElementById('entry').value = 0;
						document.getElementById('update').value = 0;
					} 
					else if(cell.title!='0' && document.getElementById('addButton').value=='All' && cell.style.backgroundColor==bgcolor2){ // all
                        // window.location.href='weekview.php?resource=' + resource + '&entry=' + cell.title;
						document.getElementById('entry').value = cell.title;
                        getCalendar(cell.title, 'all');
						button_visibility(true,false,true,true,true); // hides all buttons except del
					}
                }
            }
        break;
        case 'update':
            if(update!=0){
                ManageEntries('add',ttime,tresolution);
            } 
			else{
                for(i=1;i<tablesize;i++){
                    for (j=1;j<table.rows[i].cells.length;j++) {
                        cell=table.rows[i].cells[j];
                        if (cell.title!='0' && (cell.style.backgroundColor==bgcolor)){
							// window.location.href='weekview.php?resource=' + resource +'&update=' + cell.title +'&date=' + tdate;
							document.getElementById('entry').value = cell.title;
							document.getElementById('update').value = cell.title;
							getCalendar(cell.title, 'update');
							init();
							return;
                       }
                    }
                }
            }
        break;
        case 'monitor':
            for(i=1;i<tablesize;i++){
                for(j=1;j<table.rows[i].cells.length;j++){
                    cell=table.rows[i].cells[j];
                    if(cell.title!='0' && (cell.style.backgroundColor==bgcolor)){
						document.getElementById('entry').value=cell.title;
						ajaxEntries('GET','../agendo/process.php?&resource=' + resource + impersonateUrl,true);
						clear_table(table,false);                 
                    }
					else{
                       //alert ("No entries were selected");
                    }
                }
            }
        break;
        case 'confirm':
            if (update!=0) { // in real its a cancel
				// window.location.href='weekview.php?resource=' + resource + '&date=' + tdate; // comment this?
				// exit();
				getCalendar();
				button_visibility(true,true,true,true,true); // hides all buttons except del
				document.getElementById('updateButton').value = 'Update';
				document.getElementById('confirmButton').value = 'Confirm';
				document.getElementById('addButton').value = 'Add';
				document.getElementById('entry').value = 0;
				document.getElementById('update').value = 0;
				
				return;
            }
            objForm=document.getElementById('entrymanage');
            if (checkfield(objForm['user_id']) || checkfield(objForm['user_passwd'])){
				exit;
			}
            for (i=1;i<tablesize;i++) {
                for (j=1;j<table.rows[i].cells.length;j++) {
                    cell=table.rows[i].cells[j];
                    if (cell.title!='0' && (cell.style.backgroundColor==bgcolor)) {
                        objForm=document.getElementById('entrymanage');
						document.getElementById('entry').value=cell.title;
						myDiv = document.getElementById('InputComments');
						AssignPosition(myDiv);
						myDiv.style.display = "block";                
						//ajaxEntries('GET','process.php?&resource=' + resource,true,resource,tdate);
						//clear_table(table,false);                 
                    } 
					else{
                       //alert ("No entries were selected");
                    }
                }
            }
        break;
    }
}

function addcomments(entry) {
	myForm=document.getElementById('entrycomments');
    if (entry==0){ // has comments
        if (window.XMLHttpRequest){
            xmlhttp=new XMLHttpRequest();
        }
		else {
            alert("Your browser does not support XMLHTTP!");
            exit;
        }
        entry=document.getElementById('entry').value;
        resource=document.getElementById('resource').value;
        user_id=document.getElementById('user_id').title;
        if (myForm[0].value!='') {
            var re = /\'/g;
            myForm[0].value=myForm[0].value.replace(re," ");
            url='../agendo/process.php?resource=' + resource +'&entry=' + entry + '&user_id=' + user_id + '&action=addcomments&comments='+ myForm[0].value;
            xmlhttp.open('GET', url , true);
            xmlhttp.send(null);
        }
    }
	
	// post comment values
	nodes = document.getElementById('confirmXfields');// form allows to list the elements without garbage in the middle
	var idArray = new Array();
	var filled = true;
	var lastTableName = "";
	if(nodes != null){
		try{
			for(i=0; i<nodes.length; i++){
				fieldId = nodes[i].id.split('-');
				if(lastTableName != nodes[i].name){
					if(!filled){
						throw new Error("Please fill all fields");
					}
					else{
						filled = false;
						lastTableName = nodes[i].name;
					}
				}
				if(nodes[i].type == 'radio' || nodes[i].type == 'checkbox'){
					idArray[fieldId[1]] = nodes[i].checked;
					if(nodes[i].checked){
						filled = true;
					}
				}
				else if(nodes[i].className == numericXfield){
					if(!textIsNumeric(nodes[i].value)){
						throw new Error('Not a numeric value');
					}
					idArray[fieldId[1]] = nodes[i].value;
					if(nodes[i].value != ''){
						filled = true;
					}
				}
				else{
					idArray[fieldId[1]] = nodes[i].value;
					if(nodes[i].value != ''){
						filled = true;
					}
				}
			}
			// Used for the last "element" of the form (not checked in the for)
			if(!filled){
				throw new Error("Please fill all fields");
			}
		}
		catch(err){
			showMessage(err.message);
			return;
		}
		
		$.post('../agendo/process.php?action=addCommentsXfields', {entry: document.getElementById('entry').value, resource: resource, idArray: idArray});
	}
	//*********************
    
    ajaxEntries('GET','../agendo/process.php?resource=' + resource,true);
    clear_table(table,false); 
	// Hide the comments div
    document.getElementById('InputComments').style.display = "none";
	// Cleans the data inside the comments
	myForm[0].value = '';
}

checkedValue = '9001!';
atLeastOneChecked = false;
nameTable = '';
// haha brilliant name, oh you....
// is used to tell between uninteresting checkboxs/radiobuttons and "super sayajin"/xfield ones
function checkIfNotChecked(elementNumber, objForm){
	nameTable = objForm[elementNumber].name;
	if(elementNumber == (objForm.length-1) || objForm[elementNumber+1].name != nameTable || objForm[elementNumber+1].value != checkedValue)
		return checkIfNotCheckedAux(objForm[elementNumber], true);
	else
		return checkIfNotCheckedAux(objForm[elementNumber], false);
}

function checkIfNotCheckedAux(field, last){
	if(field.checked)
		atLeastOneChecked = true;
	nonChecked = false;	
	if(last){
		if(!atLeastOneChecked){
			// document.getElementById('msg').innerHTML = "Field " + nameTable + " required!";
			// showfade('msg',fadeConstant);
			showMessage("Field " + nameTable + " required!", true);
			clear_table(document.getElementById('caltable'),true);
			nonChecked = true;
		}
		atLeastOneChecked = false;
		nameTable = '';
		last = false;
	}
	return nonChecked;
}

// function ajaxEntries(method,url,nosync){
function ajaxEntries(method,url,nosync){
    // document.body.style.cursor='wait';
    var nelements, par = '', objForm;
    action=document.getElementById('action').value;
    objForm=document.getElementById('entrymanage');
    date=document.getElementById('tdate').value;
    resource=document.getElementById('resource').value;
    // if (window.XMLHttpRequest){
        // xmlhttp=new XMLHttpRequest();
    // } else {
        // alert("Your browser does not support XMLHTTP!");  
    // }

    switch(action){
		case 'add':
		case 'update':
		case 'monitor':
			if(!formElementsCheckedOut(objForm)){
				return;
			}
		break;
		
		case 'del':
			// if (checkfield(objForm['user_id'])){detectedUser = false; return;}
			// if (checkfield(objForm['user_passwd'])){detectedUser = false; return;}
			if (checkfield(objForm['user_id']) || checkfield(objForm['user_passwd'])){
				detectedUser = false;
				return;
			}
		break;
		
		case 'confirm':
			par += 'mac=' + macIsConfirmed + '&';
		break;
    }
	
    // builds post string
    for (nelements=0;nelements<objForm.length;nelements++){
        if (objForm[nelements].lang=='send') {
			if (objForm[nelements].type=='checkbox' || objForm[nelements].type=='radio') {
				par=par+ objForm[nelements].id + '=' + objForm[nelements].checked + "&";
			} else {
				par=par+ objForm[nelements].id + '=' + objForm[nelements].value + "&";
			}
		}
    }

	// par has username and pass
	// $.get instead of the window.location thing below, i will probably regret this
	$.get(
		url + '&' + par
		, function(serverData){
			if(serverData.functionName){
				window[serverData.functionName].apply(this, serverData.arguments); // calls a usermade function with dinamic name and arguments
			}
			else{
				showMessage(serverData.message, serverData.isError);
			}
			clear_table(document.getElementById('caltable'),true);
			getCalendar();
		}
		, "json"
		)
	;

	userLogin = objForm.user_id.value;
	userPass = objForm.user_passwd.value;
	objForm.user_id.value = "";
	objForm.user_passwd.value = "";

	// xmlhttp.open(method, url + '&' + par, nosync);
	// xmlhttp.send(null);
    // xmlhttp.onreadystatechange = function(){
        // if(xmlhttp.readyState==4) {
            // code=document.getElementById('code').value;
            // window.location.href='weekview.php?resource=' + resource + '&date=' + date+ '&code=' + code + '&msg=' + xmlhttp.responseText;
        // }
    // }
}

function formElementsCheckedOut(objForm){
	for (nelements=0;nelements<objForm.length;nelements++){
		// its over 9000!!!!
		if(objForm[nelements].value == checkedValue && checkIfNotChecked(nelements, objForm))
			return false;
		else if (objForm[nelements].className == numericXfield && !textIsNumeric(objForm[nelements].value)){
			showMessage("Not a numeric value", true);
			clear_table(document.getElementById('caltable'),true);
			return false;
		}
		else if (checkfield(objForm[nelements]))
			return false;
	}

	return true;
}

var macIsConfirmed = false;
var noApplet = false;
var macAddAppletUsed = false;
function macConfimation(givenMac){
	macAddAppletUsed = true;
	if((applet = document.getElementById('zeeApplet')) != null){
		macIsConfirmed = applet.rightMac();
	}
	else{
		try{
			html = "<applet code='MacAddressApplet' archive='../agendo/macApp.jar'	width='0' height='0' id='zeeApplet' ><param name='color' value='#1e4F54' /><param name='action' value='checkMac' />	<param name='mac' value='" + givenMac + "' /></applet>";
			document.writeln(html);
		}
		catch(err){
			// showMessage(err.message);
		}
		if((applet = document.getElementById('zeeApplet')) == null){
			// showMessage('Applets are not recognized.');
			noApplet = true;
		}
		else{
			macIsConfirmed = applet.rightMac();
		}
	}
}

function filljscombo(tagname,start,end,resolution,selection,entry){
    var i=0,sel='',dis=true;
    if (entry!=0){
		dis=false;
	}
    document.write("<select lang=send id=" + tagname + " name=" + tagname + " disabled=" + dis + ">");
    while(start<end){
        if (start==selection) sel='selected';
        document.write("<option " + sel + " value=" +i + ">"+ start + "</option>");
        i++;
        sel='';
        start=start+resolution;
    }
    document.write("</select>");
    document.getElementById(tagname).disabled=dis;
}

function init(s,m){
	if(document.getElementById('enddate') != null){
		document.getElementById('enddate').value = formatDate(curDate,"yyyy-M-dd");
		if (document.getElementById('entry').value!='0') {
			if (document.getElementById('update').value!='0') {
				table=document.getElementById('caltable');
				clear_table(table);
				document.getElementById('updateButton').value='Change';
				document.getElementById('confirmButton').value='Cancel';
				button_visibility(true,true,true,false,false);
			} else {
				button_visibility(true,false,true,true,true);
			}
			
			
		} else {
			button_visibility(true,true,true,true,true);
		}
		document.getElementById('msg').style.visibility='hidden';
		showfade('msg', fadeConstant);
		//The behavior will depend on this
		res_status=s;
		res_maxslots=m;
	}
}

function submitUser(resource) {
    if((formObj = document.getElementById('edituser')) != null){
		// if (checkfield(formObj.user_idm)) return;
		// if (checkfield(formObj.user_passwd)) return;
		if (checkfield(formObj.user_idm) || checkfield(formObj.user_passwd)){
			return;
		}

		formObj.user_idm.value = formObj.user_idm.title;
		formObj.action = '../agendo/index.php?resource=' + resource;
		formObj.submit();
	}
}

function addRadioOrCheck(tableName, id, label, type, checked){
	beginHtml = "<tr><td colspan=2>";
	endHtml = "</td></tr>";
	// name = tableName + id;
	name = tableName;
	typeName = 'checkbox';
	if(type == 2){
		// name = tableName;
		typeName = 'radio';
	}
	extraHtml = beginHtml + "<label><input lang='send' type='" + typeName + "' name='" + name + "' id='" + tableName + "-" + id + "' value='" + checkedValue + "' " + checked + ">&nbsp;" + label + "</label>" + endHtml;
	document.getElementById(tableName).innerHTML = document.getElementById(tableName).innerHTML + extraHtml;
}

// Checks if a field is empty, returns true if it is
function checkfield(field) {  
	// this is a horrible, horrible patch but still brilliant (the chosen two has done it again)
	// its purpose is to be able to ignore the login fields that are empty when the user and pass are as session variables
    if(usingSession && (field.name == 'user_id' || field.name == 'user_passwd')){
		return false;
	}

    if (field.value=='' && field.className != 'emptyAllowedText') {
		field.focus();
        if (document.getElementById('msg').innerHTML != "Field " + field.name + " required!")
			document.getElementById('msg').innerHTML = "Field " + field.name + " required!";
			
        showfade('msg',fadeConstant);
        clear_table(document.getElementById('caltable'),true);
        return true;
    }
    return false;
}

// function similarResources(value){
	// window.location="./weekview.php?resource=" + value;
// }

function calendarReduceRowSpan(id){
	if((element = document.getElementById(id)) != null){
		element.rowSpan--;
	}
}

function showfade(element,count){
	obj=document.getElementById('msg');
	if (obj.innerHTML != ""){
		// $.jnotify(obj.innerHTML);
		showMessage(obj.innerHTML);
		
	// fadeCount = count;
	// if(!showingMsg){
		// obj=document.getElementById('msg');
		// if (obj.innerHTML != "") {
			// obj.style.color = 773333;
			// obj.style.fontSize = 25;
			// obj.style.visibility = 'visible';
			// showingMsg = true;
			// showFadeAux(element, fadeCount)
		// }
	}
	document.body.style.cursor='default';
} 

// not being used
// function showFadeAux(element, count){
	// if(fadeCount > 0){
		// obj=document.getElementById(element);
		// try {
			// obj.filters.alpha.opacity=fadeCount/10; // for ie
		// } catch (err) {
			// obj.style.opacity=fadeCount/1000;
		// }
		// fadeCount = fadeCount - 20;
		// timerID = setTimeout("showFadeAux('" + element + "'," + fadeCount + ")", 100);
	// }
	// else{
		// obj.style.visibility = 'hidden';
		// obj.value='';
		// showingMsg = false;
	    // document.body.style.cursor='default';
	// }
// }
