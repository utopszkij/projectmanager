<?php
class TasksView {
	/**
	* echo html page
	* @param {users:[[avatar,nick], ...], mebers:[avatar], loggerUser:avatar, lng:hu, extraCSS:url}
	*/
	public function show($p) {
	    if (!defined('LNGDEF')) {
	        include './langs/'.$p->lng.'.php'; 	
	    }
		?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>projektmanager</title>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="./style.css">
  <?php if ($p->extraCSS != '') :?>
	  <link rel="stylesheet" href="<?php echo $p->extraCSS; ?>">
  <?php endif; ?>
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script type="text/javascript">
	var global = {};
	var global.alert = function(str) {
			window.alert(str);
		};
	var global.confirm = function(str, yesfun, nofun) {
			var res = wondow.confirm(str);
			if (res & (yesfun != undefined)) {
				yesfun();
			} 
			if (!res & (nofun != undefined)) {
				nofun();
			} 
		};	
  </script>
  <?php loadJavaScript('tasks',$p); ?>	
</head>
<body>
	<div id="buttonLine">
		<button type="button" class="" title="newTask" id="newTaskBtn">+ Új feladat</button>
		&nbsp;
		<button type="button" class="" title="members" id="membersBtn">Tagok</button>
		<img src="<?php echo $p->loggedUser; ?>" 
			width="35" style="z-index:10; float:right; margin:2px;" alt="" />
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
	<div id="info"><?php echo INFO; ?></div>
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
			<label><?php echo TITLE; ?> :</label>
			<input type="text" id="title" />
		</div>
		<div>
			<label><?php echo DESC; ?> :</label>
			<textarea id="desc" cols="80" rows="6"></textarea>
		</div>
		<div>
			<fieldset>
				<legend></legend>
				<label><?php echo TYPE; ?> :</label>
				<select id="type">
					<option value="question"><?php echo QUESTION; ?></option>
					<option value="bug"><?php echo BUG; ?></option>
					<option value="suggest"><?php echo SUGGEST; ?></option>
					<option value="other"><?php echo OTHER; ?></option>
				</select>
			</fieldset>
			<fieldset>
				<legend></legend>
				<label><?php echo ASSIGN; ?> :</label>
				<select id="assign">
					<option value="https://www.gravatar.com/avatar/">?</option>			
				</select>
			</fieldset>
		</div>
		<div>	
			<fieldset>
				<legend></legend>
				<label><?php echo STATE; ?> :</label>
				<select id="state">
					<option value="waiting"><?php echo WAITING; ?></option>
					<option value="canStart"><?php echo CANSTART; ?></option>
					<option value="atWork"><?php echo ATWORK; ?></option>
					<option value="canVerify"><?php echo CANVERIFY; ?></option>
					<option value="atVerify"><?php echo ATVERIFY; ?></option>
					<option value="closed"><?php echo CLOSED; ?></option>
				</select>
			</fieldset>
		</div>
		<div>
			<label><?php echo REQ; ?> <br>( <?php echo REQHELP; ?> ):</label>
			<input type="text" id="req" style="width:290px" />
		</div>
		<div style="text-align:center">
			<button type="button" id="Ok"><?php echo OK; ?></button>&nbsp;
			<button type="button" id="cancel"><?php echo CANCEL; ?></button>&nbsp;&nbsp;&nbsp;
			<button type="button" id="deltask"><?php echo DELTASK; ?></button>
		</div>
		</form>
	</div> <!-- taskForm -->
	
	<div id="membersForm" style="display:none">
		<p style="text-align:right">
			<button type="button" onclick="$('#membersForm').toggle();" title="close">X</button>		
		</p>
		<h3><?php echo MEMBERS; ?></h3>
		<table style="width:100%">
		<thead>
			<tr><th float="left" style="width:100px"><?php echo ADMIN; ?></th><th></th></tr>
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