function changeParentWindow(resId, date){
	window.location =  "weekview.php?resource=" + resId + "&date=" + date;
}

function changeDivColor(divId, color){
	div = document.getElementById(divId);
	div.style.backgroundColor = color;
}

function setWidth(divId, width){
	div = document.getElementById(divId);
	currentWidth = $(div).width();
	if(currentWidth > 1){
		$(div).width(currentWidth - width);
	}
	// else{
		// $(div).width(width);
	// }
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
			element.innerHTML = serverData.htmlCode;

			status2And4Divs = serverData.divsToChange;
			for(key in status2And4Divs){
				changeDivColor(key, status2And4Divs[key]);
			}
			
			qsEntries = serverData.quickScheduleEntries;
			for(key in qsEntries){
				// alert(key);
				setWidth(key, qsEntries[key]);
			}
		}
		,'json')
		.success(
			function(){
				$(".usageDataShow").tipTip({activation: 'click', fadeIn: 0, delay: 0});
			}
		)
	;
	// $(".usageDataShow").tipTip({activation: 'click', fadeIn: 0, delay: 0});
}

$(document).ready(
	function(){
		getTableData();
		$(".usageDataShow").tipTip({activation: 'click', fadeIn: 0, delay: 0});
	}
);