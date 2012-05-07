

$(document).ready(function(){
	//local variable initialization so it doesn't crash ahead
	var resource_id="";
	//time between each trigger (in ms)
	var time=10000; 
	//get script name
	filename=getUrlName();
	//distinguish between Agendo and Datumo (need to find a better way to do this)
	if(filename=="weekview.php"){
		//set correct path to pub script
		url="../datumo/pub.php?timer"; 
		resource=$(location).attr('href');
		index=resource.lastIndexOf("?");
		//get current resource id
		resource_id=resource.substring(index+10,resource.length);
		//remove strange character if it exists
		if((index=resource_id.lastIndexOf("#"))!=-1)
			resource_id=resource_id.substring(0, index);
	} else {	
		url="pub.php?timer";
	}
	//start triggers
	$(document).everyTime(time, function(i) {
		//alert(i);
		//increment value in the database
		$.get(url,{
			path:filename,
			time:time,
			resource:resource_id},
			function(data){});
	});
});

/**
 * Method to increment the number of clicks on a specific pub ad
 * @param id
 * @param outlink
 */

function clickPub(id,outlink){
	filename=getUrlName();
	//distinguish between Agendo and Datumo (need to find a better way to do this)
	if(filename=="weekview.php"){	url="../datumo/pub.php?count"; } 
	else {	url="pub.php?count"; }
	//send ajax request counting the number of clicks
	$.get(url,{
		pub:id},
		function(data){
			// window.open(outlink);
			window.open(outlink,'_blank','directories=no,status=no,menubar=yes,location=yes,resizable=yes,scrollbars=no'); // not blocked using this method?
		});
}

/**
 * Method to get current file name
 * @returns
 */

function getUrlName(){
	var pathname = window.location.pathname;
	var index = pathname.lastIndexOf("/");
	var filename = pathname.substring(index+1);
	return filename;
}
