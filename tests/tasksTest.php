<?php
declare(strict_types=1);
global $REQUEST;
include_once './tests/config.php';
include_once './core/database.php';
include_once './controllers/tasks.php';
include_once './tests/mock.php';

use PHPUnit\Framework\TestCase;

// test Cases
class TasksTest extends TestCase 
{
    protected $controller;
    protected $request;
    
    function __construct() {
        global $REQUEST;
        parent::__construct();
        if (file_exists('./projects/project_test.json'))
            unlink('./projects/project_test.json');
        $this->controller = new TasksController();
        $this->request = new Request();
        $REQUEST = $this->request;
        $db = new DB();
        $db->statement('CREATE DATABSE IF NOT EXISTS test');
        $db->statement('DROP TABLE IF EXISTS tasks');
        $db->statement('DROP TABLE IF EXISTS members');
    }
    
    public function test_saveNewOtherByAdmin() {
        $this->request->set('projectid','test');
        $this->request->set('project','{
            "canstart":[{"id":100, "title":"test100", "desc":"", 
                        "type":"other", "assign":"https://www.gravatar.com/avatar/", "req":""}]
        }');
        $this->request->sessionSet('loggedUser','admin');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array('admin','user1'));
        $this->controller->save($this->request);
        // $this->assertFalse($actual);
        // $this->assertTrue($actual);
        // $this->assertGreaterThan($expected, $actual);
        // $this->assertGreaterThanOrEqual()
        // $this->assertLessThan($excepted, $actual)
        // $this->assertLessThanOrEqual($excepted, $actual)
        // $this->assertEquals($excepted, $actual);
        // $this->assertRegExp($pattern, $actual)
        $this->expectOutputRegex('/"errorMsg":""/');
    }
    
    public function test_saveNewBugByGuest() {
        $this->request->set('projectid','test');
        $this->request->set('project','{
            "canstart":[{"id":100, "title":"test100", "desc":"", 
                        "type":"other", "assign":"https://www.gravatar.com/avatar/", "req":""}],
            "waiting":[{"id":101, "title":"test101", "desc":"", "type":"bug", "assign":"https://www.gravatar.com/avatar/", "req":""}]
        }');
        $this->request->sessionSet('loggedUser','guest');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array('admin','user1'));
        $this->controller->save($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
    
    public function test_saveNewQueryByGuest() {
        $this->request->set('projectid','test');
        $this->request->set('project','{
            "canstart":[{"id":100, "title":"test100", "desc":"", 
                        "type":"other", "assign":"https://www.gravatar.com/avatar/", "req":""}],
            "waiting":[{"id":101, "title":"test101", "desc":"", "type":"bug", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":102, "title":"test102", "desc":"", "type":"query", "assign":"https://www.gravatar.com/avatar/", "req":""}]
        }');
        $this->request->sessionSet('loggedUser','guest');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array('admin','user1'));
        $this->controller->save($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
    
    public function test_saveNewSuggestByGuest() {
        $this->request->set('projectid','test');
        $this->request->set('project','{
            "canstart":[{"id":100, "title":"test100", "desc":"",
                        "type":"other", "assign":"https://www.gravatar.com/avatar/", "req":""}],
            "waiting":[{"id":101, "title":"test101", "desc":"", "type":"bug", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":102, "title":"test102", "desc":"", "type":"query", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":103, "title":"test103", "desc":"", "type":"suggest", "assign":"https://www.gravatar.com/avatar/", "req":""}]
        }');
        $this->request->sessionSet('loggedUser','guest');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array('admin','user1'));
        $this->controller->save($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
    
    public function test_savePickUpForeignTaskByMember() {
        $this->request->set('projectid','test');
        $this->request->set('project','{
            "canverify":[{"id":100, "title":"test100", "desc":"",
                        "type":"other", "assign":"user2", "req":""}],
            "waiting":[{"id":101, "title":"test101", "desc":"", "type":"bug", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":102, "title":"test102", "desc":"", "type":"query", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":103, "title":"test103", "desc":"", "type":"suggest", "assign":"https://www.gravatar.com/avatar/", "req":""}]
        }');
        $this->request->sessionSet('loggedUser','user2');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array('admin','user1'));
        $this->controller->save($this->request);
        $this->expectOutputRegex('/"errorMsg":"CHECKERROR"/');
    }
    
    public function test_saveMoveForeignTaskByMember() {
        $this->request->set('projectid','test');
        $this->request->set('project','{
            "atverify":[{"id":100, "title":"test100", "desc":"",
                        "type":"other", "assign":"user1", "req":""}],
            "waiting":[{"id":101, "title":"test101", "desc":"", "type":"bug", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":102, "title":"test102", "desc":"", "type":"query", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":103, "title":"test103", "desc":"", "type":"suggest", "assign":"https://www.gravatar.com/avatar/", "req":""}]
        }');
        $this->request->sessionSet('loggedUser','user2');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array('admin','user1'));
        $this->controller->save($this->request);
        $this->expectOutputRegex('/"errorMsg":"CHECKERROR"/');
    }
    
    
    public function test_saveChangeTitleByMember() {
        $this->request->set('projectid','test');
        $this->request->set('project','{
            "canverify":[{"id":100, "title":"test100changed", "desc":"",
                        "type":"other", "assign":"user1", "req":""}],
            "waiting":[{"id":101, "title":"test101", "desc":"", "type":"bug", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":102, "title":"test102", "desc":"", "type":"query", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":103, "title":"test103", "desc":"", "type":"suggest", "assign":"https://www.gravatar.com/avatar/", "req":""}]
        }');
        $this->request->sessionSet('loggedUser','user1');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array('admin','user1'));
        $this->controller->save($this->request);
        $this->expectOutputRegex('/"errorMsg":"CHECKERROR"/');
    }
    
    public function test_refresh() {
        $this->request->set('projectid','test');
        $this->request->set('fileTime',0);
        $this->controller->refresh($this->request);
        $this->expectOutputRegex('/"fileTime":/');
        $this->expectOutputRegex('/"project":/');
    }
    
    public function test_refreshNotChange() {
        $this->request->set('projectid','test');
        $this->request->set('fileTime',9999999999999999999);
        $this->controller->refresh($this->request);
        $this->expectOutputRegex('/"fileTime":/');
        $this->expectOutputRegex('/^((?!"project":).)*$/');
    }
    
    public function test_show() {
        $this->request->set('projectid','demo');
        $this->controller->show($this->request);
        $this->expectOutputRegex('/html/');
    }
    
    public function test_show_demo1() {
        $this->request->set('projectid','demo1');
        $this->controller->show($this->request);
        $this->expectOutputRegex('/html/');
    }
    
    public function test_show_demo2() {
        $this->request->set('projectid','demo2');
        $this->controller->show($this->request);
        $this->expectOutputRegex('/html/');
    }
        
    public function test_show_demo3() {
        $this->request->set('projectid','demo3');
        $this->controller->show($this->request);
        $this->expectOutputRegex('/html/');
    }
    
    public function test_show_testProject() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('callerapiurl','http://localhost/projectmanager/tests/testapi.json');
        $this->controller->show($this->request);
        $this->expectOutputRegex('/html/');
    }
    
    public function test_taskinsert_Admin() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('id',200);
        
        // logged admin
        $this->request->sessionSet('loggedUser','admin');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->taskinsert($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
    
    public function test_taskinsert_Guest() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('id',200);
        
        // logged guest
        $this->request->sessionSet('loggedUser','guest');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->taskinsert($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
    
    public function test_update_Guest() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('data','{"id":200,
        "title":"titleUpdated",
        "desc":"",
        "type":"bug",
        "req":"",
        "assign":"",
        "state":"waiting"        
        }');
        
        // logged guest
        $this->request->sessionSet('loggedUser','guest');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->taskupdate($this->request);
        $this->expectOutputRegex('/"errorMsg":"CHECKERROR"/');
    }
    
    public function test_update_Member() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('data','{"id":200,
        "title":"titleUpdated",
        "desc":"",
        "type":"bug",
        "req":"",
        "assign":"",
        "state":"waiting"
        }');
        
        // logged member
        $this->request->sessionSet('loggedUser','user1');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->taskupdate($this->request);
        $this->expectOutputRegex('/"errorMsg":"CHECKERROR"/');
    }
    
    public function test_update_pickUp_Member() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('data','{"id":200,
        "project_id":"testProject",
        "title":"",
        "desc":"",
        "type":"other",
        "req":"",
        "assign":"user1",
        "state":"waiting"
        }');
        
        // logged member
        $this->request->sessionSet('loggedUser','user1');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->taskupdate($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
    
    public function test_update_moveMyTask_Member() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('data','{"id":200,
        "project_id":"testProject",
        "title":"",
        "desc":"",
        "type":"other",
        "req":"",
        "assign":"user1",
        "state":"atwork"
        }');
        
        // logged member
        $this->request->sessionSet('loggedUser','user1');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->taskupdate($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
    
    public function test_update_unPickup_Member() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('state','canverify');
        $this->request->set('data','{"id":200,
        "project_id":"testProject",
        "title":"",
        "desc":"",
        "type":"other",
        "req":"",
        "assign":"https://www.gravatar.com/avatar/",
        "state":"canverify"
        }');
        
        // logged member
        $this->request->sessionSet('loggedUser','user1');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->taskupdate($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
    
    public function test_update_Admin() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('data','{"id":200,
        "title":"titleUpdated",
        "desc":"",
        "type":"bug",
        "req":"",
        "assign":"",
        "state":"waiting"
        }');
        
        // logged admin
        $this->request->sessionSet('loggedUser','admin');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->taskupdate($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }

    public function test_update_NotFound_Admin() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('data','{"id":210,
        "title":"titleUpdated",
        "desc":"",
        "type":"bug",
        "req":"",
        "assign":"",
        "state":"waiting"
        }');
        
        // logged admin
        $this->request->sessionSet('loggedUser','admin');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->taskupdate($this->request);
        $this->expectOutputRegex('/"errorMsg":"TASKNOTFOUND"/');
    }
    
    public function test_taskdelete_Guest() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('id',200);
        $this->request->sessionSet('loggedUser','admin');
        
        // logged guest
        $this->request->sessionSet('admins',array('guest'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->taskdelete($this->request);
        $this->expectOutputRegex('/"errorMsg":"ACCESSDENIED"/');
    }
    
    public function test_taskdelete_Member() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('id',200);
        
        // logged member
        $this->request->sessionSet('loggedUser','user1');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->taskdelete($this->request);
        $this->expectOutputRegex('/"errorMsg":"ACCESSDENIED"/');
    }
    
    public function test_taskdelete_Admin() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('id',200);
        $this->request->sessionSet('loggedUser','admin');
        
        // logged admin
        $this->request->sessionSet('loggedUser','admin');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->taskdelete($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
   
}

