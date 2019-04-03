<?php
   
error_reporting(E_ALL);

if (isset($_GET['sid'])) {
    session_id(strip_tags($_GET['sid']));
}
if (isset($_POST['sid'])) {
    session_id(strip_tags($_POST['sid']));
}
session_start();

include_once './framework.php';

$request = new Request();
foreach ($_POST as $name => $value) {
	$request->set($name,$value);
}
foreach ($_GET as $name => $value) {
	$request->set($name,$value);
}
$option = $request->input('option','default');
$task = $request->input('task','default');

if (file_exists('./controllers/'.$option.'.php')) {
	include_once './controllers/'.$option.'.php';
	$controllerName = $option.'Controller';
	$controller = new $controllerName ();
	$controller->$task ($request);
} else {
	echo 'Fatal error '.$option.' controller not found'; exit();
}
?>