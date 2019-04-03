<?php
declare(strict_types=1);
include_once './controllers/tasks.php';
include_once './tests/mock.php';

use PHPUnit\Framework\TestCase;


/* CodeCovarage report start
use SebastianBergmann\CodeCoverage\CodeCoverage;
$coverage = new CodeCoverage;
$coverage->filter()->addDirectoryToWhitelist('./controllers');
$coverage->start('tasks');
*/

// test Cases
class tasksTest extends TestCase 
{
    protected $controller;
    protected $request;
    
    function __construct() {
        parent::__construct();
        if (file_exists('./projects/project_test.json'))
            unlink('./projects/project_test.json');
        $this->controller = new tasksController();
        $this->request = new Request();
    }
    
    public function test_saveNewOtherByAdmin() {
        $this->request->set('projectid','test');
        $this->request->set('project','{
            "canstart":[{"id":100, "title":"test100", "desc":"", 
                        "type":"other", "assign":"https://www.gravatar.com/avatar/", "req":""}]
        }');
        $_SESSION['loggedUser'] = 'admin';
        $_SESSION['admins'] = array('admin');
        $_SESSION['users'] = array('admin','user1');
        
        
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
        $_SESSION['loggedUser'] = 'guest';
        $_SESSION['admins'] = array('admin');
        $_SESSION['users'] = array('admin','user1');
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
        $_SESSION['loggedUser'] = 'guest';
        $_SESSION['admins'] = array('admin');
        $_SESSION['users'] = array('admin','user1');
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
        $_SESSION['loggedUser'] = 'guest';
        $_SESSION['admins'] = array('admin');
        $_SESSION['users'] = array('admin','user1');
        $this->controller->save($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
    
    public function test_saveNewOtherByGuest() {
        $this->request->set('projectid','test');
        $this->request->set('project','{
            "canstart":[{"id":100, "title":"test100", "desc":"",
                        "type":"other", "assign":"https://www.gravatar.com/avatar/", "req":""}],
            "waiting":[{"id":101, "title":"test101", "desc":"", "type":"bug", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":102, "title":"test102", "desc":"", "type":"query", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":103, "title":"test103", "desc":"", "type":"suggest", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":104, "title":"test104", "desc":"", "type":"other", "assign":"https://www.gravatar.com/avatar/", "req":""}]
        }');
        $_SESSION['loggedUser'] = 'guest';
        $_SESSION['admins'] = array('admin');
        $_SESSION['users'] = array('admin','user1');
        $this->controller->save($this->request);
        $this->expectOutputRegex('/"errorMsg":"CHECKERROR"/');
    }
    
    public function test_savePickupByMember() {
        $this->request->set('projectid','test');
        $this->request->set('project','{
            "atwork":[{"id":100, "title":"test100", "desc":"",
                        "type":"other", "assign":"user1", "req":""}],
            "waiting":[{"id":101, "title":"test101", "desc":"", "type":"bug", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":102, "title":"test102", "desc":"", "type":"query", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":103, "title":"test103", "desc":"", "type":"suggest", "assign":"https://www.gravatar.com/avatar/", "req":""}]
        }');
        $_SESSION['loggedUser'] = 'user1';
        $_SESSION['admins'] = array('admin');
        $_SESSION['users'] = array('admin','user1');
        $this->controller->save($this->request);
        $this->expectOutputRegex('/"errorMsg":""/');
    }
   
    public function test_saveMoveMyTaskByMember() {
        $this->request->set('projectid','test');
        $this->request->set('project','{
            "canverify":[{"id":100, "title":"test100", "desc":"",
                        "type":"other", "assign":"user1", "req":""}],
            "waiting":[{"id":101, "title":"test101", "desc":"", "type":"bug", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":102, "title":"test102", "desc":"", "type":"query", "assign":"https://www.gravatar.com/avatar/", "req":""},
                       {"id":103, "title":"test103", "desc":"", "type":"suggest", "assign":"https://www.gravatar.com/avatar/", "req":""}]
        }');
        $_SESSION['loggedUser'] = 'user1';
        $_SESSION['admins'] = array('admin');
        $_SESSION['users'] = array('admin','user1');
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
        $_SESSION['loggedUser'] = 'user2';
        $_SESSION['admins'] = array('admin');
        $_SESSION['users'] = array('admin','user1',"user2");
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
        $_SESSION['loggedUser'] = 'user2';
        $_SESSION['admins'] = array('admin');
        $_SESSION['users'] = array('admin','user1','user2');
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
        $_SESSION['loggedUser'] = 'user1';
        $_SESSION['admins'] = array('admin');
        $_SESSION['users'] = array('admin','user1');
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
    
    
    
}

/* CodeCovarage report stop, report files: ./tmp/clover.xml  ./tmp/code-covarage-report
$coverage->stop();
$writer = new \SebastianBergmann\CodeCoverage\Report\Clover;
$writer->process($coverage, './tmp/clover.xml');
$writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade;
$writer->process($coverage, './tmp/code-coverage-report');
*/
    