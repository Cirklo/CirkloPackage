function autoFill(id,table,e,user_id)
{
        var objEVT = window.event? event : e;
        
	var options = {
		script: "../dbSurvey.php?table=" + table + "&user=" + user_id + "&",
		varname:"input",
		minchars: 2,

	};

	var as_xml = new bsn.AutoSuggest(id, options);	
}
