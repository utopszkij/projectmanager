var assert = require('assert');

const { JSDOM } = require('jsdom');
const jsdom = new JSDOM('<!doctype html><html><body></body></html>');
const { window } = jsdom;
const $ = global.jQuery = require('jquery')(window);
window.setTimeout = function(s) {};
global.alert = function(str) {};
global.confirm = function(str,yesfun, nofun) { yesfun(); };


// params for controller	  
var projectId  = '';	
var loggedUser = 'guest';
var users = [];
var admins = [];
var sid = '';	

// include js file for test 

var fs = require('fs');
eval(fs.readFileSync('./js/tasks.js')+'');

describe('tests.js', function() {
	
	it('test_stateToJson', function() {
		$('body').append('<div id="database"></div>');
		$('#database').append(
		'<waiting>'+
		  '<task id="1">'+
		  '<id>1</id>'+
		  '<title>task1</title>'+
		  '<desc></desc>'+
		  '<type class="bug"></type>'+
		  '<assign><img src="user0" /></assign>'+
		  '<req></req>'+
		  '</task>'+
		'</waiting>');
		
		var res = stateToJson('waiting');
		assert.ok(res.indexOf('task') >= 0);
	}); 
	
	it('test_membersToJson', function() {
		$('#database').append('<members></members>');
		$('members').append(
		'<member avatar="user0" "admin":"I">'+
		  'user0nick'+
		'</member>');
		
		var res = membersToJson('waiting');
		assert.ok(res.indexOf('user0nick') >= 0);
	}) 
	
	it('test_saveToDatabase', function() {
		projectId = 'proba';
		var res = saveToDatabase(projectId);
	});
	
});
