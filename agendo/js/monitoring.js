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
			if(serverData.errorMsg == ""){
				document.getElementById('groupdiv').style.display = 'table';
				element = document.getElementById('tableHolder');
				element.innerHTML = serverData.htmlCode;

				entriesStarting = serverData.entriesStarting;
				for(key in entriesStarting){
					changeDivColor(key, entriesStarting[key]);
				}
				
				qsEntries = serverData.quickScheduleEntries;
				for(key in qsEntries){
					setWidth(key, qsEntries[key]);
				}
			}
			else{
				showMessage(serverData.errorMsg);
				document.getElementById('group').src='pics/group.png';
			}
		}
		,'json')
		.success(
			function(serverData){
				$(".usageDataShow").tipTip({activation: 'click', fadeIn: 0, delay: 0});
			}
		)
	;
}

var	weekDayScaled = null;
function scaleMe(weekday){
	if(weekDayScaled != weekday){
		if(weekDayScaled != null){
			scaleMeAux(weekDayScaled);
		}
		weekDayScaled = weekday;
		scaleMeAux(weekDayScaled, true);
	}
	else{
		weekDayScaled = null;
		scaleMeAux(weekday);
	}
}

var scaleFactor = 7;
function scaleMeAux(weekday, showOnlyWeekday){
	showOnlyWeekday = showOnlyWeekday || false;
	var table = document.getElementById("weekdaysResources");
	if(showOnlyWeekday){
		for (var i = 0, row; row = table.rows[i]; i++) {
			var mod = 1;
			for(var y = 1, col; col = row.cells[y]; y++){
				if(y == weekday){
					mod = 0;
					var elms = col.childNodes;
					var elm;
					for(var j = 0, maxI = elms.length; j < maxI; ++j){
						elm = elms[j];
						tempWidth = $(elm).width();
						$(elm).width(tempWidth * scaleFactor);
					}
				}
				else{
					col.style.display = 'none';
				}
			}
		}
	}
	else{
		getTableData();
	}
}

$(document).ready(
	function(){
		// getTableData();
		// $(".usageDataShow").tipTip({activation: 'click', fadeIn: 0, delay: 0});
	}
);