<?php
declare(strict_types=1);
include_once './controllers/default.php';
include_once './tests/mock.php';

use PHPUnit\Framework\TestCase;

// test Cases
class DefaultTest extends TestCase 
{
    protected $controller;
    protected $request;
    
    function __construct() {
        parent::__construct();
        if (file_exists('./projects/project_test.json'))
            unlink('./projects/project_test.json');
        $this->controller = new DefaultController();
        $this->request = new Request();
    }
    
    public function test_default() {
        $_SESSION['loggedUser'] = 'admin';
        $_SESSION['admins'] = array('admin');
        $_SESSION['users'] = array('admin','user1');
        $this->controller->default($this->request);
        $this->expectOutputRegex('/html/');
    }
    
}
