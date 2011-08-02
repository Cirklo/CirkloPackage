$(document).ready(function(){
	jQuery.fn.createGrid = function(options){
		//set defaults
		var defaults = {
			display: "External",
			state: "Active",
			height:"100%",
			width:700,
			id:"",
			caption:"",
			extra_op:"",
			extra_fields:""
		};
		var options = jQuery.extend({}, defaults, options);
		var report_id=options.report_id;
		var extra="&extra_op="+options.extra_op+"&extra_fields="+options.extra_fields;
		$.ajax(
		    {
		       type: "POST",
		       url: "report_properties.php?report_id="+report_id,
		       data: "",
		       dataType: "json",
		       success: function(result)
		       {
		            colN = result.colNames;
		            colM = result.colModel;		       
		            $("#list").jqGrid({
		                url:"report_data.php?report_id="+report_id+extra,	//URL to get data
		                datatype: 'json', 					//I want JSON 
		                mtype: 'GET',						//type to send query navigation variables
		                colModel: colM,						//attribute properties
		                colNames: colN,						//column names
		                pager: '#pager',					//navigation bar div id
		                rowNum:10,							//default number of rows per page	
		                rowList:[10,20,50,100,200],					//number of rows per page selector
		                multiselect: false,					//add checkboxes to each row of the table
		                sortname: "",						//attribute order
		                sortorder: 'desc',					//attribute order (ASC or DESC)
		                viewrecords: true,					
		                caption: options.caption,			//table title
		                height: options.height,				//default height
		                autowidth:true,						//default width->auto width (fit to window)	
		                grouping: false, 
		                groupingView : { 
		                	groupField : ['']} 
		               // toolbar: [true,"bottom"],			//set bottom toolbar 
		              });  
		            
		            	var viewOptions={
		            			width: 400
		            	};
		              //jQuery("#list").jqGrid('gridResize',{minWidth:600,maxWidth:1000,minHeight:250, maxHeight:500});
		              jQuery("#list").navGrid('#pager',
		                      {view:true, pdf:true, add: false, edit: false, del: false}, //options
		                      {}, 
		                      {}, // add options
		                      {}, 
		                      {multipleSearch:true}, // search options
		                      viewOptions
		              );
		              // add custom button to export the data to excel
		              jQuery("#list").jqGrid('navButtonAdd','#pager',{
		                     caption:"Excel", 
		                     title: "Export filtered results",
		                     onClickButton: function () {
		                    	 exportExcel("#list");
		                     }
		              });
		              // add custom button to export the data to excel
		              jQuery("#list").jqGrid('navButtonAdd','#pager',{
		                     caption:"Export all", 
		                     title: "Export all data to Excel",
		                     onClickButton: function () {
		                    	 allExcel("#list",report_id,extra);
		                     }
		              });
		              // add custom button to print data
		              jQuery("#list").jqGrid('navButtonAdd','#pager',{
		                     caption:"Print", 
		                     onClickButton: function () {
		                    	 $("#list").printElement({
		                    		 pageTitle: "Report"
		                    	 });
		                     }
		              });
		              jQuery("#grouping").change(function(){
		            	  var vl = $(this).val(); 
		            	  if(vl) { 
		            		  if(vl == "clear") { 
		            			  jQuery("#list").jqGrid('groupingRemove',true); 
		            		  } else { 
		            			  jQuery("#list").jqGrid('groupingGroupBy',vl); 
		            		  } 
		            	  } 
		              });
		       },
		       error: function(x, e)
		       {
		            alert(x.readyState + " "+ x.status +" "+ e.msg);   
		       }
		    });
		    setTimeout(function() {$("#list").jqGrid('setGridParam',{datatype:'json'}); },500);
		    //method that reload the table
		    function refreshGrid(xmlHttpResponse) {
                $("#list").trigger("reloadGrid");
            }
	};
	
	$("#nextFields").click(function(){
		var i=0; //initialize counter
		var objName=new Array;
		$("input[type=checkbox], .tables").each(function(){
			//check its value
			if($(this).attr("checked")){
				objName.push(this.id);
				i++; //increment number of checked checkboxes
			}
			$(this).attr("disabled","disabled"); //disable checkboxes
		});
		if(i==0){
			alert("You must select at least one table to proceed!");
			return;
		} else {
			var url="ajaxReport.php?type=0";
			$.get(url,{},
			function(data){
				$("#nextFields").css("display","none");
				$("#fields").html(data);
				var url="ajaxReport.php?type=7";
				$.get(url,{
					tables:objName
				}, function(data){
					$("#tInfo").html(data);
					$("#tInfo").css("display","block");
				});
			});
		}
		
	});
	
	$("#nextClauses").click(function(){
		//need to check if the sql query is valid
		var objName=new Array();
		var fields=new Array();
		var masks=new Array();
		$("input[type=checkbox], .tables").each(function(){
			//check its value
			if($(this).attr("checked"))	objName.push(this.id); //increment number of checked checkboxes
		});
		$("input[type=text].field").each(function(){
			//check its value
			fields.push($(this).val());
		});
		$("input[type=text].mask").each(function(){
			//check its value
			masks.push($(this).val());
		});
		var url="ajaxReport.php?type=2";
		$.get(url,{
			tables:objName,
			fields:fields,
			masks:masks
		},
		function(data){
			if(data){
//				alert(data);
				var url="ajaxReport.php?type=3";
				$.get(url,{
					fields:fields
				},
				function(data){
					//alert(data);
					$("#nextClauses").css("display","none");
					$("#nextParameters").css("display","none");
					$("#finishQuery").css("display","none");
					$("#clauses").html(data);
				});
			} else {
				alert("There is an error in your query. Please verify all the fields");
			}
		});
	});
	

	
	$("#finishQuery, #finishQuery2, #finishQuery3").click(function(){
		var resp=confirm("Sure you want to finish this report?");
		if(resp){
			//need to check if the sql query is valid
			var objName=new Array();
			var fields=new Array();
			var masks=new Array();
			$("input[type=checkbox], .tables").each(function(){
				//check its value
				if($(this).attr("checked"))	objName.push(this.id); //increment number of checked checkboxes
			});
			$("input[type=text].field").each(function(){
				//check its value
				fields.push($(this).val());
			});
			$("input[type=text].mask").each(function(){
				//check its value
				masks.push($(this).val());
			});
			var url="ajaxReport.php?type=2";
			$.get(url,{
				tables:objName,
				fields:fields,
				masks:masks
			},
			function(data){
				if(data){
				//	alert(data);
					var url="ajaxReport.php?type=4";
					$.get(url,{},
					function(data){
						//alert(data);
						$("#nextClauses").css("display","none");
						$("#nextParameters,#nextParameters2").css("display","none");
						$("#finishQuery").css("display","none");
						$("#finishQuery2").css("display","none");
						$("#finishQuery3").css("display","none");
						$("#reportInfo").html(data);
					});
				} else {
					alert("There is an error in your query. Please verify all the fields");
				}
			});
		}
	
	});
	
	$("#nextParameters, #nextParameters2").click(function(){
		var resp=confirm("Add input parameters?");
		if(resp){
			//need to check if the sql query is valid
			var objName=new Array();
			var fields=new Array();
			var masks=new Array();
			$("input[type=checkbox], .tables").each(function(){
				//check its value
				if($(this).attr("checked"))	objName.push(this.id); //increment number of checked checkboxes
			});
			$("input[type=text].field").each(function(){
				//check its value
				fields.push($(this).val());
			});
			$("input[type=text].mask").each(function(){
				//check its value
				masks.push($(this).val());
			});
			var url="ajaxReport.php?type=2";
			$.get(url,{
				tables:objName,
				fields:fields,
				masks:masks
			},
			function(data){
				if(data){
					var url="ajaxReport.php?type=6";
					$.get(url,{
						tables:objName
					},
					function(data){
						$("#finishQuery").css("display","none");
						$("#finishQuery2").css("display","none");
						$("#nextParameters").css("display","none");
						$("#nextParameters2").css("display","none");
						$("#inputParameters").html(data);
					});
				} else {
					alert("There is an error in your query. Please verify all the fields");
				}
			});
		}
	
	});
	
	
	$("#createReport").click(function(){
		//fields validation
		if($("#report_name").val()==""){
			alert("You need to enter all fields to proceed");
			this.focus();
			return;
		} else {
			var report_name=$("#report_name").val();
		}
		if($("#report_desc").val()==""){
			alert("You need to enter all fields to proceed");
			this.focus();
			return;
		} else {
			var report_desc=$("#report_desc").val();
		}
		var resp=confirm("Sure you want to create the report?");
		if(resp){
			//need to check if the sql query is valid
			var objName=new Array();
			var fields=new Array();
			var masks=new Array();
			var clauses=new Array();
			var op=new Array();
			var params=new Array();
			//get all selected tables
			$("input[type=checkbox], .tables").each(function(){
				//check its value
				if($(this).attr("checked"))	objName.push(this.id); //increment number of checked checkboxes
			});
			//get all inserted fields
			$("input[type=text].field").each(function(){
				//check its value
				fields.push($(this).val());
			});
			//get all mask that are associated with fields
			$("input[type=text].mask").each(function(){
				//check its value
				masks.push($(this).val());
			});
			//get all query clauses
			$("input[type=text].clause").each(function(){
				//check its value
				clauses.push($(this).val());
			});
			//get all operators
			$("select.op").each(function(){
				//check its value
				op.push($(this).val());
			});
			//get all query parameters
			$("input[type=text].parameters").each(function(){
				//check its value
				params.push($(this).val());
			});
			//get report conf
			var report_conf=$("#report_conf").val();
			var url="ajaxReport.php?type=5&report_name="+report_name+"&report_desc="+report_desc+"&report_conf="+report_conf;
			$.get(url,{
				tables:objName,
				fields:fields,
				masks:masks,
				clauses:clauses,
				op:op, 
				params:params
				}, function (data){
					alert(data);
			});
		}
	});
	
	
	
});

