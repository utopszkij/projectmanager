<?php
/*

./controllers/ctrlname.php
class ctrlnameController { 
	public function taskname($request) { 
   	$model = getModel(ctrlnameModel);
   	$view = getView(ctrlnameView);
   	$view->tmplname( {"data1":"value", ...} );
   } 	
}   

./models/modelname.php
class modelnameModel {

}

./views/viewname.php
class viewnameView {
	public funtion tmplname($params) {
		echo ..... $params->parName......	
		loadJavascript($jsfileName,$params);
	}
}   
*/
   
function loadJavaScript($jsName, $params) {
	echo "\n".'<script type="text/javascript">'."\n";
	echo '// params from controller'."\n";
	foreach ($params as $fn => $fv) {
		if ($fn != '') {
			if (is_array($fv)) {
				echo "var $fn = ".JSON_encode($fv).";\n";
			} else if (is_object($fv)) {
				echo "var $fn = ".JSON_encode($fv).";\n";
			} else if (is_string($fv)) {
				$fv = str_replace("'", "\\'", $fv);
				$fv = str_replace("\n", "\\n", $fv);
				$fv = str_replace("\r", "\\r", $fv);
				$fv = str_replace("\t", "\\t", $fv);
				echo "var $fn = '$fv';\n";
			}	else {
				echo "var $fn = $fv;\n";
			}
		}
	}
	echo 'var sid = "'.session_id().'";'."\n";
	include_once './js/'.$jsName.'.js';
	echo "\n".'</script>'."\n";
}

function getModel($modelName) {
	include_once './models/'.$modelName.'.php';
	$className = $modelName.'Model';
	return new $className ();
}

function getView($viewName) {
	include_once './views/'.$viewName.'.php';
	$className = $viewName.'View';
	return new $className ();
}

class Request {
	public $params = array();
	public function input($name, $default = '') {
		$result = $default;
		if (isset($this->params[$name])) {
			$result = $this->params[$name];
		}	
		return $result;
	}
	public function set($name, $value) {
		$this->params[$name] = $value;	
	}
	public function sessionGet($name,$default='') {
		$result = $default;
		if (isset($_SESSION[$name])) {
			$result = $_SESSION[$name];
		}
		return $result;
	}
	public function sessionSet($name,$value) {
		$_SESSION[$name] = $value;
	}
	
}

?>