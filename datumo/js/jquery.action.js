;(function($){
	
	$.action = function (m, o, d){
		return new dbAction(m, o, d);
	};

   	//set plugin defaults
   	var defaults = {
   			url: "ajax_query.php",		//php script to be called through an ajax request
  			action: "",					//default database action 
  			nrows:20,					//number of rows to be displayed
  			order:"ASC",					//ASCending or DESCending order
  			colOrder:"",				//ordering column
  			page:1						//default page number
   	};
    	
   	// override the defaults
	$.action.setup = function (o){
		defaults = $.extend({}, defaults, o) ;
	};
	
	function dbAction(options){
		//override defaults
		options = $.extend({}, defaults, options);
		
		//get target table
		if($.getUrlVars()['table'])		options.objName=$.getUrlVars()['table'];
		if($.getUrlVars()['nrows'])		options.nrows=$.getUrlVars()['nrows'];
		if($.getUrlVars()['order'])		options.order=$.getUrlVars()['order'];
		if($.getUrlVars()['colOrder'])	options.colOrder=$.getUrlVars()['colOrder'];
		if($.getUrlVars()['page'])		options.page=$.getUrlVars()['page'];
		
		//validate table name and action
		if(!options.objName || !options.action){ //table and action are null
			$.jnotify("Unable to perform any action over the database"); 		//display jnotify error
			return;
		}
		
		//in case that everything goes OK
		switch(options.action){
		case "delete":
			$.action.del(options);
			break;
		case "update":
			$.action.upd(options);
			break;
		case "insert":
			$.action.add(options);
			break;
		}
	}
	
	$.action.del=function(options){
		//get ids to delete
		if(!(arr=changes(options))){
			return;
		}
		var resp=confirm("You are about to "+options.action+" "+arr.length+" record(s). Proceed anyway?");
		if(!resp) return;
		cursor_wait();
		//send ajax request. Post variables
		$.post(options.url,{
			objName: options.objName,	//table name
			action: options.action,		//SQL action
			id:arr						//target ids
		},function(data){
			if(data){	$.jnotify(data);return;	}
			//submit filter and keep the current page
			filter("table", options.objName, "", options.order, options.colOrder,options.page,options.action);
		});
	};
	
	$.action.upd=function(options){
		json=[];
		arr=[];
		i=0; k=0;//initialize counter
		//loop through all checkboxes
		$("input[type=checkbox]").not("#cb_all").each(function(){
			var CurForm=eval("document.tableman"+i); //set form for this row
			i++; //increment counter
			if($(this).attr("checked")){ //store all checked boxes
				for(j=0;j<CurForm.length;j++){
					value=CurForm[j].value;
					arr.push(value);
				}
				json.push({"update":arr});
				arr=[]; k++;
			}
		});
		if(k==0){	$.jnotify("No records to "+options.action);return;}
		//give the user a chance to cancel the operation
		var resp=confirm("You are about to "+options.action+" "+k+" record(s). Proceed anyway?");
		if(!resp) return;
		cursor_wait();
		$.post(options.url,{
			objName: options.objName,	//table name
			action: options.action,		//SQL action
			arr:json						//target ids
		},function(data){
			//throw error if changes were not commited
			if(data){	$.jnotify(data); return;}
			//submit filter and keep the current page
			filter("table", options.objName, "", options.order, options.colOrder,options.page,options.action);
		});
	};
	
	$.action.add=function(options){
		//How many forms are there?
		var len=document.forms.length;
//		alert("noForms="+len);
//		alert("noInserts="+noInserts);
		//initialize arrays
		json=[];
		arr=[];
		//loop through all inserts
		for(var i=len-noInserts; i<len;i++){
			//set form name
			var CurForm = document.forms[i];
//			alert("Length:"+CurForm.length);
			for(j=0;j<CurForm.length;j++){
				value=CurForm[j].value;
				arr.push(value);
//				alert(value);
			}
			json.push({"insert":arr});
			arr=[];
		}
		var resp=confirm("You are about to "+options.action+" "+noInserts+" record(s). Proceed anyway?");
		if(!resp) return;
		cursor_wait();

//		alert(JSON.stringify(json));
		$.post(options.url,{
			objName: options.objName,	//table name
			action: options.action,		//SQL action
			arr:json						//target ids
		},function(data){
//			alert(data);
			//throw error if changes were not commited
			if(data){	$.jnotify(data); return;}
			//submit filter and keep the current page
			filter("table", options.objName, "", options.order, options.colOrder,options.page,options.action);

		});
	};
	
	/**
	 * plugin to extract URL variables (key and value)
	 * 
	 * Credits to Will
	 * http://jquery-howto.blogspot.com/2009/09/get-url-parameters-values-with-jquery.html
	 * 
	 */
	
	$.extend({
		  getUrlVars: function(){
		    var vars = [], hash;
		    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		    for(var i = 0; i < hashes.length; i++)
		    {
		      hash = hashes[i].split('=');
		      vars.push(hash[0]);
		      vars[hash[0]] = hash[1];
		    }
		    return vars;
		  },
		  getUrlVar: function(name){
		    return $.getUrlVars()[name];
		  }
		});
	
	/**
	 * Simple method to get all ids from the checked rows from the main table 
	 * 
	 */
	
	function changes(options){
		var arr=new Array;
		i=0; //initialize counter
		//loop through all checkboxes
		$("input[type=checkbox]").not("#cb_all").each(function(){
			var CurForm=eval("document.tableman"+i); //set form for this row
			i++; //increment counter
			if($(this).attr("checked")){ //store all checked boxes
				arr.push(CurForm[0].value);
			}
		});
		//how many rows are going to be deleted?
		if(arr.length==0){
			$.jnotify("No records to "+options.action);	//display alert box if there's not a row to delete
			return false;
		} else {
			return arr;
		}
	}
	
	// Changes the cursor to an hourglass
	function cursor_wait() {
		document.body.style.cursor = 'wait';
	}

	// Returns the cursor to the default pointer
	function cursor_clear() {
		document.body.style.cursor = 'default';
	}
	
})(jQuery);