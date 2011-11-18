function changeParentWindow(resId, date){
	window.location =  "weekview.php?resource=" + resId + "&date=" + date;
}

function changeDivColor(divId, color){
	div = document.getElementById(divId);
	div.style.backgroundColor = color;
}

var resource = false;
var date;
function setResourceAndDate(zeeDate, res){
	date = zeeDate;
	if(res != false){
		resource = res;
	}
}

function changeToDate(zeeDate){
	date = zeeDate;
	getTableData();
}

function getTableData(){
	url = "../agendo/monitoring.php?gimmeGroupViewData";
	if($('#labelsDiv').length > 0){
		if($('#userCheck').attr('checked')){
			url += "&userLogged";
		}
		url += "&resource=" + resource;
		if($('#similarCheck').attr('checked')){
			url += "&simEquip";
		}
		if($('#equipTypeCheck').attr('checked')){
			url += "&equipType";
		}
	}
	url += "&date=" + date;
	
	$.get(url, 
		function(serverData){
			element = document.getElementById('tableHolder');
			element.innerHTML = serverData;
		}
	);
}

$(document).ready(
	function(){
		getTableData();
		// $(".usageDataShow").tipTip({activation: 'click', fadeIn: 0, delay: 0});
	}
);