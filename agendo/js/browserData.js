function detect(detectOs){
	detectOs = detectOs || false;
	var data;
	if(detectOs){
		data = [
			{
				string: navigator.platform
				,subString: "Win"
				,identity: "Windows"
			},
			{
				string: navigator.platform
				,subString: "Mac"
				,identity: "Mac"
			},
			{
				string: navigator.userAgent
				,subString: "iPhone"
				,identity: "iPhone/iPod"
			},
			{
				string: navigator.platform
				,subString: "Linux"
				,identity: "Linux"
			}
		];
	}
	else{
		data = [
			{
				string: navigator.userAgent
				,subString: "Chrome"
				,identity: "Chrome"
			},
			{ 	string: navigator.userAgent
				,subString: "OmniWeb"
				,versionSearch: "OmniWeb/"
				,identity: "OmniWeb"
				// ,unsupported: true
			},
			{
				string: navigator.vendor
				,subString: "Apple"
				,identity: "Safari"
				,versionSearch: "Version"
			},
			{
				// prop: window.opera,
				string: window.opera
				,subString: "Opera"
				,identity: "Opera"
				,versionSearch: "Version"
				// ,unsupported: true
			},
			{
				string: navigator.vendor
				,subString: "iCab"
				,identity: "iCab"
				// ,unsupported: true
			},
			{
				string: navigator.vendor
				,subString: "KDE"
				,identity: "Konqueror"
				// ,unsupported: true
			},
			{
				string: navigator.userAgent
				,subString: "Firefox"
				,identity: "Firefox"
			},
			{
				string: navigator.vendor
				,subString: "Camino"
				,identity: "Camino"
				// ,unsupported: true
			},
			{		// for newer Netscapes (6+)
				string: navigator.userAgent
				,subString: "Netscape"
				,identity: "Netscape"
			},
			{
				string: navigator.userAgent
				,subString: "MSIE"
				,identity: "Explorer"
				,versionSearch: "MSIE"
				,unsupported: true
			},
			{
				string: navigator.userAgent
				,subString: "Gecko"
				,identity: "Mozilla"
				,versionSearch: "rv"
			},
			{ 		// for older Netscapes (4-)
				string: navigator.userAgent
				,subString: "Mozilla"
				,identity: "Netscape"
				,versionSearch: "Mozilla"
			}
		];
	}
	
	return searchData(data);
}

function searchData(data) {
	for (var i=0;i<data.length;i++)	{
		var dataString = data[i].string;
		var dataProp = data[i].prop;
		var unsupported = data[i].unsupported;
		// this.versionSearchString = data[i].versionSearch || data[i].identity;
		if (dataString) {
			if (dataString.indexOf(data[i].subString) != -1){
				if(unsupported){
					return false;
				}
				return data[i].identity;
			}
		}
		// else if (dataProp){
			// return data[i].identity;
		// }
	}
	return false;
}