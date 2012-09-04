//check plugins
function check() {

	BrowserDetect.init();

	var br = BrowserDetect.browser;
	var ver = BrowserDetect.version;
	ver = parseFloat(ver);

	// Browser CHECK //
	// ////////////////
	if ((br == "Firefox" && ver >= 6) || (br == "Safari" && ver >= 4)
			|| (br == "Chrome") || (br == "Explorer" && ver >= 7)
			|| (br == "Opera")) {
		$("#browsersupported").show();
	} else {
		$("#browsersupported").show();
	}// ;

	// Acrobat Reader CHECK //
	// ///////////////////////

	if (detectAcrobat())
		$("#acrobatreaderinstalled").show();
	else
		$("#acrobatreadernotinstalled").show();

	// Flash Player CHECK //
	// /////////////////////
	if (detectFlash())
		$("#flashplayerinstalled").show();
	else
		$("#flashplayernotinstalled").show();

	// Schockwave Player CHECK //
	// /////////////////////
	if (detectShockwave())
		$("#shockinstalled").show();
	else
		$("#shocknotinstalled").show();

	var a = $("#acrobatreadernotinstalled").css('display');
	var f = $("#flashplayernotinstalled").css('display');
	var b = $("#browsernotsupported").css('display');
	var s = $("#shocknotinstalled").css('display');

	if (a == 'none' && f == "none" && b == 'none' && s == 'none') {
		$("#OK").show();
		$("#notOK").hide();
	}

}

function detectShockwave() {
	if (window.ActiveXObject) {
		try {
			control = new ActiveXObject('SWCtl.SWCtl');
			version = control.ShockwaveVersion('').split('r');
			version = parseFloat(version[0]);
			if (version >= 10)
				return true;
		} catch (e) {
			return false;
		}
	} else {
		for ( var i = 0; i < navigator.plugins.length; i++) {
			if (navigator.plugins[i].name == 'Shockwave Flash')
				return true;
		}
		return false;
	}
}

function detectFlash() {
	if (window.ActiveXObject) {
		try {
			control = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
			version = control.GetVariable('$version').substring(4);
			version = version.split(',');
			version = parseFloat(version[0] + '.' + version[1]);
			if (version >= 10)
				return true;
		} catch (e) {
			return false;
		}
	} else {
		for ( var i = 0; i < navigator.plugins.length; i++) {
			if (navigator.plugins[i].name == 'Shockwave Flash')
				return true;
		}
		return false;
	}
}

function detectAcrobat() {
	if (window.ActiveXObject) {
		var control = null;
		var version = false;
		try {
			// AcroPDF.PDF is used by version 7 and later
			control = new ActiveXObject('AcroPDF.PDF');
		} catch (e) {
			// Do nothing
		}

		if (!control) {
			for ( var x = 2; x < 10; x++) {
				try {
					oAcro = eval("new ActiveXObject('PDF.PdfCtrl." + x + "');");
					if (oAcro) {
						version = true;
					}
				} catch (e) {
				}
			}

			try {
				oAcro4 = new ActiveXObject('PDF.PdfCtrl.1');
				if (oAcro4) {
					version = true;
				}
			} catch (e) {
			}

			try {
				oAcro7 = new ActiveXObject('AcroPDF.PDF.1');
				if (oAcro7) {
					version = true;
				}
			} catch (e) {
			}
			return version;
		}

		if (control) {
			version = control.GetVersions().split(',');
			version = version[0].split('=');
			version = parseFloat(version[1]);

			return true;
		} else {
			return false;
		}

	} else {
		for ( var i = 0; i < navigator.plugins.length; i++) {
			// alert(navigator.plugins[i].name);
			if (navigator.plugins[i].name == 'Adobe Acrobat'
					|| navigator.plugins[i].name == 'Chrome PDF Viewer')
				return true;
		}
		return false;
	}
}

var BrowserDetect = {
	init : function() {
		this.browser = this.searchString(this.dataBrowser)
				|| "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
				|| this.searchVersion(navigator.appVersion)
				|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString : function(data) {
		for ( var i = 0; i < data.length; i++) {
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch
					|| data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			} else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion : function(dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1)
			return;
		return parseFloat(dataString.substring(index
				+ this.versionSearchString.length + 1));
	},
	dataBrowser : [ {
		string : navigator.userAgent,
		subString : "Chrome",
		identity : "Chrome"
	}, {
		string : navigator.userAgent,
		subString : "OmniWeb",
		versionSearch : "OmniWeb/",
		identity : "OmniWeb"
	}, {
		string : navigator.vendor,
		subString : "Apple",
		identity : "Safari",
		versionSearch : "Version"
	}, {
		prop : window.opera,
		identity : "Opera",
		versionSearch : "Version"
	}, {
		string : navigator.vendor,
		subString : "iCab",
		identity : "iCab"
	}, {
		string : navigator.vendor,
		subString : "KDE",
		identity : "Konqueror"
	}, {
		string : navigator.userAgent,
		subString : "Firefox",
		identity : "Firefox"
	}, {
		string : navigator.vendor,
		subString : "Camino",
		identity : "Camino"
	}, { // for newer Netscapes (6+)
		string : navigator.userAgent,
		subString : "Netscape",
		identity : "Netscape"
	}, {
		string : navigator.userAgent,
		subString : "MSIE",
		identity : "Explorer",
		versionSearch : "MSIE"
	}, {
		string : navigator.userAgent,
		subString : "Gecko",
		identity : "Mozilla",
		versionSearch : "rv"
	}, { // for older Netscapes (4-)
		string : navigator.userAgent,
		subString : "Mozilla",
		identity : "Netscape",
		versionSearch : "Mozilla"
	} ],
	dataOS : [ {
		string : navigator.platform,
		subString : "Win",
		identity : "Windows"
	}, {
		string : navigator.platform,
		subString : "Mac",
		identity : "Mac"
	}, {
		string : navigator.userAgent,
		subString : "iPhone",
		identity : "iPhone/iPod"
	}, {
		string : navigator.platform,
		subString : "Linux",
		identity : "Linux"
	} ]

};
