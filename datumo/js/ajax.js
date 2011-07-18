

/**
 * @author João Lagarto / Nuno Moreno
 * @description method to handle ajax http requests
 * @param input parameter : url -> 'url' plus variables
 */

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

function ajaxEquiDD(objTagOri,objNameDest) {
	document.body.style.cursor = "default";
	var xmlhttp,url;
	objTagDest=document.getElementById(objNameDest);
	if(objNameDest == 'Matching'){
		objTagOri = document.getElementById("tables");
	}
	while (objTagDest.firstChild) {objTagDest.removeChild(objTagDest.firstChild);}
	    optionItem = document.createElement('option');
	    objTagDest.appendChild(optionItem);
	    optionItem.value='';
	    optionItem.appendChild(document.createTextNode('Select attribute...'));
	    //alert(Page + objTagOri.value);
	    //ajax request
	    var str = ajaxRequest("ajaxtable.php?type=0&table=" + objTagOri.value);
	    
	    var a=new Array();
	    var b=new Array();
	   // alert(str);
	    a=str.split("<name>");
	    for (i=1;i<a.length;i++) {
		    optionItem = document.createElement('option');
		    b=a[i].split("<value>");
		    optionItem.value=b[1];
		    optionItem.appendChild(document.createTextNode(b[0]));
		    objTagDest.appendChild(optionItem);
	    }
	}

function selOperator(id, event){
	//initialize variables to handle operators and foreign keys
	var j = document.getElementById("multiple").value;
	if(flag==1 || id=="field"){ //only one query to the database or onchange event was triggered by the first row
		var selOp = document.getElementById("operator");   
		var field = document.getElementById("field").value;
		var val = document.getElementById("val");
	} else { //multiple queries/clauses
		var selOp = document.getElementById("operator__"+j);   
		var field = document.getElementById("field__"+j).value;
		var val = document.getElementById("val__"+j);
	}
	var objName = document.getElementById("table").value;
   
	//initialize onkeyup event for all text fields in the filter (foreign key fields will have an event)
	val.onkeyup = function(){};
	
	//restart selector options
	selOp.length = 0;
	url="ajaxtable.php?type=1&table=" + objName + "&field=" + field;
    var str = ajaxRequest(url);
    
    //go through all datatype possibilities
    if(str == "varchar" || str == "character varying"){ //mysql and pgsql strings
    	optionItem = document.createElement('option');
        optionItem.value=0;
        optionItem.appendChild(document.createTextNode("regexp"));
        selOp.appendChild(optionItem);
    } else if (str=="integer" || str=="int" || str=="double" || str=="date" || str=="datetime" || str=="double precision"){//integer, double, float, date and datetime
	    optionItem = document.createElement('option');
	    optionItem.value=1;
	    optionItem.appendChild(document.createTextNode("="));
	    selOp.appendChild(optionItem);
	
	    optionItem = document.createElement('option');
	    optionItem.value=2;
	    optionItem.appendChild(document.createTextNode("<"));
	    selOp.appendChild(optionItem);
	
	    optionItem = document.createElement('option');
	    optionItem.value=3;
	    optionItem.appendChild(document.createTextNode(">"));
	    selOp.appendChild(optionItem);
	    if(str == "date" || str == "datetime"){
	    	val.onfocus = function(){showCalendarControl(this);};
	    }
    } else { //foreign keys
    	optionItem = document.createElement('option');
	    optionItem.value=4;
	    optionItem.appendChild(document.createTextNode("Foreign Key"));
	    selOp.appendChild(optionItem);
	    val.onfocus = function(){
	    	//alert(str);
	    	$(this).simpleAutoComplete("autoSuggest.php?field="+field);
	    };
    }
}