function setParams(report_id){
	var CurForm=eval("document.paramForm");
	var noRows=CurForm.length/2;
	//create an array to store values
	var arr=new Array;
	var op=new Array;
	//loop through all form fields
	for(var i=0;i<CurForm.length;i++){
		var val;
		if(i%2){ //values
			arr.push(CurForm[i].value);
		} else { //operators
			val=CurForm[i].value;
			if(val=="=")	val=0;
			op.push(val);
		}
	}
	$("#list").GridUnload();
	$(document).createGrid({
		report_id: report_id,
		extra_op: op,
		extra_fields:arr
	});
}

var noFields=1;
function multiFields(type, id, multiple_id){
	if(type == 'sum'){	
		if(noFields==10) {
			alert("No more fields allowed!");
			return;
		}
		var val = document.getElementById(multiple_id).value++;
		noFields++;
		cloneMe(id, val,false);
	} else {
		var val = document.getElementById(multiple_id).value--;
		noFields--;
		deleteMe(id);
	}	
}

$("input[lang=__fk]").focus(function(){
	//initialize local array
	var arr=new Array();
	
	//loop through all checkboxes
	$("input[type=checkbox], .tables").each(function(){
		//check its value
		if($(this).attr("checked"))	arr.push(this.id); //increment number of checked checkboxes
	});
	$(this).simpleAutoComplete("ajaxReport.php?type=1&arr="+arr);
});


