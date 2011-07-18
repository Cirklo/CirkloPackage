$(document).ready(function(){ 
	
	/**
	 * Method to create grid. 
	 * This grid display all the items of a single basket, depending on the basket state and type 
	 * 
	 */
	
	
		var lastsel2;
		jQuery.fn.createGrid = function(options){
			//set defaults
			var defaults = {
				display: "External",
				state: "Active",
				height:"100%",
				width:1000,
				id:"",
				caption:""
			};
			var options = jQuery.extend({}, defaults, options);
			var dstate = options.state;
			//get user level id
			var user_level = getUserLevel();
			//initialize edit and delete variables
			var edit_var=false;
			var del_var=false;
			//Working with 3 user levels: 0-Administrator; 1-Manager; 2-Regular User
			if(user_level==0){ //am I admin?
				if(dstate=="Received"){ //after being received the basket cannot be changed
					edit_var=false;
					del_var=false;
				} else { //otherwise the basket can be edited or deleted
					edit_var=true;
					del_var=true;
				}
			} else { //am I a manager or a regular user?
				if(dstate=="Active"){ //only allow to modify the basket if it is active
					edit_var=true;
					del_var=true;
				}
			}
			var objName = "request";
			var dtype = options.display;	
			var width=options.width;
			var height=options.height;
			if(options.caption==""){
				var title = dtype+" Requisitions";
			} else {
				var title = options.caption;
			}
			//alert(dtype);
			//alert(options.id);
			$.ajax(
			    {
			       type: "POST",
			       url: "props.php?type="+dtype,
			       data: "",
			       dataType: "json",
			       success: function(result)
			       {
			            colN = result.colNames;
			            colM = result.colModel;
			            colG = result.grouping;
			            if(colG==""){ colGroup=false; } 
			            else{colGroup=true;} 
			            $("#list").jqGrid({
			                url:"data.php?type="+dtype+"&state="+dstate+"&id="+options.id,			//URL to get data
			                datatype: 'json', 					//I want JSON 
			                mtype: 'GET',						//type to send query navigation variables
			                colModel: colM,						//attribute properties
			                colNames: colN,						//column names
			                pager: '#pager',					//navigation bar div id
			                rowNum:10,							//default number of rows per page	
			                rowList:[10,20,50],					//number of rows per page selector
			                multiselect: true,					//add checkboxes to each row of the table
			                sortname: "request_id",				//attribute order
			                sortorder: 'desc',					//attribute order (ASC or DESC)
			                viewrecords: true,					
			                caption: title,						//table title
			                height: height,						//default height
			                width:width,						//default width	
			                toolbar: [true,"both"],				//set top (t_list) and bottom (tb_list) toolbars
			               // toolbar: [true,"bottom"],			//set bottom toolbar 
			                grouping: colGroup, 
			              /*  loadComplete: function(){
			                	$("#t_list").css("text-align","right").html("Totals Amount (EUR): ");
			                },*/
			                groupingView : { 
			                	groupField : [colG], 
			                	groupColumnShow : [false], 
			                	groupText : ['<b>{0}</b>'], 
			                	groupCollapse : false, 
			                	groupOrder: ['asc'], 
			                	groupSummary : [false], 
			                	groupDataSorted : true 
			                }, 
			                onSelectRow: function(id){
			                	/*if(edit_var){
				                    if(id && id!==lastsel2){
				                      jQuery('#list').restoreRow(lastsel2);
				                      jQuery('#list').editRow(id,0,strue);
				                        lastsel2=id;
				                    }
			                	}*/
			                	var arr, total=0;
			        			arr=jQuery("#list").jqGrid('getGridParam','selarrrow');
			        			 for(var i=0;i<arr.length;i++){
			        		        	var data = jQuery("#list").jqGrid('getCell',arr[i],"request_total");
			        		        	total=Number(data)+Number(total);
			        			 }
			        			 $("#tb_list").css("text-align","right").html("Totals Amount (EUR): "+total+"&nbsp;&nbsp;&nbsp;");
			                },
			                editurl:"server.php?table="+objName
			                
			              });  
			              //jQuery("#list").jqGrid('gridResize',{minWidth:600,maxWidth:1000,minHeight:250, maxHeight:500});
			              jQuery("#list").navGrid('#pager',
			                      {pdf:true, add: false, edit: edit_var, del: del_var, search: false}, //options
			                      {	  // edit options
			                    	  width:600,
			                    	  reloadAfterSubmit:true,	//refresh grid after submit changes
			                    	  closeAfterEdit:true,		//close grid after submit changes
			                    	  checkOnSubmit:true,		//check if any change was made
			                    	  viewPagerButtons:false,	//hide page navigator
			                    	  url:"server.php?table="+objName
			                      }, 
			                      {}, // add options
			                      {		//delete options
			                    	  reloadAfterSubmit:true,
			                    	  msg: "You can delete only one item at a time. Proceed?",
			                    	  url:"server.php?table="+objName
			                      }, 
			                      {} // search options
			              );
			             
			              // add custom button to export the data to excel
			              jQuery("#list").jqGrid('navButtonAdd','#pager',{
			                     caption:"Export to Excel", 
			                     onClickButton: function () {
			                    	 exportExcel("#list");
			                     }
			              });
			              // add custom button to print data
			              jQuery("#list").jqGrid('navButtonAdd','#pager',{
			                     caption:"Print", 
			                     onClickButton: function () {
			                    	 $("#list").printElement({
			                    		 pageTitle: title
			                    	 });
			                     }
			              });
			              /*
			               * adds a toolbar button in order to handle basket state progression
			               * 
			               * ***************Validations****************
			               * Is this a regular user? if so, the button cannot be displayed (depends one the db configuration)
			               * Which is the current basket state?
			               * The button can only be displayed if basket was already submitted
			               * Are there key users for each state? If so, instead of creating restrictions according to the user's level
			               * one can restrict through power/key users table
			               * Which are the active states?
			               * In which state should we insert the sap number?
			               ********************************************/
			              if(dstate!="Active"){
			            	  	url="requisitions.php";
					            //ajax request
					  			$.get(url,{
					  				  type:5,
					  				  state:dstate},		
					  				function(data){
					  					//alert(data);
					  					//Check if there is any state available to this user
					  					if(data.length>0){
						  					//check if the answer is one or more states
						  					if(data.indexOf(",")==-1){ //one state only
						  						 $("#t_list").append("<input type='button' class=basket_state value='"+data+"' style='height:20px;font-size:-3'/>"); 
						  					} else { //more than one state
						  						var arr=new Array;
							  					arr=data.split(",");
							  					//loop through all states and display them
							  					for(var i=0;i<arr.length;i++){
							  						$("#t_list").append("<input type='button' class=basket_state value='"+arr[i]+"' style='height:20px;font-size:-3'/>");
							  					}
						  					}
					  					}
					  					$("input","#t_list").click(function(){ 
					  						var resp = confirm("You are about to change this basket state. Proceed?");
					  						if($(this).val()=="Ordered"){
					  							var reqNumber=prompt("Enter the requisition number to proceed (SAP number)");
					  							if(!reqNumber) return;
					  						}
					  						if(resp){
						  						$.get(url,{
									  				  type:6,
									  				  newstate:$(this).val(),
									  				  basket:options.id,
									  				  req:reqNumber},		
									  				  function(data){
									  					  if(data.length==0){ //everything went ok
									  						  //just need to reload the grids in order to display up-to-date info
										  					  $("#list").trigger("reloadGrid");
										  					  $("#list_0").trigger("reloadGrid");
									  					  } else { //everything went wrong
									  						  alert(data);
									  					  }
	
									  					 
									  				  });
					  						}
					  			       	}); 
					  					  
					  			});
			              }
			              	
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
		
		/**
		 * Method to create grid. 
		 * This grid display all the baskets that were already submitted
		 * 
		 */
		
		jQuery.fn.createSubGrid = function(options){
			//set defaults
			var defaults = {
				state: "Submitted"
			};
			var options = jQuery.extend({}, defaults, options);
			var objName = "basket";
			var state = options.state;
			//get user level id
			var user_level = getUserLevel();
			//initialize edit and delete variables
			var edit_var=false;
			var del_var=false;
			//Working with 3 user levels: 0-Administrator; 1-Manager; 2-Regular User
			//if user is a manager or a regular user he will not be allowed to make any changes in the basket list
			if(user_level==0){ //am I admin?
				if(state=="Received" || state=="Ordered"){ //after being received the basket cannot be changed
					edit_var=false;
					del_var=false;
				} else { //otherwise the basket can be edited or deleted
					edit_var=true;
					del_var=true;
				}
			} 
			//alert(dtype);
			$.ajax(
			    {
			       type: "POST",
			       url: "props.php?state="+state,
			       data: "",
			       dataType: "json",
			       success: function(result)
			       {
			            colN = result.colNames;
			            colM = result.colModel;
			            colG = "type_name";
			            if(colG==""){ colGroup=false; } 
			            else{colGroup=true;} 
			            $("#list_0").jqGrid({
			                url:"data.php?state="+state,	//URL to get data
			                datatype: 'json', 					//I want JSON 
			                mtype: 'GET',						//type to send query navigation variables
			                colModel: colM,						//attribute properties
			                colNames: colN,						//column names
			                pager: '#pager_0',					//navigation bar div id
			                rowNum:10,							//default number of rows per page	
			                rowList:[10,20,50],					//number of rows per page selector
			                multiselect: false,					//add checkboxes to each row of the table
			                sortname: "basket_id",				//attribute order
			                sortorder: 'desc',					//attribute order (ASC or DESC)
			                viewrecords: true,					
			                caption: state+" baskets",			//table title
			                height: "100%",						//default height
			                width:1000,							//default width	
			                toolbar: [true,"top"],				//set toolbar
			                grouping: colG, 					//group by type
				            groupingView : { 					//group settings
				                groupField : [colG], 
				                groupColumnShow : [false], 
				                groupText : ['<b>{0}</b>'], 
				                groupCollapse : false, 
				                groupOrder: ['desc'], 
				                groupSummary : [false], 
				                groupDataSorted : true 
				            }, //set toolbar 
				            //display basket details on row select
			                onSelectRow: function(ids) {		//call secondary grid on row select (selected row details)
			                	$("#list").GridUnload();
			                	var data = jQuery("#list_0").jqGrid('getCell',ids,"type_name");
			                	$(document).createGrid({
			                		id:ids,
			                		display:data,
			                		state: state,
			                		caption: "ID: "+ids+" - "+jQuery("#list_0").jqGrid('getCell',ids,"department_name")+" ("+ jQuery("#list_0").jqGrid('getCell',ids,"account_number")+")"
			                	});
			                	
			                }
			              });  
			              jQuery("#list_0").navGrid('#pager_0',
			                      {add: false, edit: edit_var, del: del_var, search: false}, //options
			                      {width:600,reloadAfterSubmit:true,url:"server.php?table="+objName}, // edit options
			                      {}, // add options
			                      {reloadAfterSubmit:true,url:"server.php?table="+objName}, // del options
			                      {} // search options
			              );
			           // add custom button to export the data to excel
			              jQuery("#list_0").jqGrid('navButtonAdd','#pager_0',{
			                     caption:"Export to Excel", 
			                     onClickButton: function () {
			                    	 exportExcel("#list_0");
			                     }
			              });
			       },
			       error: function(x, e)
			       {
			            alert(x.readyState + " "+ x.status +" "+ e.msg);   
			       }
			    });
			    setTimeout(function() {$("#list_0").jqGrid('setGridParam',{datatype:'json'}); },500);
			    //method that reload the table
			    function refreshGrid(xmlHttpResponse) {
	                $("#list_0").trigger("reloadGrid");
	            }
		};
		
		/**
		 * Method to add items to the active basket
		 * this method dynamically add items to a basket. There is a single active basket per user/department
		 * 
		 */
		
		jQuery.fn.addToCart = function(options){
			//setting default options
			var defaults = {
					objName: "product"
			};
			
			var options = jQuery.extend({}, defaults, options);
			//setting variables
			var CurForm=eval("document.tableman"+options.row);
			var arr=new Array();
			arr[0]=CurForm[0].value;
			arr[1]=Number($("#quantity_"+options.row).val());
		//	alert(arr);
			//ajax request->send entry id through ajax and add it to basket
			var url = "requisitions/requisitions.php";
			//ajax request with post variables (NICE)
			$.get(url,{
				  type:0,
				  table:options.objName,
				  item:arr},		
			//retrieve that from ajax request 
			//select another div to display the notification
				function(data){
					$.jnotify(data);
				});
		};
		
		/**
		 * Method to submit basket
		 * this method submits all selected items to a basket
		 * 
		 */
		
		$("#submit").click(function(){
			//Is there a matching column?
			var url = "requisitions.php";
			var arr, key, match, total=0;
			//ajax request
			$.get(url,{
				  type:1,
				  stype:$("#submit").attr("name")},		
			//retrieve that from ajax request 
			//select another div to display the notification
				function(data){
					var key=data;
					//code to get checkboxes' id
			        arr=jQuery("#list").jqGrid('getGridParam','selarrrow');
			        //selector validation
			        if(arr.length==0){
			        	alert("You must choose at least one item");
			        	return;
			        }
			        
			        /* if the database does not have a matching key
			         * 
			         */
			        
			        if(key!=""){
			        //loop through all selected values
				        for(var i=0;i<arr.length;i++){
				        	var data = jQuery("#list").jqGrid('getCell',arr[i],key);
				        	var calc = jQuery("#list").jqGrid('getCell',arr[i],"request_total");
        		        	total=Number(total)+Number(calc);
				        	if(i==0) match=data;
				        	else{
				        		if(data!=match) {
				        			alert("You can only order items from the same vendor");
				        			return;
				        		}
				        	}
				        }
				        
			        }
			           
			        
			        //check if an account has been selected
			        if($("#accountList").get(0).selectedIndex==0){
			        	alert("You must select a valid account to proceed");
			        	return;
			        } else { //submit basket
			        	var iComments=$("#iComments").val();
			        	//must check if the selected account has enough money.
			        	$.get(url,{
							  type:3,
							  stype:$("#submit").attr("name"),
							  account:$("#accountList").val(),
							  val:arr,
							  ammount:total,
							  iComments:iComments},
							  //retrieve that from ajax request 
							//select another div to display the notification
							function(data){
								  alert(data);
								  $("#list").trigger("reloadGrid"); 
							  });
			        }
				});
		});
			
		$("#accountList").change(function(){
			var index=$(this).val();
			var url = "requisitions.php";
			$.get(url,{
				  type:2,
				  id:index},		
				//retrieve that from ajax request 
				//select another div to display the notification
				function(data){
					  //alert(data);
					  $("#accountDetails").html(data);
				  });
		});
		
		$("#totals").click(function(){
			var arr, total=0;
			arr=jQuery("#list").jqGrid('getGridParam','selarrrow');
			 for(var i=0;i<arr.length;i++){
		        	var data = jQuery("#list").jqGrid('getCell',arr[i],"request_total");
		        	total=Number(data)+Number(total);
			 }
			 alert(total);
		});
		
		$("#vendorSubmit").click(function(){
			//initialize control variable
			var ctrl=true;
			$("form[name=newVendor]").find("input[lang=yes]").each(function(){
				if(this.value=="")	{
					alert("Fields missing!");
					ctrl=false;
					return false;
				}
			});
			//bank validation
			if($("#nib").val()==""){
				$("form[name=newVendor]").find("input[lang=int]").each(function(){
					if(this.value=="")	{
						alert("You must enter the payment information");
						ctrl=false;
						return false;
					}
				});
			}
			//everything's OK
			if(ctrl){
				var CurForm=eval("document.newVendor");
				url="requisitions.php?type=8";
				CurForm.action=url;
				CurForm.submit();
			}
		});
		
		
		
}); 

function center(object)
{
 object.style.marginLeft = "-" + parseInt(object.offsetWidth / 2) + "px";
 object.style.marginTop = "-" + parseInt(object.offsetHeight / 2) + "px";
}

function updateBasket(basket_id){
	//Update basket delivery
	
	//display div to access user credentials
	$("#basket_number").val(basket_id);
	$("#igc_user").css("display","block");
	//get div ID
	var igc_user=document.getElementById("igc_user");
	//center div on screen
	center(igc_user);
}

function validate(){
	var url="deliver.php?auth";
	$.get(url,{
		basket_id:$("#basket_number").val(),
		username:$("#igc_email").val(),
		pass:$("#igc_pass").val()},
	function(data){
		alert(data);
		location.reload(true);
		//setTimeout("location.reload(true)",1000);
		
	});
}


function getUserLevel(){
	url="requisitions.php?type=7";
	var str=ajaxRequest(url);
	return str;
}

function exportExcel(grid)
{
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
    document.forms[0].action='../excel.php?oper';  // send it to server which will open this contents in excel file
    document.forms[0].target='_blank';
    document.forms[0].submit();
}