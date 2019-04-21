<?php

/*
 interface Task {
     public const id = 0;
     public const title = '';
     public const desc = '';
     public const req = '';
     public const assign = '';
     public const type = '';
}
*/

class Task {
     public $id = 0;
     public $title = '';
     public $desc = '';
     public $req = '';
     public $assign = '';
     public $type = '';
}


class TasksModel {

    protected $errorMsg = '';
    
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
		if (file_exists('./projects/project_'.$projectId.'.json')) {
				$actFileTime = filemtime('./projects/project_'.$projectId.'.json');
				if ($actFileTime > $fileTime) {
						$lines = file('./projects/project_'.$projectId.'.json');
						$result->project = JSON_decode(implode("",$lines));   
				}
				$result->fileTime = $actFileTime;
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
            $this->errorMsg = 'CONTENTCHANGE';
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
             $errorMsg = 'CHECKERROR';
             
             //  ha semmi nem változott akkor OK
             if ($oldTask) {
                 if (($this->contentNotChange($newTask, $oldTask)) &&
                     ($newTask->state == $oldTask->state) &&
                     ($newTask->assign == $oldTask->assign)) {
                         $errorMsg = '';
                         $result = true;
                 }
             }
             
	         // új javaslatot, kérdést, hibajelzést bárki felvihet
             if ( (!$oldTask) &&
                  (($newTask->type == 'suggest') || ($newTask->type == 'query') || ($newTask->type == 'bug')) &&
                  ($newTask->state == 'waiting') &&
                  ($newTask->assign == $freeAssign)) {
                     $errorMsg = '';
                     $result = true;
             }

            // eddig szabad feladatottag magához veheti, state változhat
            if ($oldTask && $member) {
                if (($oldTask->assign == $freeAssign) && 
                    ($newTask->assign == $loggedUser) &&    
                    ($this->contentNotChange($newTask, $oldTask ))
                   ) {
                          $errorMsg = '';
                          $result = true;
                  }
            }

            // magához rendelt feladat csak state változtatás
            if ($oldTask && $member) {
                 if (($oldTask->assign == $loggedUser) &&
                     ($newTask->assign == $loggedUser) &&
                     ( $this->contentNotChange($newTask, $oldTask ))) {
                         $errorMsg = '';
                         $result = true;
                 }
            }

           // magához rendeltet elengedheti ha state atWork --> canVerify
            if ($oldTask && $member) {
                 if (($oldTask->assign == $loggedUser) &&
                      ($newTask->assign == $freeAssign) &&
                      ($oldTask->state == 'atwork') &&
                      ($newTask->state == 'canverify') &&
                      ($this->contentNotChange($newTask, $oldTask) )) {
                         $errorMsg = '';
                         $result = true;
                 }
            }
            if ($errorMsg != '') {
                $this->errorMsg = $errorMsg;
            }
            return $result;    
	}
		
	/**
	 * @param bool $accessRight I/O
	 * @param int|string $projectId
	 * @param object $project
	 * @return void
	 */
	protected function saveCheckNoAdmin(bool & $accessRight, $projectId, $project) {
	    $member = in_array($_SESSION['loggedUser'], $_SESSION['users']);
	    $newTasks = $this->getTasks($project);
	    if (file_exists('./projects/project_'.$projectId.'.json')) {
	        $s = implode('',file('./projects/project_'.$projectId.'.json'));
	    } else {
	        $s = '{}';
	    }
	    $oldTasks = $this->getTasks($s);
	    
	    $loggedUser = $_SESSION['loggedUser'];
	    foreach($newTasks as $newTask) {
	        $oldTask = $this->findTask($oldTasks, $newTask);
	        if (!$this->checkTaskAction($newTask,$oldTask, $member, $loggedUser)) {
	            $accessRight = false;
	        }
	    }
	    foreach($oldTasks as $oldTask) {
	        $newTask = $this->findTask($newTasks, $oldTask);
	        if (!$newTask) {
	            $this->errorMsg = 'DELETE_ACCESSDENIED';
	            $accessRight = false;
	        }
	    }
	    
	}
		
	/**
	* save tasks into database 
	* @param int|string $projectId  REQUED
	* @param project jsonStr REQUED
	* @return string jsonStr 
	* - {"fileTime":num}  
	*/
	public function save($projectId, $project): string {
		// sessionban lévő loggedUser, admins, users alapján jogosultság ellenörzés
	    $result = new stdClass();
	    $accessRight = true;
		$this->errorMsg = '';
		if (!isset($_SESSION['loggedUser'])) {
		    // fatal error
		    return '{"fileTime":0, "errorMsg":"WRONGSESSION"}';
		}
		
		
		if (!in_array($_SESSION['loggedUser'], $_SESSION['admins'])) {
		    // nem admin
		    $this->saveCheckNoAdmin($accessRight, $projectId, $project);
		}
		
		if ($accessRight) {
	      $fp = fopen('./projects/project_'.$projectId.'.json','w+');
		  fwrite($fp, $project);
		  fclose($fp);
		}
		if (file_exists('./projects/project_'.$projectId.'.json')) {
		    $fileTime = filemtime('./projects/project_'.$projectId.'.json');
		} else {
		    $fileTime = '';
		}
		$result->fileTime = $fileTime;
		$result->errorMsg = $this->errorMsg;
		return JSON_encode($result);
	} 
}
?>