
// mock
var assert = require('assert');
const { JSDOM } = require('jsdom');
const jsdom = new JSDOM('<!doctype html><html><body></body></html>');
const { window } = jsdom;
const $ = global.jQuery = require('jquery')(window);
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

// params for controller	  
var projectId  = '';	
var loggedUser = 'guest';
var users = [];
var admins = [];
var sid = '';	
var REFRESHMIN = 2;
var REFRESHMAX = 10;
var SESSIONCOUNT = 1;

// test html kialakitása
$('body').append('<button type="tuppon" id="newTaskBtn">New task</button>');
$('body').append('<button type="tuppon" id="membersBtn">New task</button>');
$('body').append('<div id="database"></div>');
$('body').append('<members>'+
		'<member avatar="admin1" admin="1">admin1</member>'+
		'<member avatar="user1" admin="0">user1</member>'+
		'</members>');
$('#database').append(
'<waiting>'+
  '<h2>waiting</h2>'+
  '<task id="1">'+
  '<id>1</id>'+
  '<title>task1</title>'+
  '<desc></desc>'+
  '<type class="bug"></type>'+
  '<assign><img src="user0" /></assign>'+
  '<req></req>'+
  '</task>'+
'</waiting>');

$('body').append('<form id="taskForm">');
$('#taskForm').append('<input id="id" />');
$('#taskForm').append('<input id="state" />');
$('#taskForm').append('<input id="title" />');
$('#taskForm').append('<input id="desc" />');
$('#taskForm').append('<input id="type" />');
$('#taskForm').append('<input id="req" />');
$('#taskForm').append('<select id="assign"><option value="user2">user2</option></select>');
$('#taskForm').append('<button type="button" id="deltask">del task </button>')
$('#taskForm').append('<button type="button" id="Ok">OK</button>');

$('body').append('<form id="membersForm">');
$('#membersForm').append('<table>'+
		'<tbody></tbody>'+
		'</table>');

//include js file for test
var fs = require('fs');
eval(fs.readFileSync('./js/tasks.js')+'');

//jquery pageOnload futtatása
pageOnLoad();

