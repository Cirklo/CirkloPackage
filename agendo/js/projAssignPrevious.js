var activeMatrix = {};
var defaultArray = {};

function selectAllFromDep(department, checked){
	$('#dep_projs-' + department).find('input:checkbox').each(function () {
		this.checked = checked;
		sendCheckedToArray(department, this.value, this.checked);
     });
}

function sendCheckedToArray(department, project, checked){
	if(!activeMatrix[department]){
		activeMatrix[department] = {};
	}
	activeMatrix[department][project] = checked;
}

function changeDefault(department, project){
	defaultArray[department] = project;
}

function saveData(){
	if(isObjEmpty(activeMatrix) && isObjEmpty(defaultArray)){
		showMessage("No changes were made");
		return;
	}

	$.post(
		'projAssign.php'
		,{'activeMatrix': JSON.stringify(activeMatrix), 'defaultArray': defaultArray}
		,function(serverData){
			showMessage(serverData.message, serverData.isError);
		}
		,'json'
	)
	.error(
		function(error){
			showMessage(error.responseText, true);
		}
	);
}