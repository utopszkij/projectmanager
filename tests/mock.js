
exports.init = function(window) {
	window.setTimeout = function(s) {};
	window.scrollTo = function(x,y) {};
	document = {};
	document.documentElement = {};
	document.documentElement.scrollTop = 0;
	global.postResult = {};
	global.alert = function(str) {};
	global.confirm = function(str,yesfun, nofun) { yesfun(); };
	global.post = function(url, options, fun) {
		fun(global.postResult);
	}	
};

