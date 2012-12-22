$(document).ready(
	function(){
	   $('#projectList').multiselect(
			{
				noneSelectedText: 'Select project(s)'
				,height: 'auto'
				,multiple: false
				,selectedList: 1
			}
	   ).multiselectfilter();

	   $('#resourceList').multiselect(
			{
				noneSelectedText: 'Select resource(s)'
				,selectedText: '# of # selected'
				,height: 'auto'
			}
	   ).multiselectfilter();
	}
);

$("#projectList").bind("multiselectoptgrouptoggle", function(event, ui){});

function assignProjs(){
	var projects = $("#projectList").val();
	var resources = $("#resourceList").val();
	
	if(!projects){
		showMessage('Please select a project');
		return;
	}
	
	if(!resources){
		showMessage('Please select at least one resource');
		return;
	}

	var project = projects[projects.length - 1];

	$.post(
		"projAssign.php"
		, {project: project, "resources[]": resources}
		, function(serverData){
			showMessage(serverData.message, serverData.isError);
		}
		, 'json'
	/*)
	.error(
		function(error){
			showMessage(error.responseText, true);
		}*/
	);
}