/*
	cloneFieldset.js
	by Nathan Smith, sonspring.com

	Additional credits:
	> Ara Pehlivanian, arapehlivanian.com
	> Jeremy Keith, adactio.com
	> Jonathan Snook, snook.ca
	> Peter-Paul Koch, quirksmode.org
*/


// insertAfter function, by Jeremy Keith
function insertAfter(newElement, targetElement)
{
	var parent = targetElement.parentNode;
	parent.appendChild(newElement); //add clone fields to the end of the parent table	
}


// Suffix + Counter
var suffix = '__';
var counter = 1;


// Clone nearest parent fieldset
function cloneMe(a, val, origin)
{
	// Increment counter
	counter++;
	// Find nearest parent tr
	var original = a.parentNode;
	while (original.nodeName.toLowerCase() != 'tr')
	{
		original = original.parentNode;
	}
	
	var duplicate = original.cloneNode(true);
	
	// form - Name + ID
	var newForm = duplicate.getElementsByTagName('form');
	for (var i = 0; i < newForm.length; i++)
	{
		var formName = newForm[i].name;
		if (formName)
		{
			oldForm = formName.indexOf(suffix) == -1 ? formName : formName.substring(0, formName.indexOf(suffix));
			newForm[i].name = oldForm + suffix + counter;
			//alert(document.tableman.elements[oldName].value);
			//newSelect[i].value = document.tableman.elements[oldName].value;
		}
		var CurForm = newForm[i];
	}

	// Input - Name + ID
	var newInput = duplicate.getElementsByTagName('input');
	if(!origin){
		for (var i = 0; i < newInput.length; i++)
		{
			var inputName = newInput[i].name;
			if (inputName)
			{
				oldName = inputName.indexOf(suffix) == -1 ? inputName : inputName.substring(0, inputName.indexOf(suffix));
				newInput[i].name = oldName + suffix + counter;
			}
			var inputId = newInput[i].id;
			if (inputId)
			{
				oldId = inputId.indexOf(suffix) == -1 ? inputId : inputId.substring(0, inputId.indexOf(suffix));
				var fk = newInput[i].lang;
				if (fk.indexOf('__fk') != -1){ //Search for external keys
					newInput[i].id = oldId + suffix + counter; //oldId in the old version
					newInput[i].onfocus = function(){
						var arr=new Array();
						//loop through all checkboxes
						$("input[type=checkbox], .tables").each(function(){
							//check its value
							if($(this).attr("checked"))	arr.push(this.id); //increment number of checked checkboxes
						});
						$(this).simpleAutoComplete("ajaxReport.php?type=1&arr="+arr);
					};
				}else{
					newInput[i].id = oldId + suffix + counter;
				}
				 
			}			
		}
	}
	// Select - Name + ID
	var newSelect = duplicate.getElementsByTagName('select');
	for (var i = 0; i < newSelect.length; i++)
	{
		var selectName = newSelect[i].name;
		if (selectName)
		{
			oldName = selectName.indexOf(suffix) == -1 ? selectName : selectName.substring(0, selectName.indexOf(suffix));
			newSelect[i].name = oldName + suffix + counter;
			//alert(document.tableman.elements[oldName].value);
			//newSelect[i].value = document.tableman.elements[oldName].value;
		}
		var selectId = newSelect[i].id;
		if (selectId)
		{	
			oldId = selectId.indexOf(suffix) == -1 ? selectId : selectId.substring(0, selectId.indexOf(suffix));
			newSelect[i].id = oldId + suffix + counter;
		}
	}	
	duplicate.className = 'duplicate';
	insertAfter(duplicate, original);
}


