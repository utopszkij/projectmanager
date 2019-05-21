<?php
class TasksView {
	/**
	* echo html page
	* @param object $p
	* - extraCSS
	* - loggedUser
	* - projectId
	* @return void
	*/
	public function show($p) {
	    ?>    
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=1240px, initial-scale=1">
  <title>projektmanager</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="./style.css">
  <?php if ($p->extraCSS != '') :?>
	  <link rel="stylesheet" href="<?php echo $p->extraCSS; ?>">
  <?php endif; ?>
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script type="text/javascript">
	var global = {};
	global.alert = function(str) {
		// window.alert(str);
		$("#popupYes").hide(); 
		$("#popupNo").hide(); 
		$("#popupClose").show();
		$("#popup p").html(str);
		$("#popup").show(); 
		$("#popupClose").click(function(){
			$("#popup").hide();
		});
	};
	global.confirm = function(str, yesfun, nofun) {
		if (yesfun != undefined) {
			$("#popupYes").mouseup(yesfun);
		} else {
			$("#popupYes").mouseup(function() {});
		}
		if (nofun != undefined) {
			$("#popupNo").mouseup(nofun);
		} else {
			$("#popupNo").mouseup(function() {});
		}	
		$("#popupYes").show(); 
		$("#popupNo").show(); 
		$("#popupClose").hide();
		$("#popup p").html(str);
		$("#popupNo").click(function(){
			$("#popup").hide();
		});
		$("#popupYes").click(function(){
			$("#popup").hide();
		});
		$("#popup").show(); 
	};
	global.post = function(url, options, fun) {
		$.post(url, options, fun);
	}	
  </script>
  <?php loadJavaScript('tasks',$p); ?>	
</head>
<body>
    <div id="popup" style="display:none; z-index:20; position:fixed;
        top:100px; left:100px; width:auto; max-width:70%; height:auto;
        background-color:#f0f0f0; padding:10px; margin:5px;
        border-style:solid; border-width:1px; text-align:center">
		<p class="alert alert-danger"></p>
		<div>
			<button type="button" id="popupYes"><?php echo txt('YES'); ?></button>
			<button type="button" id="popupNo"><?php echo txt('NO'); ?></button>
			<button type="button" id="popupClose"><?php echo txt('CLOSE'); ?></button>
		</div>
    </div>
	<div id="buttonLine">
		<button type="button" class="" title="newTask" id="newTaskBtn">+ Új feladat</button>
		&nbsp;
		<button type="button" class="" title="members" id="membersBtn">Tagok</button>
		<?php if($p->projectId == 'demo') : ?>
		<img src="<?php echo $p->loggedUser; ?>" 
			width="35" style="z-index:10; float:right; margin:2px;" alt="" />
		<?php endif; ?>	
	</div>

	<!-- data from database start php olvassa be a kapott projectId alapján -->
	<div id="database">
		<project>
			<waiting class="col">
				<h2>waiting</h2>
			</waiting>
			<canStart class="col">
				<h2>canStart</h2>
			</canStart>
			<atWork class="col">
				<h2>atWork</h2>
			</atWork>
			<canVerify class="col">
				<h2>canVerify</h2>
			</canVerify>
			<atVerify class="col">
				<h2>atVerify</h2>
			</atVerify>
			<closed class="col">
				<h2>closed</h2>
			</closed>
			<members>
			</members>
		</project>
	</div>
	<!-- data from database end -->

	<div class="clear"></div>
	<div id="info"><?php echo txt('INFO'); ?></div>
	<div style="display:none">
		<task id="taskInit">
			<id>01</id>
			<title></title>
			<desc></desc>
			<type class="question"></type>
			<assign><img src="https://www.gravatar.com/avatar/" title="?" alt="" /></assign>
			<req></req>
		</task>
	</div>"
	<div id="taskForm">
		<form>
		<div>
			<label>Task ID:</label>
			<input type="text" disabled="disabled" id="id" value="01" />
		</div>
		<div>
			<label><?php echo txt('TITLE'); ?> :</label>
			<input type="text" id="title" />
		</div>
		<div>
			<label><?php echo txt('DESC'); ?> :</label>
			<textarea id="desc" cols="80" rows="6"></textarea>
		</div>
		<div>
			<fieldset>
				<legend></legend>
				<label><?php echo txt('TYPE'); ?> :</label>
				<select id="type">
					<option value="task"><?php echo txt('TASK'); ?></option>
					<option value="question"><?php echo txt('QUESTION'); ?></option>
					<option value="bug"><?php echo txt('BUG'); ?></option>
					<option value="suggest"><?php echo txt('SUGGEST'); ?></option>
					<option value="other"><?php echo txt('OTHER'); ?></option>
				</select>
			</fieldset>
			<fieldset>
				<legend></legend>
				<label><?php echo txt('ASSIGN'); ?> :</label>
				<select id="assign">
					<option value="https://www.gravatar.com/avatar/">?</option>			
				</select>
			</fieldset>
		</div>
		<div>	
			<fieldset>
				<legend></legend>
				<label><?php echo txt('STATE'); ?> :</label>
				<select id="state">
					<option value="waiting"><?php echo txt('WAITING'); ?></option>
					<option value="canStart"><?php echo txt('CANSTART'); ?></option>
					<option value="atWork"><?php echo txt('ATWORK'); ?></option>
					<option value="canVerify"><?php echo txt('CANVERIFY'); ?></option>
					<option value="atVerify"><?php echo txt('ATVERIFY'); ?></option>
					<option value="closed"><?php echo txt('CLOSED'); ?></option>
				</select>
			</fieldset>
		</div>
		<div>
			<label><?php echo txt('REQ'); ?> <br>( <?php echo txt('REQHELP'); ?> ):</label>
			<input type="text" id="req" style="width:290px" />
		</div>
		<div style="text-align:center">
			<button type="button" id="Ok"><?php echo txt('OK'); ?></button>&nbsp;
			<button type="button" id="cancel"><?php echo txt('CANCEL'); ?></button>&nbsp;&nbsp;&nbsp;
			<button type="button" id="deltask"><?php echo txt('DELTASK'); ?></button>
		</div>
		</form>
	</div> <!-- taskForm -->
	
	<div id="membersForm" style="display:none">
		<p style="text-align:right">
			<button type="button" onclick="$('#membersForm').toggle();" title="close">X</button>		
		</p>
		<h3><?php echo txt('MEMBERS'); ?></h3>
		<table style="width:100%">
		<thead>
			<tr><th float="left" style="width:100px"><?php echo txt('ADMIN'); ?></th><th></th></tr>
		</thead>
		<tbody>
		</tbody>
		</table>
	</div>
	
</body>
</html>
<?php 		
	}
}
?>