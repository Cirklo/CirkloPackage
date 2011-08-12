
function setMatching(sourceObj, targetObj, mainObj){
	if($("#"+sourceObj).attr("checked")){	//if the checkbox is checked
		$("#"+targetObj).attr("disabled","");
		ajaxEquiDD(mainObj, targetObj);
	} else {
		$("#"+targetObj).attr("disabled","disabled");
	}
}

function ajaxEquiDD(objTagOri,objNameDest) {
	objTagDest=document.getElementById(objNameDest);
	while (objTagDest.firstChild) {objTagDest.removeChild(objTagDest.firstChild);}
    optionItem = document.createElement('option');
//    objTagDest.appendChild(optionItem);
//    optionItem.value='';
//    optionItem.appendChild(document.createTextNode('select field...'));

    //ajax request
    switch (objNameDest){
    case "targetUnique":
        type=0;
        table=objTagOri.value;
        break;
    case "targetMatching":
    	type=0;
    	table=$("#"+objTagOri).val();
    	break;
    }
    //set ajax URL
    url="auxFunctions.php";
	$.get(url,{
		type:type,
		table:table},
		function (str){
//			alert(str);
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
		});

	//if the checkbox is checked
    if($("#cbMatching").attr("checked") && objNameDest=="targetUnique"){
    	//dont know why I can't do this straight. Must set a small timeout in order to work
    	setTimeout("ajaxEquiDD('targetTable','targetMatching');",100);
    	
    }
    
    
}

function goValidation(){
	//set cursor on waiting mode
	document.body.style.cursor = "wait";
	//set validation options form
	var CurForm=eval("document.options");
	var targetTable=$("#targetTable").val();	//get table value
	var uniqueField=$("#targetUnique").val();	//get unique field
	var file=$("#file").val();
	//validation
	if(targetTable==0){	//table validation
		$.jnotify("Please select a valid table");
		document.body.style.cursor = "default";
		return;
	} 
	if(uniqueField==0){	//unique field validation
		$.jnotify("Please select a valid unique field");
		document.body.style.cursor = "default";
		return;
	}
	if(file==""){	//file validation -> do not check if it is the right extension or not. Just checks if it has a file or not
		$.jnotify("Select a file to proceed");
		document.body.style.cursor = "default";
		return;
	}
	//check of there's a matching key or not
	/*
	 * dataErase option 0 - Do not delete
	 * dataErase option 1 - Delete all
	 * dataErase option 2 - Delete only matching key related
	 */
	if(!$("#cbMatching").attr("checked") && $("#dataErase").val()==2){
		$.jnotify("You have to enable matching key option in order to delete related data");
		document.body.style.cursor = "default";
		return;
	}
	
	//check delete option in order to display the right confirmation
	var deleteOption=$("#dataErase").val();
	switch (deleteOption){
	case "0":
		ask="You are about to insert new data into the table "+targetTable+". Proceed?";
		break;
	case "1":

		ask="You are about to delete all data from the table "+targetTable+" and insert new data from the file. Proceed?";
		break;
	case "2":
		ask="You are about to replace all data from the table "+targetTable+" that matches the value in your file's " +
				"matching key. Proceed?";
		break;
	}
	resp=confirm(ask);
	if(resp){
		CurForm.action = "validation.php";
		CurForm.submit();
	} else {
		alert("Action denied by user");
		document.body.style.cursor = "default";
		return;
	}
}

function startImport(objName, unique, matchingKey, delOption, filename, errorArray){
	var resp=confirm("Are you sure you want to start importing the file?");
	if(resp){
		document.body.style.cursor="wait";
		if(matchingKey){
			matchingKeyValue=$("#matchingKey").val();
		} else {
			matchingKeyValue=null;
		}
		var url="inject.php";
		$.post(url,{
			objName:objName,
			unique:unique,
			matchingKey:matchingKey,
			matchingKeyValue:matchingKeyValue,
			delOption:delOption,
			path:filename,
			error:errorArray
		}, function(data){
			$.jnotify(data);
			document.body.style.cursor="default";
		});
	} else {
		$.jnotify("Action cancelled by the user");
		document.body.style.cursor="default";
	}

}
