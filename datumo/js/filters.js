
/**
 * @author João Lagarto / Nuno Moreno
 * @description function to submit filter forms
 */

function filter(name, objName, j, order, colOrder,page,comeFromAction){
	//number of rows to be displayed
	if(j=="")
		var nrows=20;
	else {
		var nrows = document.getElementById("nrows"+j).value;
		if(!isNumber(nrows)){
			alert("Please insert a valid number!");
			return;
		}
	}
	//get form
	var CurForm = eval("document."+name);
	for(var i=0;i<(CurForm.length-1);i++){
		if(CurForm[i].alt==""){
			var newId=CurForm[i].id;
			url="ajax.php?val=" + CurForm[i].value + "&var=" + newId.substring(0,newId.length-3);
			var str = ajaxRequest(url);
			if(CurForm[i].lang=='__fk'){
				CurForm[i].value = str;	
			}
		} else {
			CurForm[i].value=CurForm[i].alt;
		}
	}
	//form actions
	//(page-1) hack to store page position after update/delete
	if(!comeFromAction)
		comeFromAction=false;
	CurForm.action = "manager.php?table="+objName+"&nrows="+nrows+"&search=1&order="+order+"&colOrder="+colOrder+"&page="+page+"&comeFromAction="+comeFromAction;
	CurForm.submit();
}

/**
 * @author João Lagarto / Nuno Moreno
 * @description function to handle multiple clauses from the advanced filter
 * @extra Possibly this function can be used with multiple insert (don't know yet how i am going to implement it)
 *  
 */

//counter initializer -> variable created for control purposes
var flag = 1; //variable that counts the number of rows to submit through the advanced filter
function checknew(type, id){
	if(type == 'sum'){	
		//do not allow to add clauses if some are left blank
		var i = document.getElementById("multiple").value;
		if(flag > 1){ //more than 1 search clause
			if(document.getElementById("val__"+i).value=='') return;
			if(document.getElementById("field__"+i).value=='') return;
			if(document.getElementById("operator__"+i).value=='') return;
		} else {
			if(document.getElementById("val").value=='') return;
			if(document.getElementById("field").value=='') return;
			if(document.getElementById("operator").value=='') return;
		}
	
		var val = document.getElementById('multiple').value++;
		flag++;
		cloneMe(id, val);
	} else {
		flag--;
		deleteMe(id);
	}	
	//do not allow to change table if we have more than 1 clause
	if(flag==1) document.getElementById("table").disabled = false;
	else document.getElementById("table").disabled = true;
}

function serialize( mixed_value ) {
    var _getType = function( inp ) {
        var type = typeof inp, match;
        var key;
        if (type == 'object' && !inp) {
            return 'null';
        }
        if (type == "object") {
            if (!inp.constructor) {
                return 'object';
            }
            var cons = inp.constructor.toString();
            match = cons.match(/(\w+)\(/);
            if (match) {
                cons = match[1].toLowerCase();
            }
            var types = ["boolean", "number", "string", "array"];
            for (key in types) {
                if (cons == types[key]) {
                    type = types[key];
                    break;
                }
            }
        }
        return type;
    };
    var type = _getType(mixed_value);
    var val, ktype = '';
    
    switch (type) {
        case "function": 
            val = ""; 
            break;
        case "undefined":
            val = "N";
            break;
        case "boolean":
            val = "b:" + (mixed_value ? "1" : "0");
            break;
        case "number":
            val = (Math.round(mixed_value) == mixed_value ? "i" : "d") + ":" + mixed_value;
            break;
        case "string":
            val = "s:" + mixed_value.length + ":\"" + mixed_value + "\"";
            break;
        case "array":
        case "object":
            val = "a";
            var count = 0;
            var vals = "";
            var okey;
            var key;
            for (key in mixed_value) {
                ktype = _getType(mixed_value[key]);
                if (ktype == "function") { 
                    continue; 
                }
                
                okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key);
                vals += serialize(okey) +
                        serialize(mixed_value[key]);
                count++;
            }
            val += ":" + count + ":{" + vals + "}";
            break;
    }
    if (type != "object" && type != "array") {
      val += ";";
  }
    return val;
}

/**
 * @author João Lagarto / Nuno Moreno
 * @description function to submit the advanced filter
 * @extra Possibly can be used in both admin.php and manager.php
 */

