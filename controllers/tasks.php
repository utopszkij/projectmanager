<?php
class TasksController {

    protected function setHtmlHeader() {
        if (!headers_sent()) {
            header('Content-Type: json');
        }
    }
    
	/**
	* show projectmanager main page
	* @param Request $request
	* - callerapiurl string  REQUED 
	* - sessionid string  REQUED 
	* - projectid string  REQUED 
	* - lng string 'hu'|'en'     OPTIONAL
	* - css string cssFileURL    OPTIONAL
	*
	* API hivással kéri le: loggedUser, users, admin
	*   url: <apiurl>/sessionid/projectid
	*   autput: {
	*    "users":[[avatarurl, nickName], ...],
	*    "admins":[avatarurl, ...],
	*    "loggedUser":avatarurl	
	* }
	* @return void
	* - echo html page
	*/
	public function show(Request $request) {
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
			$request->set('projectid','demo');
		} else if ($request->input('projectid') == 'demo2') {
			$res->loggedUser = $res->users[1][0];
			$request->set('projectid','demo');
		} else if ($request->input('projectid') == 'demo3') {
			$res->loggedUser = './images/guest.jpg';
			$request->set('projectid','demo');
		} else if (($request->input('projectid') != '') && ($request->input('callerapiurl') != '')) {
		    // get infos from caller api
		    $options = array(
		        CURLOPT_URL => $request->input('callerapiurl').
		          '/'.$request->input('sessionid','0').
		          '/'.$request->input('projectid','0'),
		        CURLOPT_HEADER => 0,
		        CURLOPT_RETURNTRANSFER => TRUE,
		        CURLOPT_TIMEOUT => 4
		    );
		    $ch = curl_init();
		    curl_setopt_array($ch, $options);
		    if( ! $lines = curl_exec($ch)) {
		        trigger_error(curl_error($ch));
		    }
		    curl_close($ch); 
			$res = JSON_decode($lines);
        }
        if (!isset($res->loggedUser)) {
            $res = new stdClass();
            $res->loggedUser = '';
            $res->admins = array();
            $res->users = array();
        }
        // store users, admins, loggedUser info into session

        $request->sessionSet('loggedUser', $res->loggedUser);
        $request->sessionSet('admins', $res->admins);
        $request->sessionSet('users', $res->users);
        
		// forward params into viewer
		$p = new stdClass();
		$p->projectId = $request->input('projectid','0000');
		$p->users = $res->users;
		$p->admins = $res->admins;
		$p->loggedUser = $res->loggedUser;
		$p->lng = $request->input('lng','hu');
		$p->extraCSS = $request->input('css','');
		$p->REFRESHMIN = REFRESHMIN;
		$p->REFRESHMAX = REFRESHMAX;
		$p->SESSIONCOUNT = $request->session_count();
		
		// call viewer
		$view = getView('tasks');
		$view->show($p);
	}
	
	/**
	* refresh tasks from database AJAX backend server
	* @param Request $request
	* - projectId string  REQUED
	* - fileTime number   REQUED
	* @return void, 
	*     echo json {"fileTime":num} vagy {"fileTime":num, "project":jsonStr}  
	*/
	public function refresh(Request $request) {
		$projectId = $request->input('projectid','0000');
		$fileTime = $request->input('fileTime',0);

		$model = getModel('tasks');
		if (!headers_sent()) {
		    header('Content-Type: json');
		}
		echo $model->refresh($projectId, $fileTime);
	}
	
	/**
	* save complete project into database AJAX backend server
	* @param Request $request
	* - string projectid REQUED
	* - jsonStr project   REQUED
	* @return void, 
	*     echo json {"fileTime":num, "errorMsg":""}  
	*/
	public function save(Request $request) {
	    $this->setHtmlHeader();
		$projectId = $request->input('projectid','0000');
		$project = $request->input('project','');
		$model = getModel('tasks');
		echo $model->save($request, $projectId, $project);
	} 
	
	/**
	 * insert new default task AJAX backend server
	 * @param Request $request
	 * - string projectid
	 * - string id
	 * - string sid
	 * @return void
	 */
	public function taskinsert(Request $request) {
	    $this->setHtmlHeader();
	    $projectId = $request->input('projectid','0000');
	    $id = $request->input('id','0');
	    $model = getModel('tasks');
	    echo $model->newTask($projectId, $id);
	}
	
	/**
	 * delete one task  AJAX backend server
	 * @param Request $request
	 * - string projectid
	 * - string id     taskId
	 * - string sid
	 * @return void
	 */
	public function taskdelete(Request $request) {
	    $this->setHtmlHeader();
	    $projectId = $request->input('projectid','0000');
	    $id = $request->input('id','0');
	    $model = getModel('tasks');
	    echo $model->delTask($request, $projectId, $id);
	}
		
	/**
	 * update one task AJAX backend server
	 * @param Request $request
	 * - string projectid
	 * - string data task json string
	 * - string state
	 * - string sid
	 * @return void
	 */
	public function taskupdate(Request $request) {
	    $this->setHtmlHeader();
	    $projectId = $request->input('projectid','0000');
	    $state = $request->input('state','waiting');
	    $data = $request->input('data','{"id":""}');
	    $dataObj = JSON_decode($data);
	    $dataObj->state = $state;
	    $dataObj->project_id = $projectId;
	    $model = getModel('tasks');
	    echo $model->updateTask($request, $projectId, $dataObj);
	}
}
?>