<?php
declare(strict_types=1);
global $REQUEST;
include_once './tests/config.php';
include_once './core/database.php';
include_once './controllers/members.php';
include_once './tests/mock.php';

use PHPUnit\Framework\TestCase;

// test Cases
class MembersTest extends TestCase 
{
    protected $controller;
    protected $request;
    
    function __construct() {
        global $REQUEST;
        parent::__construct();
        if (file_exists('./projects/project_test.json'))
            unlink('./projects/project_test.json');
        $this->controller = new MembersController();
        $this->request = new Request();
        $REQUEST = $this->request;
        $db = new DB();
        $db->statement('CREATE DATABSE IF NOT EXISTS test');
        $db->statement('DROP TABLE IF EXISTS tasks');
        $db->statement('DROP TABLE IF EXISTS members');
    }
    
    public function test_memberupdate_Member() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('avatar','user1');
        $this->request->sessionSet('admin','1');
        
        // logged member
        $this->request->sessionSet('loggedUser','user1');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->memberupdate($this->request);
        $this->expectOutputRegex('/"errorMsg":"ACCESSDENIED"/');
    }
    
    public function test_memberupdate_Guest() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('avatar','user1');
        $this->request->sessionSet('admin','1');
        
        // logged guest
        $this->request->sessionSet('loggedUser','guest');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->memberupdate($this->request);
        $this->expectOutputRegex('/"errorMsg":"ACCESSDENIED"/');
        
    }
        
    public function test_memberupdate_Admin() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('avatar','user1');
        $this->request->sessionSet('admin','1');
        
        // logged admin
        $this->request->sessionSet('loggedUser','admin');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->memberupdate($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
        
    public function test_saveallmembers_Admin_1() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('members','[
            {"avatar":"admin", "nick":"admin", "admin":1},
            {"avatar":"user1", "nick":"user1", "admin":0},
            {"avatar":"user2", "nick":"user2", "admin":0},
            {"avatar":"user3", "nick":"user3", "admin":0}
        ]');
        
        // logged admin
        $this->request->sessionSet('loggedUser','admin');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->saveallmembers($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
    
    public function test_saveallmembers_Admin_2() {
        $this->request->set('projectid','testProject');
        $this->request->set('sessionid',0);
        $this->request->set('members','[
            {"avatar":"admin", "nick":"admin", "admin":1},
            {"avatar":"user1", "nick":"user1", "admin":0},
            {"avatar":"user3", "nick":"user2", "admin":1},
            {"avatar":"user4", "nick":"user4", "admin":0}
        ]');
        
        // logged admin
        $this->request->sessionSet('loggedUser','admin');
        $this->request->sessionSet('admins',array('admin'));
        $this->request->sessionSet('users',array(array('admin','admin'),array('user1','user1')));
        
        $this->controller->saveallmembers($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
    
}

