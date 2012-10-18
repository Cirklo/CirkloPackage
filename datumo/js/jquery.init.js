$(document).ready(function() {
	
	/**
	 * @author João Lagarto
	 * Method to handle logins
	 */
	/*
	//initialize notification plugin
	$("#alertDiv").startAlert({});	
	*/
	/**
	 * @abstract TipTip plugin initialization
	 * @author Drew wilson
	 * This plugin shows a fancy tag (title) on hover 
	 */
	
	$("*").tipTip();
	
	/**
	 * @abstract Method to handle datumo login
	 * @author João Lagarto
	 */
	
	$("#login").click(function(){
		//check if any of the fields is empty		
		if($("#user_login").val()=="" || $("#user_passwd").val()==""){
			$.jnotify("Missing fields");
		} else {
			//send the ajax request
			$.post("../datumo/session.php?login",{login:$("#user_login").val(),
							  pass:$('#user_passwd').val()},
							  function(data){
								  //return the data
								  if(data.length != 0){
									  // $.jnotify("Wrong login");
									  $.jnotify("->" + data + "<-");
								  } else {
									  window.location = "../datumo/index.php";
								  }
							  });
		}	
	});
	
	/**
	 * @abstract Method to handle password recovery issues
	 * 
	 */
	
	jQuery.fn.recoverPwd = function(){
		var mail = prompt("Your email:");
		var login= prompt("Your username:");
		if(mail){
			$.get("../datumo/session.php?pwd",{email:mail,
									 user:login},
								function(data){
									if(data.length!=0){
										msg=data;
									} else {
										msg="Password updated. Check your email";
									}
									 $.jnotify(msg);
								});
		}
	};
	
	/**
	 * @abstract Method to dinamically send an email
	 */
	
	jQuery.fn.submitBug = function(options){
		//set defaults
		var defaults = {
			form: "cform"
		};
		var options = jQuery.extend({}, defaults, options);
		//call contact form
		var CurForm=eval("document."+options.form);
		var contactType=$("#contactType").val();
		if(contactType==0){
			alert("Choose a contact type");
			return;
		}
		for(var i=0;i<CurForm.length;i++){
			if(CurForm[i].value==""){
				CurForm[i].focus();
				$.jnotify("Missing fields");
				return;
			}
		}
		var url = "ajaxMail.php?type=1";
		//ajax request with post variables (NICE)
		$.post(url,{name:$('#name').val(),
			  target:$("#contactType").val(),
			  email:$('#email').val(),
			  message:$('#message').val()},
		
		//retrieve that from ajax request
		function(data){
			$.jnotify(data);
		});
		//call method to clean form
		cleanForm(options.form);
	};

	
	
	
	/**
	 * @author João Lagarto
	 * @abstract Method called on anchor/button click event. Acts like a show/hide method. Only one div can be visible
	 */
	
	$("table").find("div:not(div[lang=exp])").hide().end().find("a:not(.exp),input:button").click(function() {
		$(this).next().slideToggle(200,function(){
			$("div").not("#"+this.id+",div[lang=tiptip], div[lang=exp], .alertClass").slideUp(200);
		});
	});
	
	
	/**
	 * @author João Lagarto / Nuno Moreno
	 * @description method to clean all textfield inputs
	 */
	
	function cleanForm(form){
		$("form[name="+form+"]").find(":input").each(function(){
			switch(this.type){
				case "text":
				case "textarea":
					$(this).val("");
					break;
			}	
		});
	}
	
	/**
	 * @author João Lagarto
	 * @description method to display the information div on key press
	 */
	
	//
	$(document).keypress(function(event){
		if(event.which==43){
			$("div[class=info]").slideToggle();
		}
	});
	
	/**
	 * AutoSuggest Plugin
	 * 
	 * Input must have lang=__fk in order to work correctly
	 */
	
	$("input[lang=__fk]").focus(function(){
		$(this).autocomplete({
			source:"autoSuggest.php?field="+this.id,
			minLength:1,
			dataType:"json"
		});
	});
	
	$("input").focus(function(){
		this.select();
	});
	
	
	//foreign key insert form
	$("input[lang=__fk]").keypress(function(e){
		if(e.which==13 && e.ctrlKey) {
			e.preventDefault();
			//check field id 
			$.get("functions.php?type=3",{
				field:this.id
			}, function (data){
				if(data.length>1)
					window.open("insert.php?table="+data,"_blank","width=275px,height=300px,scrollbars=yes,menubar=no");
			});
			
		}
	});
	
	/**
	 * Method to highlight all checked boxes 
	 * 
	 */
	
	$(document).keypress(function(event){
		if(event.which==0){
			$("input[type=checkbox]").not("#cb_all").each(function(){
				if($(this).attr("checked")){ //store all checked boxes
					$(this).closest("tr").css("background-color","#00FF00");
				}
			});
		}
	});
});