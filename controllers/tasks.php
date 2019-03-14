<?php
class tasksController {

	/**
	* show projectmanager main page
	* @request callerapiurl string  REQUED 
	* @request sessionid string  REQUED 
	* @request projectid string  REQUED 
	* @request lng string 'hu'|'en'     OPTIONAL
	* @request css string cssFileURL    OPTIONAL
	*
	* API hivással kéri le: loggedUser, users, admin
	*   url: <apiurl>/sessionid/projectid
	*   autput: {
	*    "users":[[avatarurl, nickName], ...],
	*    "admins":[avatarurl, ...],
	*    "loggedUser":avatarurl	
	* }
	* @return void, echo html page
	*/
	public function show($request) {
		
		// set demo params or call from callerAPI 
		$res = JSON_decode('{"users":[["https://www.gravatar.com/avatar/2c0a0e6e2dc8b37f24ddb47dfb7e3eb5","utopszkij"],
							               ["./images/user1.png","user1"],
					                     ["./images/user2.png","user2"]
					                    ],
		"admins":["https://www.gravatar.com/avatar/2c0a0e6e2dc8b37f24ddb47dfb7e3eb5"],
		"loggedUser":""
		}');
		if ($request->input('projectid') == 'demo1') {
			$res->loggedUser = $res->admins[0];
		} else if ($request->input('projectid') == 'demo2') {
			$res->loggedUser = $res->users[1];
		} else if ($request->input('projectid') == 'demo3') {
			$res->loggedUser = '';
		} else if (($request->input('projectid') != '') && ($request->input('callerapiurl') != '')) {	
			$lines = file($request->input('callerapiurl').
			  '/'.$request->input('sessionid','0').
			  '/'.$request->input('projectid'.'0'));
			$res = JSON_decode(implode("",$lines));
      }
		// forward params into viewer
		$p = new stdClass();
		$p->projectId = $request->input('projectid','0000');
		$p->users = $res->users;
		$p->admins = $res->admins;
		$p->loggedUser = $res->loggedUser;
		$p->lng = $request->input('lng','hu');
		$p->extraCSS = $request->input('css','');
		
		// call viewer
		$view = getView('tasks');
		$view->show($p);
	}
	
	/**
	* refresh tasks from database AJAX backend server
	* @request projectId string  REQUED
	* @request fileTime number   REQUED
	* @return void, 
	*     echo json {"fileTime".num} vagy {"fileTime".num, "project":XMLstr}  
	*/
	public function refresh($request) {
		$projectId = $request->input('projectId','0000');
		$fileTime = $request->input('fileTime',0);

		$model = getModel('tasks');
	   header('Content-Type: json');
		echo $model->refresh($projectId, $fileTime);
		exit();
	}
	
	/**
	* save tasks into database AJAX backend server
	* @request projectId string  REQUED
	* @request project XMLstr    REQUED
	* @return void, 
	*     echo json {"fileTime".num}  
	*/
	public function save($request) {
		$projectId = $request->input('projectId','0000');
		$project = $request->input('project','');
		$model = getModel('tasks');
	   header('Content-Type: json');
		echo $model->save($projectId, $project);
		exit();
	} 
}
?>