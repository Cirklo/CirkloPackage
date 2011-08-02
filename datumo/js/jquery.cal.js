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
			defaultView: 'agendaWeek',
			/*eventRender: function(event, element) {
		        element.qtip({
		            content: event.description
		        });
		    },*/
			eventClick: function(event) {
				resp=confirm("Do you want to export this entry to your personal calendar?");
				if(resp){
					//SEND EMAIL WITH THE .ICS ATTACHED
					$.get("calendar_feed.php?export",{
						events:event
					},
					function(data){
						alert(data);
					});
				}
			},
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
			defaultView: 'agendaWeek',
			eventClick: function(event) {
				resp=confirm("Do you want to export this entry to your personal calendar?");
				if(resp){
					//SEND EMAIL WITH THE .ICS ATTACHED
					$.get("calendar_feed.php?export",{
						events:event
					},
					function(data){
						alert(data);
					});
				}
			},
			loading: function(bool) {
				if (bool) $('#loading').show();
				else $('#loading').hide();
			}
			
		});
	});
});