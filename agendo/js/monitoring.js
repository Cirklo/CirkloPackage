function checkRedirect(element, showMsg, msg){
	if(showMsg){
		showMessage(msg);
		element.checked = false;
	}
	else{
		var url = window.location.href;
		value = element.value;
		if(url.indexOf(value) != -1){
			window.location.href = url.replace("&" + value, "");
		}
		else{
			window.location.href += "&" + value;
		}
	}
}

function changeToDate(date){
	var url = window.location.href;
	// haha, kinda funny if you are portuguese
	var dateData = "&date=" + date;
	if(date == null){
		dateData = "";
	}
	
	// This is pretty horrible...
	if((index = url.indexOf('date')) != -1){
		index --;
		url = url.replace(url.substring(index, index + 14), "");
	}
	
	window.location.href = url + dateData;
}

function changeParentWindow(resId, date, path){
	window.opener.location =  path + "/weekview.php?resource=" + resId + "&date=" + date;
}

$(document).ready(
	function(){
		$('#weekdaysResources').width($('#weekdaysResources').width() + 5);
		$(".usageDataShow").tipTip({activation: 'click', fadeIn: 0, delay: 0});
	}
);