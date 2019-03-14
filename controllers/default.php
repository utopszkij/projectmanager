<?php
class defaultController {
	public function default($request) {
		
		// set default params
		$request->set('projectid','demo1');
		$request->set('sessionid','0');
		$request->set('lng','hu');

		// call default task
		include_once './controllers/tasks.php';
		$ctrl = new tasksController();
		$ctrl->show($request); 
	}
}
?>