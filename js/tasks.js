  // globals	
  var saveTimer = 0;	
  var refreshTimer = 0;	
  var oldAssign = '';	       // use in checTaskForm
  var dbRefreshEnable = true;  // false at saveToDatabase use in refreshFromdatabase
  var dbSaveEnable = true;     // false at RefresFromDatabase use in saveToDatabase
  var fileTime = 0;            // use in refreshFromDatabase
  var atDragging = false;      // mark at dragging, use refreshFromDatabase
  
  // params for controller	  
  // projectId string  requed	
  // loggedUser string requed
  // users array [avatarurl,nincname],...] project' members  OPTIONAL
  // admins array [avatarurl]    OPTIONAL
  // sid string requed
  
 /**
  * convert #database state into json string
  * @param state
  * @return string
  */ 
 function stateToJson(state) {
   var tasks = $(state).find('task');
   var i;
   var result = '"'+state+'": [';
   var task = null;   
   for (i=0; i<tasks.length; i++) {
	   task = $('#'+tasks[i].id);
	   if (i > 0) {
		   result += ', ';
	   }
	   result += '{';
	   result += '"id":'+task.find('id')[0].innerHTML+', ';
	   result += '"title":'+JSON.stringify(task.find('title')[0].innerHTML)+', ';
	   result += '"desc":'+JSON.stringify(task.find('desc')[0].innerHTML)+', ';
	   result += '"type":"'+task.find('type')[0].class+'", ';
	   result += '"req":'+JSON.stringify(task.find('req')[0].innerHTML)+', ';
	   result += '"assign":"'+task.find('img')[0].src+'"';
	   result += '}';
   }
   return result+'], '	 
 } 
 
 /**
  * convert #database members into json string
  * @return string
  */
 function membersToJson() {
	var result = '"members":['; 
	var i;
	var members = $('member');
	for (i=0; i<members.length; i++) {
		if (i > 0) {
			result += ', ';
		}
		result += '{"avatar":"'+members[i].getAttribute('avatar')+'", ';
		result += '"admin":"'+members[i].getAttribute('admin')+'", ';
		result += '"nick":'+JSON.stringify(members[i].innerHTML)+'}';
	}
	result += ']';
	return result; 
 }
  
  
 /**
 * save task into database
 * server input: act='save', projectId, project 
 * server result: fileTime
 *  use global fileTime
 */
 function saveToDatabase(projectId) {
 	if (dbSaveEnable) {
 		dbRefreshEnable = false;

 	   // #database convert into  json string	
	   var s = '{';
	   s += stateToJson('waiting');
	   s += stateToJson('canstart');
	   s += stateToJson('atwork');
	   s += stateToJson('canverify');
	   s += stateToJson('atverify');
	   s += stateToJson('closed');
	   s += membersToJson();
	   s += '}';
	   if (projectId == 'demo') {
	 			dbRefreshEnable = true;
	   } else {
		 	$.post('./app.php', {"option":"tasks", "task":"save", "projectid":projectId, "project": s, "sid": sid}, function(res) {
		 		fileTime = res.fileTime;
	 			dbRefreshEnable = true;
		 	})
	   }
 	} else {
 		 clearTimeout(saveTimer);
 	 	 saveTimer = window.setTimeout("saveToDatabase(projectId)", 5000);
 	}
 }
 
 function appendState(stateObj, stateName) {
	 var i;
	 var task = null;
	 var s = '';
	 for (i=0; i < stateObj.length; i++) {
		task = stateObj[i];
		s = '<task id="'+task.id+'">';
		s += '<id>'+task.id+'</id>';
		s += '<title>'+task.title+'</title>';
		s += '<desc>'+task.desc+'</desc>';
		s += '<type class="'+task.type+'">&nbsp;</type>';
		s += '<assign><img src="'+task.assign+'" /></assign>';
		s += '<req>'+task.req+'</req>';
		$(stateName).append(s);
	 }
	 
 }
 
 /**
 * refresh task from database
 * server input: act='refresh', projectId
 * server result: fileTime, project (json string)
 *  use global fileTime
 */
 function refreshFromDatabase(projectId, fun) {
 	if ((dbRefreshEnable) && (!atDragging)) {
 		dbSaveEnable = false;
	 	$.post('./app.php', {"option":"tasks", "task":"refresh", "projectid":projectId, "fileTime": fileTime}, function(res) {
	 		fileTime = res.fileTime;
	 		if (res.project != undefined) {
	 			// json project --> #database html dom
	 			$('task').remove();
	 			appendState(res.project.waiting, 'waiting');
	 			appendState(res.project.canstart, 'canstart');
	 			appendState(res.project.atwork, 'atwork');
	 			appendState(res.project.canverify, 'canverify');
	 			appendState(res.project.atverify, 'atverify');
	 			appendState(res.project.closed, 'closed');
		        colTranslate(); 
		        colResize();
	 			setTaskEventHandlers();
	 		}
 			dbSaveEnable = true;
 			if (fun != undefined) {
 				fun();
 			}	
 		    clearTimeout(refreshTimer);
	  	    refreshTimer = window.setTimeout("refreshFromDatabase(projectId)", 15000);
	 	})
 	} else {
 		 dbSaveEnable = true;
 		 clearTimeout(refreshTimer);
 	 	 refreshTimer = window.setTimeout("refreshFromDatabase(projectId)", 5000);
 	}
 }

 function getIdMax() {
		var result = 0;
		var tasks = $('task');
		var i;
		var j = 0;
		for (i=0; i<tasks.length; i++) {
			j = Number(tasks[i].id);
			if ((j > result) && (tasks[i].id != 'taskInit')) {
				result = j;			
			}		
		}
		return result; 
 }
 
 function userMember() {
 	var result = false;
 	var i;
 	var members = $('members').find('member');
 	for (i=0; i < members.length; i++) {
		if (members[i].attributes.avatar.nodeValue == loggedUser) {
			result = true;		
		} 	
 	}
	return result; 
 }

 function loggedAdmin() {
 	var result = false;
 	var i;
 	var members = $('members').find('member');
 	for (i=0; i < members.length; i++) {
		if ((members[i].attributes.avatar.nodeValue == loggedUser) &&
		    (members[i].attributes.admin.nodeValue == "1")) {
			result = true;		
		} 	
 	}
	return result; 
 }

 function getClosedTasks() {
	var result = [];
	var tasks = $('closed').find('task');
	var i;
	for (i=0; i<tasks.length; i++) {
		result.push(tasks[i].id);	
	}
	return result; 
 }	

 /**
 * taskForm validation
 * @param JQueryObject
 * @return bool, alert errorMsg
 */	
 function checkForm(taskForm) {
   // check state from taskForm
   var result = true;
 	var newState = taskForm.find('#state').val();
 	var req = taskForm.find('#req').val();
 	result = checkState3(newState, req);
 	if (result) {
		var assign = taskForm.find('#assign').val();
		if ((oldAssign != assign) && /* hozzányult */ 
			 (loggedAdmin() == false) && /* nem admin */
		    (assign != loggedUser) /* nem önmgához rendelte */
		) {
			global.alert("<?php echo ACCESSDENIED; ?>");
			result = false;		
		} else if ((oldAssign != assign) && /* hozzányult */ 
			        (loggedAdmin() == false) && /* nem admin */
		           (oldAssign != 'https://www.gravtar.com/avatar/') /* eddig nem volt üres */
		) {
			global.alert("<?php echo ACCESSDENIED; ?>");
			result = false;		
		}			 
 	}
 	return result;
 }	
 
 /**
 * check <task> tag state
 * @param JQueryObject
 * @return bool, alert errorMsg
 */
 function checkState2(task, newState) {
	var req = task.find('req').html();
 	return checkState3(newState, req);
 } 
 
 /**
 * check state (accesRight and requed condition)
 * @param string state
 * @param string req condition
 * @return bool, alert errorMsg
 */
 function checkState3(newState, req) {
	var result = true;
	var i;
	var closedTasks;
	if ((newState != 'waiting') && (req != '')) {
		req = req.split(',');
		closedTasks = getClosedTasks();
		for (i=0; i<req.length; i++) {
			if (closedTasks.indexOf(req[i]) < 0) {
				result = false;			
			}
		}
	}
	if (!result) {
		global.alert("<?php echo NOTSTARTING; ?>");	
	}
	return result; 
 }

 function accessRight(task, viewMessage) {
   var result;
 	if ((loggedUser == task.find('img').attr('src')) || (loggedAdmin())) {
		result = true;
	} else {
		result = false;
		if (viewMessage) {
			global.alert("<?php echo ACCESSDENIED; ?>");		
		}
	}	 
	return result;
 }

 function setReadOnly(taskForm) {
 	taskForm.find('#id').attr('disabled','disabled');
 	taskForm.find('#title').attr('disabled','disabled');
 	taskForm.find('#type').attr('disabled','disabled');
 	taskForm.find('#desc').attr('disabled','disabled');
 	taskForm.find('#state').attr('disabled','disabled');
 	taskForm.find('#prior').attr('disabled','disabled');
 	taskForm.find('#req').attr('disabled','disabled');
 	if ((userMember() && (taskForm.find('#assign').val() == 'https://www.gravatar.com/avatar/')) ||
 	    (loggedAdmin()))  {
 	   taskForm.find('#assign').attr('disabled',false);
 	} else {
 	   taskForm.find('#assign').attr('disabled','disabled');
 	}
 }

 function setWritable(taskForm) {
 	taskForm.find('#title').attr('disabled',false);
 	taskForm.find('#type').attr('disabled',false);
 	taskForm.find('#desc').attr('disabled',false);
 	taskForm.find('#state').attr('disabled',false);
 	taskForm.find('#prior').attr('disabled',false);
 	taskForm.find('#req').attr('disabled',false);
 	taskForm.find('#assign').attr('disabled',false);
 }    	  

 function getStateFromTask(task) {    	  
    	  var state = task.parent()[0].nodeName;
    	  if (state == 'WAITING') {
				state = 'waiting';    	  
    	  }
    	  if (state == 'CANSTART') {
				state = 'canStart';    	  
    	  }
    	  if (state == 'ATWORK') {
				state = 'atWork';    	  
    	  }
    	  if (state == 'CANVERIFY') {
				state = 'canVerify';    	  
    	  }
    	  if (state == 'ATVERIFY') {
				state = 'atVerify';    	  
    	  }
    	  if (state == 'CLOSED') {
				state = 'closed';    	  
    	  }
    	  return state;
  }  

  function setTaskEventHandlers() {	 
        if ($('task').draggable != undefined) {
        	$('task').draggable(); 
        }
        $('task').click(function() {
	        var members = $('members').find('member');
	    	  var id = this.id;
	    	  var task = $('#'+id);
	    	  var taskForm = $('#taskForm');
			  var state = getStateFromTask(task);	    	  
	     	
	        // copy member into taskFom user selecor'options
	        var i;
	        var s = '<option value="https://www.gravatar.com/avatar/">?</option>';
	        $('#assign').html('');
		     $('#assign').append(s); 		      
	        for (i=0; i < members.length; i++) {
	      	 s = '<option value="'+members[i].attributes.avatar.nodeValue+'">'+
		      		members[i].innerHTML+'</option>';
				 $('#assign').append(s); 		      
	        }
	
	        // load form'fields from <task>	
	    	  this.style.zIndex=1;
	    	  taskForm.find('#id').val(task.find('id').html());
	    	  taskForm.find('#title').val(task.find('title').html());
	    	  taskForm.find('#desc').val(task.find('desc').html().replace(/\<br\>/g,"\n"));
	    	  taskForm.find('#type').val(task.find('type').attr('class'));
	    	  taskForm.find('#assign').val(task.find('img').attr('src'));
	    	  taskForm.find('#req').val(task.find('req').html());
	    	  taskForm.find('#state').val(state);
	    	  oldAssign = task.find('img').attr('src');
	    	  if (!accessRight(task, false)) {
	    	  		setReadOnly(taskForm);
	    	  } else {
					setWritable(taskForm);    	  
	    	  }
	    	  if (taskForm.find('#assign').val() == loggedUser) {
					taskForm.find('#state').attr('disabled',false);    	  
	    	  }
			  $('#taskForm').toggle();
      });
      $('task').mousedown(function(){
      	atDragging = true;
			this.style.zIndex = 99;      
      })
      $('task').mouseup(function(){
      	atDragging = false;
			this.style.zIndex = 1;
      })
  }
  
  function colTranslate() {
    $('waiting').find('h2').html("<?php echo WAITING; ?>");
    $('canStart').find('h2').html("<?php echo CANSTART; ?>");
    $('atWork').find('h2').html("<?php echo ATWORK; ?>");
    $('canVerify').find('h2').html("<?php echo CANVERIFY; ?>");
    $('atVerify').find('h2').html("<?php echo ATVERIFY; ?>");
    $('closed').find('h2').html("<?php echo CLOSED; ?>");
  }
  
  function colResize() {  
    // adjust heights
	 var maxHeight = 0;	
    $('.col').css('height', '');
	 
    var cols = $('project').find('.col');
    var i;
    var col = null;
    for (i=0; i<cols.length; i++) {
    	col = $(cols[i].nodeName);
		if (col.height() > maxHeight) {
			maxHeight = col.height();		
		}    
    }
    $('.col').css('height', maxHeight+'px');
  }
  
  $(function() {
  	 
    colTranslate(); 
    colResize();
    setTaskEventHandlers();

    if ($('body').droppable != undefined) {
	 $('body').droppable({
		drop: function(event, ui) {
			// drop into body
			var scrolTop  = window.pageYOffset || document.documentElement.scrollTop;
			
			// calculate newState
			var newState;
			if (ui.offset.left > 1000) {
				newState = 'closed';
			} else if (ui.offset.left > 800) {
				newState = 'atverify';
			} else if (ui.offset.left > 600) {
				newState = 'canverify';
			} else if (ui.offset.left > 400) {
				newState = 'atwork';
			} else if (ui.offset.left > 200) {
				newState = 'canstart';
			} else {
				newState = 'waiting';
			}
			
			// calculate beforSelector
			var beforeSelector = 'h2';
			if (ui.offset.top > (scrolTop+60)) {
				var tasks = $(newState).find('task');
				var i;
				for (i=0; i < tasks.length; i++) {
					if (ui.draggable.position().top > $('#'+tasks[i].id).position().top) {
						beforeSelector = '#'+tasks[i].id;				
					}			
				}
			}

			// check, if ok process
        	if ((checkState2(ui.draggable, newState)) && (accessRight(ui.draggable, true))) {
	        	 	ui.draggable.insertAfter($(newState).find(beforeSelector));
					dbRefreshEnable = false;
		 		   clearTimeout(saveTimer);
		        	saveTimer = window.setTimeout("saveToDatabase(projectId)", 5000);
		        	if (beforeSelector == 'h2') {
						window.scrollTo(0,0);		        	
		        	}
		       	colResize();
	      }
     	 	ui.draggable.css('left','0px');
     	 	ui.draggable.css('top','0px');
		}	 
	 });
    }
    
    $('#Ok').click(function() {
      // taskForm --> task (!!! update parent !!!)
    	var taskForm = $('#taskForm');
    	var id = taskForm.find('#id').val();
    	var task = $('#'+id);
    	var newState = taskForm.find('#state').val();
    	var oldState = getStateFromTask(task);
		if (checkForm(taskForm)) { 
	      task.find('title').html(taskForm.find('#title').val());
	      task.find('desc').html(taskForm.find('#desc').val().replace(/\n/g,"<br>"));
	      task.find('type').attr('class', taskForm.find('#type').val());
		  var assign = taskForm.find('#assign').val();
		  var assignSelect = taskForm.find('#assign')[0];
		  var selectedIndex = assignSelect.selectedIndex;
		  var nick = assignSelect.options[selectedIndex].label;
	      task.find('img').attr('src',assign);
	      task.find('img').attr('title',nick);
	      task.find('req').html(taskForm.find('#req').val());
		  if (newState != oldState) {	      
				task.insertAfter($(newState).find('h2'));
	      }
	      dbRefreshEnable = false;
 		  clearTimeout(saveTimer);
       	saveTimer = window.setTimeout("saveToDatabase(projectId)", 5000);
       	colResize(); 
		}
	 	$('#savedMsg').hide();
		$('#taskForm').toggle();  
    });

    $('#cancel').click(function() {
		$('#taskForm').toggle();    
    });

    $('#deltask').click(function() {
    		if (loggedAdmin()) {
           var id = $('#taskForm').find('#id').val();
           $('#'+id).remove();
			  dbRefreshEnable = false;
 		     clearTimeout(saveTimer);
     	 	  saveTimer = window.setTimeout("saveToDatabase(projectId)", 5000);
	 	 	  $('#savedMsg').hide();
	 		  $('#taskForm').toggle();
	 		}    
    });
    
    $('#newTaskBtn').click(function() {
    	if (loggedAdmin()) {
	      var id = 1 + getIdMax();
	      var newTask = $('#taskInit').clone(false);
	      newTask.find('id').html(''+id);
	      newTask.attr('id',id);
	      newTask.insertAfter($('waiting').find('h2'));
	      setTaskEventHandlers();
		  window.scrollTo(0,0);
		  dbRefreshEnable = false;
		  clearTimeout(saveTimer);
	      saveTimer = window.setTimeout("saveToDatabase(projectId)", 5000);
	      colResize(); 
		  $('#'+id).click();
		}
    }); // newTask

	 $('#membersBtn').click(function() {
	 	var tbody = $('#membersForm tbody');
	 	var s = '';
	 	var i = 0;
	 	var members = $('members').find('member');
 		var checked = '';
	 	tbody.html('');
	 	for (i=1; i < members.length; i++) {
	 		if (members[i].attributes.admin.nodeValue == '1') {
	 			checked = ' checked=\"1\"';
	 		} else {
	 			checked = '';
	 		}
	 		var avatar = members[i].attributes.avatar.nodeValue;
	 		if (loggedAdmin() && (i > 1)) {
				s = '<tr><td><input type="checkbox" id="" value="1"+'+checked+' /></td>'+
				   '<td>'+
				   '<img src="'+avatar+'" alt="'+avatar+'" width="40" height="40" />'+
				   '<span>'+members[i].innerHTML+'</span>'+
				   '</td></tr>';
			} else {
				s = '<tr><td><input type="checkbox" disabled="disabled" id="" value=""'+checked+' /></td>'+
				   '<td>'+
				   '<img src="'+avatar+'" alt="'+avatar+'" width="40" height="40" />'+
				   '<span>'+members[i].innerHTML+'</span>'+
				   '</td></tr>';
			}   	
			tbody.append(s);
	 	}
	 	$('#membersForm tr input').click(function() {
		 	var members = $('members').find('member');
		 	var i = 0;
		 	var avatar = this.parentNode.nextSibling.firstChild.alt;
		 	// i=1 is the  creator it is admin.
			members[1].attributes.admin.nodeValue = '1';
		 	// i=0 is the guest it is not admin
			members[0].attributes.admin.nodeValue = '0';
			for (i=2; i < members.length; i++) {
				if (members[i].attributes.avatar.nodeValue == avatar) {
					if (this.checked) {
						members[i].attributes.admin.nodeValue = '1';
					} else {
						members[i].attributes.admin.nodeValue = '0';
					}				
				}		
			}			 	
 		   clearTimeout(saveTimer);
	       saveToDatabase(projectId);
	 	});
		$('#membersForm').toggle();	 
	 });

	if ($(window).unload != undefined) { 
		$(window).unload(function() {
			saveToDatabase(projectId);    
		});
	}
    
    // init application
    // ================
    
    // is set users in request then 
    // - admins merge to database,
    // - users overwrite to database
    if (users.length > 0) {
    	
	   refreshFromDatabase(projectId,  function() {
		   dbRefreshEnable = false;
		   
		   //copy admins from <members> into admins array
			var oldMembers = $('members').find('member');
			var i;
			var s = '';
			for (i=0; i < oldMembers.length; i++) {
				if (oldMembers[i].attributes.admin.nodeValue == '1') {
					admins.push(oldMembers[i].attributes.avatar.nodeValue);			
				}		
			}
			// clear <members>
			$('members').html('');
			
			// copy users into <members> (first user is admin!)
			for (i=0; i < users.length; i++) {
				if ((admins.indexOf(users[i][0]) >= 0) || (i == 0)) {
					s = '<member avatar="'+users[i][0]+'" admin="1">'+users[i][1]+'</member>';
				} else {
					s = '<member avatar="'+users[i][0]+'" admin="0">'+users[i][1]+'</member>';
				}
				$('members').append(s);
			}
		   dbSaveEnable = true;
	    	saveToDatabase(projectId);
		   dbRefreshEnable = true;
		   if (loggedAdmin()) {
		   	$('#newTaskBtn').show();
		   } else {
		   	$('#newTaskBtn').hide();
		   }
	   });
	   
    }
     
    // start refresh interval
	refreshFromDatabase(projectId);
	
  }); // pageLoad
