function resourceSelect(){
	//alert(item);
	document.body.style.cursor = "wait";
	//set url for ajax request
	url="auxFunctions.php"; 
	//ajax request
	$.get(url,{
		type:0,
		resource:$("#resource").val()},
		function(data){
			//return the data
			//alert(data);
			$("#resource_display").html(data);
			getValuesToPlot();
		});
	
	
}

function getValuesToPlot(){
	var counter=0;	//initialize debug counter
	//get values from drowpdowns
	var val=$("#resource").val();
	//alert(val);
	var time=$("#time").val();
	//initialize json array
	var json=new Array();
	//go through all checkboxes to create plots
	$("input[type=checkbox]").each(function(){
		var parameter_id=$(this).attr("id"); //store checkbox id (equip_id)
		//define link to create JSON object
		if(this.checked){
			counter++;
			var div="#plot_"+counter;
			var url="auxFunctions.php?type=1";
			var urlParams="resource="+val+"&time="+time+"&equip="+parameter_id;
			$.getJSON(url,urlParams, function(data){
				if(data.entry){
					json=data;
				} else {
					json=data.measure;
				}
				plotValues(json, div,"#tag_"+parameter_id, parameter_id);
				document.body.style.cursor = "default";
			});
		}		
	});
	
}

function plotValues(data, div, parameter, parameter_id){
	//get tag from each checkbox that is checked
	var tag=$(parameter).val();
	//which resource is this?
	var val=$("#resource").val();
	var min=0;
	var max=0;
	//get resource maximum and minimum values for this parameter
	var url="auxFunctions.php?type=2";
	var urlParams="resource="+val+"&param="+parameter_id;
	$.getJSON(url,urlParams, function(json){	
		min=json.equip_min;	//minimum limit
		max=json.equip_max;	//maximum limit
		//draw plot
		if(!data.entry){
				$.plot($(div), [{data: data, label: tag, 
					threshold: { 
						above: {
							limit: max,
							color: "rgb(200, 20, 30)" 
						}, 
						below: {
							limit: min,
							color: "rgb(200, 100, 30)" 
						}				
					}}], {
				//yaxis: { min: 2000, max: 3000 },
				xaxis: { mode: "time", timeformat: "%d/%m %H:%M"},
				selection: { mode: "x" },
				legend: {position: "sw"},
				crosshair: { mode: "x"},
				grid: { hoverable: true, autoHighlight: false }
			});
		} else {
			$.plot($(div), [{data: data.measure, label: tag, 
				threshold: { 
					above: {
						limit: max,
						color: "rgb(200, 20, 30)" 
					}, 
					below: {
						limit: min,
						color: "rgb(200, 100, 30)" 
					}				
				}}, {data: data.entry, label: "0-Not reserved; 1-Reserved", color: "rgb(200, 10, 30)" }], {
			//yaxis: { min: 2000, max: 3000 },
			xaxis: { mode: "time", timeformat: "%d/%m %H:%M"},
			selection: { mode: "x" },
			legend: {position: "sw"},
			crosshair: { mode: "x"},
			grid: { hoverable: true, autoHighlight: false }
		});
		}
		
		
		var options = {
			xaxis: { mode: "time", timeformat: "%d/%m %H:%M"},
			selection: { mode: "x" }
		};
			
		$(div).bind("plotselected", function (event, ranges) {
			// do the zooming
			if(!data.entry){
				plot = $.plot($(div), [{data: data, label: tag}],
				      $.extend(true, {}, options, {
					  xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to },
					  legend: {position: "sw"},
					  crosshair: { mode: "x"},
					  grid: { hoverable: true, autoHighlight: false }
			      }));	
			} else {
				plot = $.plot($(div), [
				                    {data: data.measure, label: tag},
				                    {data: data.entry, label: "0-Not reserved; 1-Reserved", color: "rgb(200, 10, 30)" }],
					      $.extend(true, {}, options, {
						  xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to },
						  legend: {position: "sw"},
						  crosshair: { mode: "x"},
						  grid: { hoverable: true, autoHighlight: false }
				      }));	
			}
					// don't fire event on the overview to prevent eternal loop
					//overview.setSelection(ranges, true);
		});
			
		$(div).dblclick(function(){
			plotValues(data, div, parameter, parameter_id)					
		});
	});
}
