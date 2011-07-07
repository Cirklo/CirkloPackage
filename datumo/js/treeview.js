function dispTree(id,table1, table2, conn, val, page, type, bool, tree){
	var url = "ajaxTree.php?id="+id+"&table1="+table1+"&table2="+table2+"&conn="+conn+"&val="+val+"&page="+page+"&type="+type+"&tree="+tree;
	var str = ajaxRequest(url);
	$("#"+id).html(str);
	//open and closes div
	if(!bool) {
		if($("#"+id).css("display")=="none"){
			$("#"+id).slideDown(function(){
				$("div.c").not("#"+id).slideUp();
			});
		} else {
			$("#"+id).slideUp();
		}
		$("#details").html("No items selected");
	}
	if(type==2){
		$("div.detailsTree").fadeIn(1000);
	}
}

function checkit(id,objName,val,bool,treeview){
	$("input[type=checkbox]").each(function(){
		$(this).attr("checked",true);	
	});
	dispInputTree(id,objName,val,bool,treeview);
}


function dispInputTree(id,objName,val,bool,tree){
	//initialize array to store checked boxes ID
	var arr=new Array;
	$("input[type=checkbox]:checked").each(function(){
		//id's to be updated
		arr.push(this.id);
	});
	if(arr.length>0){
		var url = "ajaxTree.php?disp&conn="+objName+"&val="+val+"&tree="+tree+"&list="+arr;
		var str = ajaxRequest(url);
		$("#"+id).html(str);
	} else {
		$("#"+id).html("No items selected");
	}
	
}

function treeshow(id){
	var obj = document.getElementById(id);
	var allobj = document.getElementsByTagName("div");
	if(obj.style.display == "block")
		obj.style.display = "none";
	else{
		obj.style.display = "block";
	}
	
}

function actionTree(action,val,objName,tree){
	//initialize form
	var CurForm=eval("document.CurForm_"+objName);
	//initialize array to store checked boxes ID
	var arr=new Array;
	$("input[type=checkbox]:checked").each(function(){
		//id's to be updated
		arr.push(this.id);
	});
	if(arr.length>0){
		var resp=confirm("You are about to make changes in the database. Proceed?");
		if(!resp) return;
		if(action!="delete"){
			//loop through all form inputs (all attributes of the table)
			for(var i=0;i<CurForm.length;i++){
				//Is it a foreign key
				if(CurForm[i].lang=="__fk"){
					if(CurForm[i].alt=="" && CurForm[i].value!=""){
						url="ajax.php?val=" + CurForm[i].value + "&var=" + CurForm[i].id;
						var str = ajaxRequest(url);
						CurForm[i].value = str;	
						//if foreign key is null
						if(CurForm[i].value == ""){
						    CurForm[i].focus();
						    alert("Field cannot be null! Please use the autocomplete tool");
						    return;
						}
					} else {
						CurForm[i].value=CurForm[i].alt;
					}	
				}
			}
		}
	} else {
		alert("No entries selected!");
	}
	url = "treeview.php?tree="+tree+"&action="+action+"&arr="+arr+"&val="+val;
	//alert(url);
	CurForm.action = url;
	CurForm.submit();
}
	
