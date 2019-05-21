<?php

class Task {
     public $id = 0;
     public $title = '';
     public $desc = '';
     public $req = '';
     public $assign = '';
     public $type = '';
}


class TasksModel {

    /**
     * error Message
     * @var string
     */
    protected $errorMsg = '';

    function __construct() {
        $db = new DB();
        $db->statement('
        CREATE TABLE IF NOT EXISTS tasks (
                `id` int(11) unsigned NOT NULL,
                `title` varchar(128) NOT NULL DEFAULT "" COMMENT "task shoer title",
                `desc` text NOT NULL COMMENT "task description",
                `req` varchar(128) NOT NULL DEFAULT "" COMMENT "list of taskIDs",
                `assign` varchar(128) NOT NULL DEFAULT "" COMMENT "user avatar url",
                `type` varchar(16) NOT NULL DEFAULT "" COMMENT "bug|question|suggest|other",
                `state` varchar(16) NOT NULL DEFAULT "waiting" COMMENT "waiting|atstart|inworking|...",
                `project_id` varchar(128) NOT NULL DEFAULT "" COMMENT "project id ",
                KEY `idx_project` (`project_id`),
                KEY `idx_type` (`type`),
                KEY `idx_assign` (`assign`)
          )
        ');
        $db->statement('
        CREATE TABLE IF NOT EXISTS lastupdate (
                `time` int(11)
          )
        ');
        
    }
    
    /**
     * chek loggedUser is member?
     * @param string $loggedUser
     * @param array $users [[avatar, nick],...]
     * @return boolean
     */
    protected function isMember(string $loggedUser, array $users): bool {
        $result = false;
        for ($i=0; $i<count($users); $i++) {
            if ($users[$i][0] == $loggedUser) {
                $result = true;
            }
        }
        return $result;
    }

    /**
	* refresh tasks from database
	* @param string $projectId REQUED
	* @param integer $fileTime REQUED
	* @return string jsonStr 
	* - {"fileTime":num} vagy {"fileTime":num, "project":JsonStr}  
	*/
    public function refresh($projectId, $fileTime): string {
        $result = new stdClass();
		$result->fileTime = 0;
		
		// get lastUpdate time
		$table = DB::table('lastupdate');
		$res = $table->first();
		if ($res) {
		    $result->fileTime = $res->time;
		} else {
		    $record = new stdClass();
		    $record->time = time();
		    $table->insert($record);
		    $result->time = $record->time;
		}
		
		// get data from databases if database updated
		$table = DB::table('tasks');
		if ($result->fileTime > $fileTime) {
    		$result->project = new stdClass();
    		$tasks = $table->where(array('project_id','=',$projectId))->get();
    		foreach ($tasks as $task) {
    		    $state = $task->state;
    		    if (!isset($result->project->$state)) {
    		      $result->project->$state = array();
    		    }
    		    $result->project->$state[] = $task;
    		}
    		$table = DB::table('members');
    		$members = $table->where(array('project_id','=',$projectId))->get();
    		$result->project->members = array();
    		foreach ($members as $member) {
    		    $result->project->members[] = $member;
    		}
		}
	    return JSON_encode($result);
	}
	
	/**
	 * get tasks from project json string
	 * @param string $s
	 * @return array of task
	 */
	protected function getTasks(string $s): array {
	    $result = array();
	    $project = JSON_decode($s);
	    foreach ($project as $stateName => $stateObj) {
	        foreach ($stateObj as $task) {
	            if (isset($task->title)) {
	                $task->state = $stateName;
	                $result[] = $task;
	            }
	        }
	    }
	    return $result;
	}
	
	
	/**
	 * find task from tasks array
	 * @param array of task objects $tasks
	 * @param Task $task
	 * @return Task object or false  
	 */
	protected function findTask(array $tasks, $task) {
	   $result = false;
       foreach ($tasks as $i => $t) {
          if ($t->id == $task->id) {
             $result = $tasks[$i];
          }
       }
       return $result;
	}
	

    /**
    * csak state és assign változott?
    * @param object $newTask
    * @param object $oldTask
    * @return bool
    */
    protected function ContentNotChange($newTask, $oldTask): bool {
        $result = true;
        if ($oldTask) {
            if ($newTask->id  !=  $oldTask->id) {
                $result = false;
            }
            if ($newTask->title !=  $oldTask->title) {
                $result = false;
            }
            if ($newTask->desc !=  $oldTask->desc) {
                $result = false;
            }
            if ($newTask->type  !=  $oldTask->type) {
                $result = false;
            }
            if ($newTask->req  !=  $oldTask->req) {
                $result = false;
            }
            if (!$result) {
                $this->errorMsg = txt('CONTENTCHANGE');
            }
        }
        return $result;
    }
    
    /**
     * chack changed task ?
     * @param Task $oldTask
     * @param Task $newTask
     * @return bool
     */
    protected function noChanged($oldTask, $newTask): bool {
        $result = $this->contentNotChange($newTask, $oldTask);
        if ($oldTask) {
            if ($newTask->state != $oldTask->state) {
                $result = false;
            }
            if ($newTask->assign != $oldTask->assign) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * public add enabled this task type?
     * @param Task|false $oldTask
     * @param Task $newTask
     * @param string $freeAssign
     * @return bool
     */
    protected function publicEnabledNewtask($oldTask, $newTask, string $freeAssign): bool {
        $result = false;
        if ((!$oldTask) &&
            (($newTask->type == 'suggest') || ($newTask->type == 'query') || ($newTask->type == 'bug')) &&
            ($newTask->state == 'waiting') &&
            ($newTask->assign == $freeAssign)) {
                $result = true;
        }
        return $result;            
    }

    /**
     * check this action picup free task into loggedUser?
     * @param Task|false $oldTask
     * @param Task $newTask
     * @param string $loggedUser
     * @param bool $member
     * @param string $freeAssign
     * @return bool
     */
    protected function pickupTask($oldTask, $newTask, 
        string $loggedUser, 
        bool $member, 
        string $freeAssign): bool {
        $result = false;
        if (($oldTask && $member) &&
            ($oldTask->assign == $freeAssign) &&
            ($newTask->assign == $loggedUser) &&
            ($this->contentNotChange($newTask, $oldTask ))
           ) {
            $result = true;
        }
        return $result;                
    }

    /**
     * check this action only state change and assign == loggedUser ?
     * @param Task|false $oldTask
     * @param Task $newTask
     * @param string $loggedUser
     * @param bool $member
     * @return bool
     */
    protected function selfTaskStateChange($oldTask, $newTask, 
        string $loggedUser, bool $member): bool {
        $result = false;
        if (($oldTask && $member) &&
            ($oldTask->assign == $loggedUser) &&
            ($newTask->assign == $loggedUser) &&
            ( $this->contentNotChange($newTask, $oldTask ))) {
            $result = true;
        }
        return $result;        
    }
    
    
	/**
	 * check task action access right
	 * @param Task $newTask
	 * @param Task $oldTask
	 * @param bool $member
	 * @param string $loggedUser
	 * @return bool
	 */
	protected function checkTaskAction($newTask, $oldTask, bool $member, string $loggedUser): bool {
             $result = false;
             $freeAssign = 'https://www.gravatar.com/avatar/';
             $workErrorMsg = txt('CHECKERROR');
             
             //  ha semmi nem változott akkor OK
             $result = $this->noChanged($oldTask, $newTask);
             
             // új javaslatot, kérdést, hibajelzést bárki felvihet
             if (!$result) {
                 $result = $this->publicEnabledNewtask($oldTask, $newTask, $freeAssign);
             }

            // eddig szabad feladatot tag magához veheti, state változhat
             if (!$result) {
                 $result = $this->pickupTask($oldTask, $newTask, $loggedUser, $member, $freeAssign);
             }
             
            // magához rendelt feladat, csak state változtatás
             if (!$result) {
                 $result = $this->selfTaskStateChange($oldTask, $newTask, $loggedUser, $member);
             }

            // magához rendeltet elengedheti ha state canverify
            if ((!$result) && ($oldTask && $member) && ($oldTask->assign == $loggedUser) &&
                ($newTask->assign == $freeAssign) && ($newTask->state == 'canverify') &&
                ($this->contentNotChange($newTask, $oldTask) )) {
                $result = true;
            }
            if (($workErrorMsg != '') && (!$result)) {
                $this->errorMsg = $workErrorMsg;
            }
            return $result;    
	}
	
	
	/**
	 * Minden newtask -ra ellenörzi, hogy a loggeduser jososult-e erre a modositásra?
	 * @param array $newTasks array of Task
	 * @param array $oldTasks array of Task
	 * @param bool $member
	 * @param string $loggedUser
	 * @return bool
	 */
	protected function accesRightNewTaskUpdate(array $newTasks, array $oldTasks, 
	    bool $member, string $loggedUser): bool {
	    $result = true;
	    foreach($newTasks as $newTask) {
	        $oldTask = $this->findTask($oldTasks, $newTask);
	        if (!$this->checkTaskAction($newTask,$oldTask, $member, $loggedUser)) {
	            $result = false;
	        }
	    }
	    return $result;
	}
	    
	/**
	 * task törlés nem megengedett
	 * @param array $newTasks array of Task
	 * @param array $oldTasks array of Task
	 * @return bool (true - ok nem történik törlés, false- neme jó törlés történne)
	 */
	protected function doDeletedTask(array $newTasks, array $oldTasks): bool {
	    $result = true;
	    foreach($oldTasks as $oldTask) {
	        $newTask = $this->findTask($newTasks, $oldTask);
	        if (!$newTask) {
	            $this->errorMsg = txt('ACCESSDENIED');
	            $result = false;
	        }
	    }
	    return $result;
	}
	    
	/**
	 * load oldTasks from database by projectId
	 * @param string $projectId
	 * @return array of Task
	 */
	protected function loadOldTasks(string $projectId):  array {
	    $oldTasks = array();
	    $res = $this->refresh($projectId, 0);
	    if ($res) {
	        $pr = JSON_decode($res);
	        if (isset($pr->project)) {
	            foreach ($pr->project as $fn => $state) {
	                if ($fn != 'members') {
	                    foreach ($state as $task) {
	                        $oldTasks[] = $task;
	                    }
	                }
	            }
	        }
	    }
	    return $oldTasks;
	}
	
		
	/**
	 * @param bool $accessRight I/O
	 * @param int|string $projectId
	 * @param object $project
	 * @return void
	 */
	protected function saveCheckNoAdmin(bool & $accessRight, & $request, $projectId, $project) {
	    $member = $this->isMember($request->sessionGet('loggedUser'),$request->sessionGet('users'));
	    $newTasks = $this->getTasks($project);
	    
	    // load $pldTasks array
	    $oldTasks = $this->loadOldTasks($projectId);
	    
	    // megnézzük minden newTask -ot, hogy a user jogosult-e erre a modositásra?
	    $loggedUser = $request->sessionGet('loggedUser');
	    if (!$this->accesRightNewTaskUpdate($newTasks, $oldTasks, $member, $loggedUser)) {
	        $accessRight = false;
	    }
	    
	    // megnézzük történne-e task törlés - ez itt ugyanis nem megengedett
	    if (!$this->doDeletedTask($newTasks, $oldTasks)) {
	        $accessRight = false;
	    }
	    
	}
	
	/**
	 * save all task from project json string
	 * @param string $projectId
	 * @param string $project json string
	 * @return void
	 */
	protected function saveAllTaskFromJson(string $projectId, string $project) {
	    $pr = JSON_decode($project);
	    $table = DB::table('tasks');
	    $table->where(array('project_id','=',$projectId))->delete();
	    foreach ($pr as $fn => $state) {
	        if ($fn != 'members') {
	            foreach ($state as $task) {
	                $task->state = $fn;
	                $task->project_id = $projectId;
	                $table->insert($task);
	            }
	        }
	    }
	}
	
	/**
	 * set lastUpdate time into database
	 * @return integer latUpdateTime
	 */
	protected function setLastUpdate(): int {
	    $table = DB::table('lastupdate');
	    $record = $table->first();
	    if ($record) {
	        $record->time = time();
	        $table->update($record);
	    } else {
	        $record = new stdClass();
	        $record->time = time();
	        $table->insert($record);
	    }
	    return $record->time;
	}
	
		
	/**
	* save tasks into database 
	* @param int|string $projectId  REQUED
	* @param project jsonStr REQUED
	* @return string jsonStr 
	* - {"fileTime":num}  
	*/
	public function save(& $request, $projectId, $project): string {
	    $result = new stdClass();
	    $accessRight = true;
		$this->errorMsg = '';
		if ($request->sessionGet('loggedUser') == '') {
		    return '{"fileTime":0, "errorMsg":"'.txt('WRONGSESSION').'"}';
		}
		if (!in_array($request->sessionGet('loggedUser'), $request->sessionGet('admins'))) {
		    $accessRight = $this->saveCheckNoAdmin($accessRight, $request, $projectId, $project);
		}
		if ($accessRight) {
		    $this->saveAllTaskFromJson($projectId, $project);
		    $membersModel = getModel('members');
		    $membersModel->saveAllMembersFromJson($projectId, $project);
		}
		
		$result->fileTime = $this->setLastUpdate();
		
		$result->errorMsg = $this->errorMsg;
		return JSON_encode($result);
	} 
	
	/**
	 * create one default new task into database
	 * @param string $projectId
	 * @param int $Id
	 * @return string {"errorMsg":"..."}
	 */
	public function newTask(string $projectId, int $id): string {
	    $table = DB::table('tasks');
	    $task = JSON_decode('{"id":'.DB::quote($id).',
        "project_id":'.DB::quote($projectId).',
        "title":"",
        "desc":"",
        "req":"",
        "assign":"https://www.gravatar.com/avatar/",
        "type":"other",
        "state":"waiting"
        }');
	    $table->insert($task);
	    $result = '{"errorMsg":"'.$table->getErrorMsg().'"}';
	    
	    // change lastUpdate
	    $this->setLastUpdate();
	    
	    return $result;
	}
	
    /**
     * delete one task from database + accesRight control
     * @param Request objerct $request
     * @param string $projectId
     * @param int $Id
	 * @return string {"errorMsg":"..."}
     */
	public function delTask(& $request, string $projectId, int $id): string {
	    if ($request->sessionGet('loggedUser') == '') {
	        return '{"errorMsg":"'.txt('WRONGSESSION').'"}';
	    }
	    if (!in_array($request->sessionGet('loggedUser'), $request->sessionGet('admins'))) {
	        return '{"errorMsg":"'.txt('ACCESSDENIED').'"}';
	    }
	    $table = DB::table('tasks');
	    $table->where(array('project_id','=',$projectId))
	    ->where(array('id','=',$id))
	    ->delete();
	    $result = '{"errorMsg":"'.$table->getErrorMsg().'"}';
	       
    	// change lastUpdate
	    $this->setLastUpdate();
	    
	    return $result;
	}
	
    /**
     * update ont task
     * @param Request $request
     * @param string $projectId
     * @param object $task
     * @return string {"errorMsg":"..."}
     */
	public function updateTask(& $request, string $projectId, $task): string {
	    $task->state = strtolower($task->state);
	    if ($task->assign == '') {
	        $task->assign = 'https://www.gravatar.com/avatar/';
	    }
	    if ($request->sessionGet('loggedUser') == '') {
	        $result =  '{"errorMsg":"'.txt('WRONGSESSION').'"}';
	    } else {
	       $table = DB::table('tasks');
           $table->where(array('project_id','=',$projectId))->where(array('id','=',$task->id));
	       $oldTask = $table->first();
	       if ($oldTask === false) {
	            $result = '{"errorMsg":"'.txt('TASKNOTFOUND').'"}';
	       } else {
    	       if ($this->noChanged($oldTask, $task)) {
    	            $result = '{"errorMsg":""}';
    	       } else {
        	       $loggedUser = $request->sessionGet('loggedUser');
        	       $member = $this->isMember($loggedUser, $request->sessionGet('users'));
        	       if ((!in_array($request->sessionGet('loggedUser'), $request->sessionGet('admins'))) && 
        	           (!$this->checkTaskAction($task, $oldTask, $member, $loggedUser))) {
        	               $result = '{"errorMsg":"'.$this->errorMsg.'"}';
        	       } else {
        	               $table->update($task);
        	               $result = '{"errorMsg":"'.$table->getErrorMsg().'"}';
        	               
        	               // change lastUpdate
        	               $this->setLastUpdate();
        	       } // access right?
    	       } // task record changed?
	       } // oldTask found?
	    } // set loggedUser?
	    return $result;
	}
	
} // class
?>