$(document).ready(function(){
	/*
	 * method to create mating dynamically
	 * After mating it creates a new cage
	 */
	
	$("#mating").click(function(){
		//alert("flag");
		var counter=0,i=0; //initialize counter
		var arr=new Array;
		//loop through all checkboxes
		$("input[type=checkbox]").not("#cb_all").each(function(){
			var CurForm=eval("document.tableman"+i); //set form for this row
			i++; //increment counter
			if($(this).attr("checked")){ //store all checked boxes
				arr.push(CurForm[0].value);
			}
		});
		//if number of animals is out of range for mating
		if(arr.length<2){
			alert("You need to select at least two animals to proceed!");
			return;
		}
		if(arr.length>3){
			alert("You cannot select more than three animals!");
			return;
		} 
		//alert(arr);
		//ajax request
		$.get("animalhouse/bioterio.php?type=0",{ids:arr},
				function(data){
				  	//return the data
					alert(data);
			  });
		//uncheck all entries
		$("input[type=checkbox]").not("#cb_all").each(function(){
			$(this).attr("checked",false);
		});
		//immediatelly refresh page
		setTimeout("location.reload()",200);
	});
	
	/*
	 * Method to perform an automatic weaning 
	 * TODO 
	 * Check if the litters come from different cages
	 * Check if chosen litters are from CB or Br
	 * Sum all females and all males and put them in 2 different cages.
	 * Create animals
	 */
	
	$("#weaning").click(function(){
		//alert("flag");
		var counter=0,i=0; //initialize counter
		var arr=new Array;
		//loop through all checkboxes
		$("input[type=checkbox]").not("#cb_all").each(function(){
			var CurForm=eval("document.tableman"+i); //set form for this row
			i++; //increment counter
			if($(this).attr("checked")){ //store all checked boxes
				arr.push(CurForm[0].value); //get selected litter IDs
			}
		});
		//ajax request
		$.get("animalhouse/bioterio.php?type=1",{ids:arr},
			  function(data){
				  //return the data
				  alert(data);
			  });
	});
	
	$("#animalSubmit").click(function(){
		var CurForm=eval("document.animal_req");
		//Loop through all form elements
		for(var i=0;i<CurForm.length;i++){
			if(CurForm[i].lang=="yes" && CurForm[i].value==""){
				alert("You must enter all obligatory fields to proceed");
				return;
			}
		}
		
		CurForm.action="bioterio.php?type=2";
		var resp=confirm("Sure you want to submit this requisition?");
		if(resp){
			try{
				CurForm.submit();
				alert("Requisition successfully requested");
				//window.location="admin.php";
			} catch(err){
				alert("Unable to submit requisition");
				return;
			}
		}
		
	});
	
	$("#schedule").click(function(){
		var counter=0,i=0; //initialize counter
		var arr=new Array;
		//loop through all checkboxes
		$("input[type=checkbox]").not("#cb_all").each(function(){
			var CurForm=eval("document.tableman"+i); //set form for this row
			i++; //increment counter
			if($(this).attr("checked")){ //store all checked boxes
				arr.push(CurForm[0].value); //get selected litter IDs
			}
		});
		//get current url variables
		var multi=getMulti();
		//set variable to pass ids through http/url (gets)
		if(multi!=""){
			var conf=confirm("You have some stored values from previous pages. Do you want to use these?");
			if(conf)
				arr.push(multi);
		}
		alert(arr);
		return;
	
		//ajax request
		$.get("bioterio.php?type=3",{
			ids:arr,
			action:"Mating"},
			function(data){
				//return the data
				alert(data);
				//loop through all checkboxes
				$("input[type=checkbox]").not("#cb_all").each(function(){
					$(this).attr("checked",false);
				});
			});
		
	});
	
});