// Delete nearest parent tr
function deleteMe(a)
{
	var duplicate = a.parentNode;
	while (duplicate.nodeName.toLowerCase() != 'tr')
	{
		duplicate = duplicate.parentNode;
	}
	duplicate.parentNode.removeChild(duplicate);
}

function allExcel(grid, report_id, extra){
	//capture column names
	var mya=new Array();
	mya=$(grid).getDataIDs();  // Get All IDs
	var data=$(grid).getRowData(mya[0]);     // Get First row to get the labels
	var colNames=new Array(); 
	var j=0;
	for (var i in data){colNames[j++]=i;}    // capture col names
	
	var url="excel.php?report&report_id="+report_id+"&columns="+colNames+extra;
	document.forms[0].method='POST';
	document.forms[0].action=url;  // send it to server which will open this contents in excel file
	document.forms[0].target='_blank';
	document.forms[0].submit();
}


function exportExcel(grid){

    var mya=new Array();
    mya=$(grid).getDataIDs();  // Get All IDs
    var data=$(grid).getRowData(mya[0]);     // Get First row to get the labels
    var colNames=new Array(); 
    var ii=0;
    for (var i in data){colNames[ii++]=i;}    // capture col names
    var html="";
        for(k=0;k<colNames.length;k++)
        {
        html=html+colNames[k]+"\t";     // output each Column as tab delimited
        }
        html=html+"\n";                    // Output header with end of line
    for(i=0;i<mya.length;i++)
        {
        data=$(grid).getRowData(mya[i]); // get each row
        for(j=0;j<colNames.length;j++)
            {
         html=html+data[colNames[j]]+"\t"; // output each Row as tab delimited
            }
        html=html+"\n";  // output each row with end of line

        }
    html=html+"\n";  // end of line at the end
    document.forms[0].csvBuffer.value=html;
    document.forms[0].method='POST';
    document.forms[0].action='excel.php?oper';  // send it to server which will open this contents in excel file
    document.forms[0].target='_blank';
    document.forms[0].submit();
}
