<?php
declare(strict_types=1);
global $REQUEST;
include_once './tests/config.php';
include_once './core/database.php';
include_once './controllers/example.php';
include_once './tests/mock.php';

use PHPUnit\Framework\TestCase;

// test Cases
class ExampleTest extends TestCase 
{
    protected $controller;
    protected $request;
    
    function __construct() {
        global $REQUEST;
        parent::__construct();
        $this->controller = new ExampleController();
        $this->request = new Request();
        $REQUEST = $this->request;
        
    }
    
    public function test_start() {
        // create and init test database
        $db = new DB();
        $db->statement('CREATE DATABASE IF NOT EXISTS test');
        $this->assertEquals('',$db->getErrorMsg());
    }
    
    public function test_example() {
        $this->request->set('param1','testParam');
        
        $this->controller->example($this->request);
        $this->expectOutputRegex('/param1/');
        // lásd: https://phpunit.readthedocs.io/en/8.1/
    }
    
    public function test_end() {
        $db = new DB();
        // clear test datas
        $this->assertEquals('','');
    }
}

