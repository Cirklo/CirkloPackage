





/****************************************************************************************/



function relations (id){
	//clear divs
	document.getElementById('dispFields').innerHTML="";
	document.getElementById('dispFeatures').innerHTML="";
	var val = document.getElementById(id).value;
	if(document.getElementById(id).selectedIndex!=0){
		url = "ajaxRelations.php?table="+val;
		var str = ajaxRequest(url);
		document.getElementById('displayRelations').innerHTML = str;
	} else  {
		document.getElementById('displayRelations').innerHTML="";
	}
}

function displayFields(){
	document.getElementById('multiple').value=0;
	var CurForm = eval("document.list");
	//store tables in this array
	var arr=new Array();
	arr[0] = document.getElementById('table').value;
	for(var i=0;i<(CurForm.length-1);i++){
		if(CurForm[i].checked) {
			//calculate the number of selected tables
			document.getElementById('multiple').value++;
			arr.push(CurForm[i].id);
		}
	}
	if(document.getElementById('multiple').value>1){
		alert("You can only choose one additional table, for now!");
		return;
	}
	var table="";
	for(var j=0;j<arr.length;j++){
		table+="&table"+j+"="+arr[j];
		
	}
	url = "ajaxFields.php?type=0"+table;
	var str = ajaxRequest(url);
	document.getElementById("dispFields").innerHTML = str;
}

//method using jQuery
function buildQuery(){
	//initialize counter
	$("#multipleFields").val(0);
	var add=Number($("#multipleFields").val());
	//get forms
	var CurForm = eval("document.fieldDisp");
	var ObjForm = eval("document.list");
	var subForm = eval("document.submitForm");
	//initialize query string
	var count="";
	var sql="";
	var tables="";
	var where="";
	var limit="";
	//store keys in this array
	var keys=new Array();
	//store tables in this array
	var arr=new Array();
	var order=new Array();
	var group=new Array();
	arr[0] = $("#table").attr("value");
	//CREATE CLAUSES FOR EACH ONE OF THE CHECKBOXES (COUNT, ORDER AND GROUP)
	for(var i=0;i<(CurForm.length-1);i++){
		var str = CurForm[i].id;
		if(CurForm[i].checked) {
			switch(str.substring(0,6)){
			case "order_":
				if($("#"+str.substring(6,str.length)).attr("checked")){
					order.push(str.substring(6,str.length));
				}
				break;
			case "group_":
				if($("#"+str.substring(6,str.length)).attr("checked")){
					group.push(str.substring(6,str.length));
				}
				break;
			default:
				add=add+1;
				//calculate the number of selected tables
				sql+=CurForm[i].id+",";
				break;
			}
			
		}
		if(str.substring(0,6)=="value_"){
			if(CurForm[i].value!=""){
				where+=str.substring(6,str.length)+"=\""+CurForm[i].value+"\" AND ";
			}
		}
		
	}
	where=where.substring(0, where.length-4);
	for(var j=0;j<(ObjForm.length-1);j++){
		if(ObjForm[j].checked) {
			arr.push(ObjForm[j].id);
			keys.push(ObjForm[j].lang);
			if(where=="")
				where+=arr[0]+"_id="+ObjForm[j].lang;
			else
				where+=" AND "+arr[0]+"_id="+ObjForm[j].lang;
		}
	}
	if(add==0){
		alert("You must select at least one field to proceed!");
		return;
	}
	//BUILD THE QUERY
	sql=sql.substring(0,sql.length-1);
	tables=arr;
	if($("#distinct").attr("checked")) $("#queryClauses").val("DISTINCT");
	//fill hidden textboxes in order to build the query
	$("#queryFields").val(sql);
	$("#queryTables").val(tables);
	$("#queryWhere").val(where);
	$("#queryLimit").val($("#nrows").val());
	$("#queryOrder").val(order);
	$("#queryGroup").val(group);
	subForm.action = "report.php";
	subForm.target = "_blank";
	subForm.submit();
	window.close();
}

function submitReport(page){
	var subForm = eval("document.submitForm");
	subForm.action = "report.php?page="+page;
	subForm.submit();
}


function saveReport(user_id){
	var resp = confirm("Sure you want to save this report?");
	if(resp){
		var reportName="";
		var reportDescription="";
		reportName = prompter(reportName, 20, "Report name");
		reportDescription = prompter(reportDescription, 100, "Report description");
		var conf = confirm("Do you want to make this report available to other users? \n\n1. Press OK to make this report public\n2. Press CANCEL to make it private");
		if(conf){ conf=1; } //Go public
		else { conf=2; } //Go private
		url = "ajaxReport.php?user_id="+user_id+"&name="+reportName+"&desc="+reportDescription+"&conf="+conf;
		var str = ajaxRequest(url);
		alert(str);
	}
}

function prompter(id, noChars, tag){
	while(id==""){
		id = prompt(tag+": (Max. "+noChars+" characters)");
		if(id.length>noChars) {
			alert("You have exceeded the number of characters allowed");
			id="";
		}
	}
	return id;
}
