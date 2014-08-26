// redirect the browser to specified URL
function redirect(url) {
	if (!url.match('limitstart=')) { url += "&limitstart=0"; }
	if (!url.match('limit=')) { url += "&limit=20"; }
	window.location = url;
	return false;
}

// check time format correctness on input with id 'field'
function checkTime(field) {
	input = document.getElementById(field);
	if (!input) {
		alert("Can't find input field in checkTime()");
		return false;
	}

	value = input.value;

	// remove chars other than digits and colons
	value = value.replace(/[^\d:]/g, '');

	// cut trailing colon
	value = value.replace(/:$/, '');

	// check for syntax
	if (value.match(/^([0-9]+)$/)) {
		value = value + ":00:00";
	} else if (value.match(/^([0-9]+):([0-9]+)$/)) {
		value = value + ":00";
	}

	var hms = value.split(/:/);
	for (i = 0; i < 3; i++) {
		if ( !hms[i] ) {
			hms[i] = "0";
		}
		if ( hms[i].length < 2 ) {
			hms[i] = "0" + hms[i];
		}
		if ( hms[i].length > 2 ) {
			hms[i] = hms[i].replace(/(\d\d).*/, '$1');
		}
	}
	value = hms[0] + ":" + hms[1]; // + ":" + hms[2]; // excluding seconds

	input.value = value;
}

// check float correctness on input with id 'field'
function checkFloat(field) {
	input = document.getElementById(field);
	if (!input) {
		alert("Can't find input field in checkFloat()");
		return false;
	}

	value = input.value;

	// remove chars other than digits and colons
	value = value.replace(/[^\d\.]/g, '');

	// cut trailing dot
	value = value.replace(/.$/, '');

	var splitted = value.split(/./);
	value = splitted[0] + '.' + splitted[1];

	input.value = value;
}

// check date field
function checkDate(field, begin, end) {
	input = document.getElementById(field);
	if (!input) {
		alert("Can't find input field " + field + " in checkDate()");
		return false;
	}

	original_value = value = input.value;

	// transform chars other than digits in dashes
	value = value.replace(/[^0-9]+/g, '-');

	// cut trailing colon
	value = value.replace(/-$/, '');

	// transform YYYY-MM-DD into DD-MM-YYYY
	if (value.match(/\d\d\d\d-\d\d?-\d\d?/)) {
		value = value.replace(/(\d\d\d\d)-(\d\d?)-(\d\d?)/, '$3-$2-$1');
	}
	if (begin.match(/\d\d\d\d-\d\d?-\d\d?/)) {
		begin = begin.replace(/(\d\d\d\d)-(\d\d?)-(\d\d?)/, '$3-$2-$1');
	}
	if (end.match(/\d\d\d\d-\d\d?-\d\d?/)) {
		end = end.replace(/(\d\d\d\d)-(\d\d?)-(\d\d?)/, '$3-$2-$1');
	}

	if (!value.match(/\d\d?-\d\d?-\d\d\d\d/)) {
		input.value = begin;
		return;
	}

	var dmy = value.split(/-/);
	var begin_dmy = begin.split(/-/);
	var end_dmy = end.split(/-/);

	// check the year
	if (dmy[2].length < 4) { dmy[2] = dmy[2] * 1.0 + 2000; }
	else if (dmy[2].length > 4) { dmy[2] = dmy[2].replace(/(\d\d\d\d).*/, '$1'); }

	// check the month
	if (dmy[1].length < 2) { dmy[1] = '0' + dmy[1]; }
	else if (dmy[1].length > 2) { dmy[1] = dmy[1].replace(/(\d\d).*/, '$1'); }

	// check the day
	if (dmy[0].length < 2) { dmy[0] = '0' + dmy[0]; }
	else if (dmy[0].length > 2) { dmy[0] = dmy[0].replace(/(\d\d).*/, '$1'); }

	var value_date = new Date(dmy[2], dmy[1], dmy[0]);
	var begin_date = new Date(begin_dmy[2], begin_dmy[1], begin_dmy[0]);
	var end_date = new Date(end_dmy[2], end_dmy[1], end_dmy[0]);

	if (value_date < begin_date) {
		value = begin;
	} else if ( value_date > end_date ) {
		value = end;
	} else {
		value = dmy[0] + "-" + dmy[1] + "-" + dmy[2];
	}

	if (original_value != value) {
		input.value = value;
	}
}

/* spin button functions */
function spin(widgetId, direction, min, max, step) {
	var w = document.getElementById(widgetId);
	if (w) {
		var new_value = w.value.replace(/[^0-9]/g, '');
		if (direction) {
			new_value = new_value * 1 + step;
		} else {
			new_value = new_value * 1 - step;
		}
		if (new_value * 1 < min) { new_value = min; alert('valore troppo basso. il minimo consentito è ' + min); }
		else if (new_value * 1 > max) { new_value = max; alert('valore troppo alto. il massimo consentito è ' + max); }
		w.value = new_value;
	}
}

function check_in_range(widgetId, min, max) {
	var w = document.getElementById(widgetId);
	if (w) {
		// alert('current: ' + w.value + ' min: ' + min + ' max: ' + max);
		w.value = w.value.replace(/[^0-9]/g, '');
		if (w.value * 1 < min) { w.value = min; alert('valore troppo basso. il minimo consentito è ' + min); }
		else if (w.value * 1 > max) { w.value = max; alert('valore troppo alto. il massimo consentito è ' + max); }
		// alert('current: ' + w.value + ' min: ' + min + ' max: ' + max);
	}
}

/* browser detection */
var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari",
			versionSearch: "Version"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			   string: navigator.userAgent,
			   subString: "iPhone",
			   identity: "iPhone/iPod"
	    },
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};

function popup(url) {
	window.open(url, "", "top=10, left=10, width=750, height=400, status=no, menubar=no, toolbar=no scrollbar=no");
}

BrowserDetect.init();