describe('tests.js', function() {
	
	it('test_stateToJson', function() {
		
		var res = stateToJson('waiting');
		assert.ok(res.indexOf('task') >= 0);
	}); 
	
	it('test_membersToJson', function() {
		$('members').append(
		'<member avatar="user0" admin="1">'+
		  'user0nick'+
		'</member>');
		
		var res = membersToJson('waiting');
		assert.ok(res.indexOf('user0nick') >= 0);
	}) 
	
	it('test_saveToDatabase', function() {
		projectId = 'proba';
		global.postResult = {"fileTime":0};
		var res = saveToDatabase(projectId);
		assert.ok(true);
	});
	
	it('test_appendState', function() {
		var stateObj = [
			{"id":101,
			 "title":"test101",
			 "desc":"test101",
			 "class":"bug",
			 "assign":"",
			 "req":"?"
		}];
		appendState(stateObj, 'waiting');
		assert.ok(true);
	});
	
	it('test_userMemberAdmin', function() {
		loggedUser = 'admin1';
		var res = userMember();
		assert.ok(res);
	});
	
	it('test_userMemberMember', function() {
		loggedUser = 'user1';
		var res = userMember();
		assert.ok(res);
	});
	
	
	it('test_userMemberGuest', function() {
		loggedUser = 'guest';
		var res = userMember();
		assert.ok(!res);
	});
	
	it('test_getClosedTasks', function() {
		var res = getClosedTasks();
		assert.ok(res.length == 0);
	});
	
	it('test_setReadOnly', function() {
		setReadOnly($('#taskForm'));
		assert.ok($('#taskForm #title').attr('disabled') == 'disabled');
	});
	
	it('test_setWritable', function() {
		setWritable($('#taskForm'));
		assert.ok($('#taskForm #title').attr('disabled') != 'disabled');
	});
	
	it('test_refreshFromDatabase', function() {
		dbRefreshEnable = true;
		atDragging = false;
		global.postResult = {"project":{}};
		var res = refreshFromDatabase('test2');
		assert.ok(true);
	});
	
	it('test_newTaskBtnClickAdmin', function() {
		// set logged User is admin
		loggedUser = 'admin1';
		
		var res = $('#newTaskBtn').click();
		assert.ok($('#taskForm:visible'));
	});
	
	it('test_newTaskBtnClickMember', function() {
		// set logged User is admin
		loggedUser = 'user1';
		
		var res = $('#newTaskBtn').click();
		assert.ok($('#taskForm:visible'));
	});

	
	it('test_newTaskBtnClickGuest', function() {
		// set logged User is admin
		loggedUser = 'guest';
		
		var res = $('#newTaskBtn').click();
		assert.ok($('#taskForm:visible'));
	});
	
	
	it('test_okClickAdmin', function() {
		// set logged User is admin
		loggedUser = 'admin1';
		$('#taskForm').find('id').val(111);
		$('#taskForm').find('title').val('test111');
		$('#taskForm').find('desc').val('test111');
		$('#taskForm').find('type').val('bug');
		$('#taskForm').find('req').val('');
		$('#taskForm').find('assign').val('user1');
		var res = $('#Ok').click();
		assert.ok($('#taskForm:hidden'));
	});
		
	it('test_okClickMember', function() {
		// set logged User is admin
		loggedUser = 'user1';
		$('#taskForm').find('id').val(111);
		$('#taskForm').find('title').val('test111');
		$('#taskForm').find('desc').val('test111');
		$('#taskForm').find('type').val('bug');
		$('#taskForm').find('req').val('');
		$('#taskForm').find('assign').val('user1');
		var res = $('#Ok').click();
		assert.ok($('#taskForm:hidden'));
	});
	
	it('test_okClickGuest', function() {
		// set logged User is admin
		loggedUser = 'guest';
		$('#taskForm').find('id').val(111);
		$('#taskForm').find('title').val('test111');
		$('#taskForm').find('desc').val('test111');
		$('#taskForm').find('type').val('bug');
		$('#taskForm').find('req').val('');
		$('#taskForm').find('assign').val('user1');
		var res = $('#Ok').click();
		assert.ok($('#taskForm:hidden'));
	});
	
	it('test_delClickAdmin', function() {
		loggedUser = 'admin1';
		$('#deltask').click();
		assert.ok(true);
	});
	
	it('test_delClickMember', function() {
		loggedUser = 'user1';
		$('#deltask').click();
		assert.ok(true);
	});
	
	it('test_delClickGuest', function() {
		loggedUser = 'guest';
		$('#deltask').click();
		assert.ok(true);
	});

	it('test_taskClickMember', function() {
		loggedUser = 'user1';
		$('#1').click();
		assert.ok(true);
	});	

	it('test_taskClickGuest', function() {
		loggedUser = 'guest';
		$('#1').click();
		assert.ok(true);
	});	

	it('test_membersClickAdmin', function() {
		loggedUser = 'admin1';
		$('#membersBtn').click();
		assert.ok(true);
	});

	it('test_membersClickMember', function() {
		loggedUser = 'user1';
		$('#membersBtn').click();
		assert.ok(true);
	});

	it('test_membersClickGuest', function() {
		loggedUser = 'guest';
		$('#membersBtn').click();
		assert.ok(true);
	});

	it('test_taskDropAdmin', function() {
		loggedUser = 'admin1';
		var task = $('#1');
		var offset = {"left":220, "right":0, "top":200};
		taskDrop(null, {"offset": offset, "draggable": task});
		assert.ok(true);
	});

	it('test_taskDropGuest', function() {
		loggedUser = 'guest';
		var task = $('#1');
		var offset = {"left":220, "right":0, "top":200};
		taskDrop(null, {"offset": offset, "draggable": task});
		assert.ok(true);
	});

	it('test_taskDropMember', function() {
		loggedUser = 'user1';
		var task = $('#1');
		var offset = {"left":220, "right":0, "top":200};
		taskDrop(null, {"offset": offset, "draggable": task});
		assert.ok(true);
	});
	
});
