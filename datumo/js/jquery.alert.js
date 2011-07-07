(function(jQuery){
	
	/**
	 * JQUERY ALERT PLUGIN
	 * 
	 * @author João Lagarto (jlagarto@igc.gulbenkian.pt)
	 * 
	 * My first plugin. Still very basic. Needs improvement
	 * Display an alert Message. Can be configurated 
	 * 
	 */

	jQuery.fn.alertMsg = function(options){
		//default settings
		var defaults = {
			target: "errorNotify",	
			style: "alertClass",//div where the message is going to be displayed
			text: "No message to be displayed", //default message
			fadein: 500,						//time to display message
		    fadeout: 2000, 						//time for the message to leave
		    idle: 5000							//time between fadein and fadeout
		};
		
		var options = jQuery.extend({}, defaults, options);		
		$("#"+options.target).addClass(options.style);
		$("#"+options.target).html(options.text);
		$("#"+options.target).fadeIn(options.fadein).idle(options.idle);
		$("#"+options.target).fadeOut(options.fadeout);
	};
	
	jQuery.fn.idle = function(time){ 
	      var element = $(this); 
	      element.queue(function(){ 
	         setTimeout(function(){ 
	            element.dequeue(); 
	         }, time);
	      });
	  };

	
})(jQuery);