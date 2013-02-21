function mailListCheck(element){
	$.post(
		'mailListAssign.php'
		,{functionName: 'mailListCheck', resource: element.value, check: element.checked}
		,function(serverData){
			if(!serverData.isError){
				element.checked = serverData.check;
			}
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