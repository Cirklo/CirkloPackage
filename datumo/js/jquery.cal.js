$(document).ready(function(){
	/**
	 * Method to handle resource display in mycalendar
	 */
	
	$("#calRes").click(function(){
		var arr=new Array;
		//find all checked checkboxes and stores its id
		$("#options").find("input[type=checkbox]:checked").each(function(){
			arr.push($(this).attr("id"));
		});
		//empty calendar div
		$("#calendar").html("");
		//call calendar feed
		$('#calendar').fullCalendar({
			editable: false,
			events: "calendar_feed.php?regular&ids="+arr,
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'

			},
			/*eventRender: function(event, element) {
		        element.qtip({
		            content: event.description
		        });
		    },*/
			loading: function(bool) {
				if (bool) $('#loading').show();
				else $('#loading').hide();
			}
			
		});

		
	});
	
	/**
	 * Method to call calendar php feed to display manager-related entries
	 */
	
	$("#managerView").click(function(){
		//empty calendar div
		$("#calendar").html("");
		//call calendar feed
		$('#calendar').fullCalendar({
			editable: false,
			events: "calendar_feed.php?manager",
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'

			},
			loading: function(bool) {
				if (bool) $('#loading').show();
				else $('#loading').hide();
			}
			
		});
	});
});