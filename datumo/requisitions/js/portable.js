function updateBasket(basket_id){
	//Update basket delivery
	var url="requisitions.php?type=6";
	$.get(url,{
		basket:basket_id,
		newstate:"Received"},
	function(data){
		alert(data);
	});
	
}