function filterSubmit(form){
	//using jquery now
	//target table
	var objName=$("#table").val();//var objName = document.getElementById("table").value;
	//form from which the filter was applied
	var CurForm = eval("document."+form);
	//each clause from the filter has 3 elements: field, operator and value
	//sent these elements into an array
	var arrRows = new Array(CurForm.length/3);
	//Number of clauses = Form length divided by 3 elements 
	var ctrl=0; //initialize control variable
	var jsonArr = []; //initialize array to write the filter fields
	var jsonMaster = []; //initialize array to write the filter data (from fields)
	var att=""; //initialize variable to handle each element
	//loop through all filter elements (fields)
	for(var i=0; i<CurForm.length;i++){
		if(CurForm[i].value==''){
	    	alert("Enter all parameters to submit filter!");
	    	return;
	    }
		//check clause operator
		if(ctrl==1 && CurForm[i].value==4){
			att=CurForm[i-1].value;
		}
		if (att!="" && ctrl==2){
			url="ajaxFilter.php?val=" + CurForm[i].value + "&table=" + objName + "&att="+att;
			var str = ajaxRequest(url);
			CurForm[i].value = str;
		}
		//write field id and value into a json object
		jsonArr.push({
			id:CurForm[i].id, //field id
			value:CurForm[i].value //field value
		});
		ctrl++; //increment control variable
		if(ctrl==3){ //each filter row has 3 fields(field, operator and value). When it reaches 3: write row into an array
			jsonMaster.push(jsonArr); //write row into a general array
			jsonArr=[]; //clear array to restart the loop
			ctrl=0;	//reset control variable to reinitialize all process
			att="";
		}
	}

	CurForm.action = "manager.php?table="+objName+"&nrows=20&no=" + CurForm.length + "&filter=true&search=2&arr="+serialize(jsonMaster);
	CurForm.submit();
	
}

/**
 * @author João Lagarto / Nuno Moreno
 * @description fmethod to handle page navigation with filter
 */
//
function submit(search, objName, nrows, order, colOrder, page){
	if(search == ''){
		onclick=window.open("manager.php?table="+objName+"&nrows="+nrows+"&order="+order+"&colOrder="+colOrder+"&page="+page,'_self');
	}
	if(search == 1){ //regular filter search
		//get form
		var CurForm = eval("document.table");
		for(var i=0;i<CurForm.length;i++){
			if(CurForm[i].lang=='__fk'){
				var newId=CurForm[i].id;
				url="ajax.php?val=" + CurForm[i].value + "&var=" + newId.substring(0,newId.length-3);
				//url="ajax.php?val=" + CurForm[i].value + "&var=" + CurForm[i].id;
			    var str = ajaxRequest(url);
			    CurForm[i].value = str;	
			}
		}
		CurForm.action = "manager.php?table="+objName+"&nrows="+nrows+"&order="+order+"&colOrder="+colOrder+"&page="+page+"&search=1";
		CurForm.submit();
	} 
	if(search == 2){ //advanced filter search
		//using jquery now
		//form from which the filter was applied
		var CurForm = eval("document.advFilter");
		//each clause from the filter has 3 elements: field, operator and value
		//sent these elements into an array
		var arrRows = new Array(CurForm.length/3);
		//Number of clauses = Form length divided by 3 elements 
		var ctrl=0; //initialize control variable
		var jsonArr = []; //initialize array to write the filter fields
		var jsonMaster = []; //initialize array to write the filter data (from fields)
		var att=""; //initialize variable to handle each element
		//loop through all filter elements (fields)
		for(var i=0; i<CurForm.length;i++){
			if(CurForm[i].value==''){
		    	alert("Enter all parameters to submit filter!");
		    	return;
		    }
			//check clause operator
			if(ctrl==1 && CurForm[i].value==4){
				att=CurForm[i-1].value;
			}
			if (att!="" && ctrl==2){
				url="ajaxFilter.php?val=" + CurForm[i].value + "&table=" + objName + "&att="+att;
				var str = ajaxRequest(url);
				CurForm[i].value = str;
			}
			//write field id and value into a json object
			jsonArr.push({
				id:CurForm[i].id, //field id
				value:CurForm[i].value //field value
			});
			ctrl++; //increment control variable
			if(ctrl==3){ //each filter row has 3 fields(field, operator and value). When it reaches 3: write row into an array
				jsonMaster.push(jsonArr); //write row into a general array
				jsonArr=[]; //clear array to restart the loop
				ctrl=0;	//reset control variable to reinitialize all process
				att="";
			}
		}
		
		CurForm.action = "manager.php?table="+objName+"&nrows="+nrows+"&order="+order+"&colOrder="+colOrder+"&page="+page+"&no=" + CurForm.length + "&filter=true&search=2&arr="+serialize(jsonMaster);
		CurForm.submit();
	}
	
}

function qSubmit(objName,i){
	var CurForm = eval("document.qsearch"+i);
	var nrows = document.getElementById("qsearchNrows_"+i).value;
	var qSearchValue=$("#qsearch"+objName).val();
	if(qSearchValue==""){
		alert("Empty search string");
		return;
	} else {
		if(qSearchValue.length<3){
			alert("Search string must have more than 2 characters");
			return;
		}	
	}
	CurForm.action = "manager.php?table="+objName+"&nrows="+nrows+"&search=3";
	CurForm.submit();
	
}

