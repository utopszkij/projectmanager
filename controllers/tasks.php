<?php
class TasksController {

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
		return;
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
	* save tasks into database AJAX backend server
	* @param Request $request
	* - string projectid REQUED
	* - jsonStr project   REQUED
	* @return void, 
	*     echo json {"fileTime":num, "errorMsg":""}  
	*/
	public function save(Request $request) {
		$projectId = $request->input('projectid','0000');
		$project = $request->input('project','');
		$model = getModel('tasks');
		if (!headers_sent()) {
		    header('Content-Type: json');
		}
		echo $model->save($projectId, $project);
	} 
	
	/**
	 * Add new bug, query or suggest iframe module
	 * @param Request $request
	 *   - projectid
	 *   - title
	 *   - desc
	 *   - type 'bug' | 'query' | 'suggest'
	 *   - email
	 * @return void
	 */
	public function addTask(Request $request) {
	    $projectId = $request->input('projectid');
	    $model = getModel('tasks');
	    $res = JSON_decode($model->refresh($projectId, 0));
	    $project = $res->project;
	    $newTask = new stdClass();
	    
	    // új task.id meghatározása
	    $newTask->id = 0;
	    foreach ($project as $state) {
	        foreach ($state as $task) {
	            if (isset($task->id)) {
	                if ($task->id > $newTask->id) {
	                    $newTask->id = $task->id;
	                }
	            }
	        }
	    }
	    
	    $newTask->id++;
	    $newTask->title = $request->input('title');
	    $newTask->desc = $request->input('desc').
	      '<span class="email">'.$request->input('email').'</span>';
	    $newTask->type = $request->input('type');
	    $newTask->assign = 'https://www.gravatar.com/avatar/';
	    $newTask->req = '';
	    $project->waiting[] = $newTask;
	    $model->save($projectId, JSON_encode($project));
	    echo '<html>
            <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=1240px, initial-scale=1">
            <title>projektmanager</title>
            </head>
            <body>
            <div>
            <p style="text-align:center; margin: 20px">
            Message saved. Thanks.
            </p>
            </div>
            </body>
            </html>';
	}
	
	/**
	 * send new ticket form run in iframe
	 * @param Request $request
	 * - projectid
	 * @return void
	 */
	public function newTicket(Request $request) {
	    echo '<html>
            <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=1240px, initial-scale=1">
            <title>projektmanager</title>
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
            </head>
            <body>
            <div style="margin:5px; padding:5px;">
            <form id="newTicketForm" method="post" action="./app.php">
                <input type="hidden" name="option" value="tasks" /> 
                <input type="hidden" name="task" value="addTask" /> 
                <input type="hidden" name="projectid" value="'.$request->input('projectid').'" />
                <h2>New error ticket, question or suggest</h2>
                <p><label>Type</label><br />                
                <select name="type">
                    <option value="bug">Bug</option>
                    <option value="question">Question</option>
                    <option value="suggest">Suggest</option>
                   </select></p> 
                <p><label>Title</label><br />                
                <input type="text" name="title" value="" style="width:400px" /></p>
                <p><label>Description</label><br />                
                <textarea name="desc" cols="40" rows="5"></textarea></p>
                <p><label>E-mail</label><br />                
                <input type="text" name="email" value="" style="width:400px" /></p>
                <p class="buttonLine">
                    <button type="submit" class="btn btn-primary">SEND</button>
                </p>
            </form>
            </div>
            </body>
            </html>';
	}
}
?>