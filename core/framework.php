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

/**
 * echo javascript code, inject params
 * @param string $jsName javascript file full path
 * @param array $params  {"name":value, ....}
 * @return void
 */
function loadJavaScript(string $jsName, $params) {
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
	echo '$("#working").hide()'."\n";
	echo "\n".'</script>'."\n";
}

/**
 * echo javascript code, inject params and language constanses
 * must in html:  <body ng-app="app">
 *                  <div ng-controller="ctrl">
 *                      ....
 *                      <?php loadJavaSciptAngular('jsName', $params); ?>
 *                  </div>
 *                </body>  
 * @param string $jsName javascript file full path
 * @param array $params  {"name":value, ....}
 * @return void
 */
function loadJavaScriptAngular(string $jsName, $params) {
    ?>
    <script src="https://code.angularjs.org/1.7.8/angular.js"></script>
    <script type="text/javascript">
    angular.module("app", []).controller("ctrl", function($scope) {
        <?php 
        $languages = get_defined_constants(true);
        echo '$scope.LNG = [];'."\n";
        foreach ($languages['user'] as $fn => $fv) {
            if (substr($fn,0,5) != 'MYSQL') {
                echo '$scope.LNG["'.$fn.'"] = '.JSON_encode($fv).';'."\n";
            }
        }
        echo '$scope.txt = function(token) {
            if ($scope.LNG[token] == undefined) {
                return token;
            } else {
                return $scope.LNG[token];
            }
        };
        ';
        foreach ($params as $fn => $fv) {
            if ($fn != '') {
                if (is_array($fv)) {
                    echo '$scope.'."$fn = ".JSON_encode($fv).";\n";
                } else if (is_object($fv)) {
                    echo '$scope.'."$fn = ".JSON_encode($fv).";\n";
                } else if (is_string($fv)) {
                    $fv = str_replace("'", "\\'", $fv);
                    $fv = str_replace("\n", "\\n", $fv);
                    $fv = str_replace("\r", "\\r", $fv);
                    $fv = str_replace("\t", "\\t", $fv);
                    echo '$scope.'."$fn = '$fv';\n";
                }	else {
                    echo '$scope.'."$fn = $fv;\n";
                }
            }
        }
        echo '$scope.sid = "'.session_id().'";'."\n";
        include_once './js/'.$jsName.'.js';
        ?>
        $("#scope").show();
        $("#working").hide();
    }); // controller function
    </script>
    <?php
}

/**
 * create new model object from "./models/modelname.php"
 * @param string $modelName
 * @return Model
 */
function getModel(string $modelName) {
	include_once './models/'.$modelName.'.php';
	$className = ucfirst($modelName).'Model';
	return new $className ();
}

/**
 * cretae new view object from "./views/viewName.php"
 * @param string $viewName
 * @return View
 */
function getView(string $viewName) {
	include_once './views/'.$viewName.'.php';
	$className = ucfirst($viewName).'View';
	return new $className ();
}

class Request {
	public $params = array();
	protected $sessions;
	
	/**
	 * get item from $this->params
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function input(string $name, $default = '') {
		$result = $default;
		if (isset($this->params[$name])) {
			$result = $this->params[$name];
		}	
		return $result;
	}
	
	/**
	 * set item into $this->params
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function set(string $name, $value) {
		$this->params[$name] = $value;	
	}
	
	/**
	 * get item from session
	 * @param string $name
	 * @param string $default
	 * @return mixed
	 */
	public function sessionGet(string $name, $default='') {
	    $result = $default;
	    $sessionId = session_id();
	    $this->session_init($sessionId);
	    if (isset($this->sessions->$name)) {
	        $result = $this->sessions->$name;
	    }
	    return $result;
	}
	
	/**
	 * set item into session
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function sessionSet(string $name, $value) {
	    $sessionId = session_id();
	    $this->session_init($sessionId);
	    $this->sessions->$name = $value;
	    $this->session_save($sessionId);
	}
	
	/**
	 * session_start - open sessions record from database or create new
	 * @param string $sessionId
	 * @return void
	 */
	protected function session_init(string $sessionId) {
	    $maxlifetime = ini_get("session.gc_maxlifetime");
	    if (count($this->sessions) <= 0) {
	        $this->sessions = new stdClass();
	        $db = new DB();
	        $db->statement('CREATE TABLE IF NOT EXISTS sessions (id varchar(256), data text, time datetime)');
	        $table = DB::table('sessions');
	        $table->where(array('time','<', date('Y-m-d H:i:s', (time() - $maxlifetime))))->delete();
	        $table = DB::table('sessions');
	        $res = $table->where(array('id',$sessionId))->first();
	        if ($res) {
	            $this->sessions = JSON_decode($res->data);
	            $record = new stdClass();
	            $record->time = date('Y-m-d H:i:s');
	            $table->where(array('id',$sessionId))->update($record);
	        } else {
	            $this->sessions = new stdClass();
	            $record = new stdClass();
	            $record->id = $sessionId;
	            $record->data = JSON_encode($this->sessions);
	            $record->time = date('Y-m-d H:i:s');
	            $table->insert($record);
	        }
	    } else {
	        $table = DB::table('sessions');
	        $record = new stdClass();
	        $record->time = date('Y-m-d H:i:s');
	        $table->where(array('id',$sessionId))->update($record);
	        $table->where(array('time','<', date('Y-m-d H:i:s', (time() - $maxlifetime))))->delete();
	    }
	}
	
	/**
	 * save session into database
	 * @param string $sessionId
	 * @return void
	 */
	protected function session_save(string $sessionId) {
	    $maxlifetime = ini_get("session.gc_maxlifetime");
	    $db = new DB();
	    $db->statement('CREATE TABLE IF NOT EXISTS sessions (id varchar(256), data text, time datetime)');
	    $table = DB::table('sessions');
	    $record = new stdClass();
	    $record->data = JSON_encode($this->sessions);
	    $record->time = date('Y-m-d H:i:s');
	    $table->where(array('id',$sessionId))->update($record);
	    $table = DB::table('sessions');
	    $table->where(array('time','<', date('Y-m-d H:i:s', (time() - $maxlifetime))))->delete();
	}
	
	/**
	 * count of active sessions
	 * @return integer
	 */
	public function session_count(): int {
	    $sessionId = session_id();
	    $table = DB::table('sessions');
	    return $table->count();
	}
} // Request

/**
 * url kiegészités
 * 'https(s):xxxxxxxx' változatlan
 * './xxxxxx' --> https(s)://{domain}xxxxxx
 * 'xxxxxxx' --> https(s):xxxxxxxx
 * @param string $s
 * @return string
 */
function url(string $s): string {
    $result = $s;
    if (substr($result,0,2) == './') {
        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] != '') {
                $result = 'https://'.$_SERVER['SERVER_NAME'].substr($result,2,500);
            } else {
                $result = 'http://'.$_SERVER['SERVER_NAME'].substr($result,2,500);
            }
        } else {
            $result = 'http://'.$_SERVER['SERVER_NAME'].substr($result,2,500);
        }
    } else if (strpos($result,'http') === false) {
        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] != '') {
                $result = 'https:'.$result;
            } else {
                $result = 'http:'.$result;
            }
        } else {
            $result = 'http:'.$result;
        }
    }
    return $result;
}

/**
 * language convertion
 * @param string $s language token
 * @return string translated text
 */
function txt(string $s): string {
    $result = $s;
    if (defined($s)) {
        $result = constant($s);
    }
    return $result;
}

/**
 * return hTML head 
 *    include javascript global.alert, global.confirm, global.post, globa.working functions
 * must use htmlPopup() in HTML body tag    
 * @return string
 */
function htmlHead(): string {
    return '
    <!doctype html>
    <html lang="hu">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1240px, initial-scale=1">
    <title>projektmanager</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="./style.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script type="text/javascript">
    var global = {};
    global.alert = function(str) {
        // window.alert(str);
        $("#popupYes").hide();
        $("#popupNo").hide();
        $("#popupClose").show();
        $("#popup p").html(str);
        $("#popup").show();
        $("#popupClose").click(function(){
            $("#popup").hide();
        });
    };
    global.confirm = function(str, yesfun, nofun) {
        if (yesfun != undefined) {
            $("#popupYes").mouseup(yesfun);
        } else {
            $("#popupYes").mouseup(function() {});
        }
        if (nofun != undefined) {
            $("#popupNo").mouseup(nofun);
        } else {
            $("#popupNo").mouseup(function() {});
        }
        $("#popupYes").show();
        $("#popupNo").show();
        $("#popupClose").hide();
        $("#popup p").html(str);
        $("#popupNo").click(function(){
            $("#popup").hide();
        });
            $("#popupYes").click(function(){
                $("#popup").hide();
            });
                $("#popup").show();
    };
    global.working = function(show) {
        if (show) {
            $("#working").show();
        } else {
            $("#working").hide();
        }
    };
    global.post = function(url, options, fun) {
        $.post(url, options, fun);
    }
    </script>
    ';
}

/**
 * retrun popup html code (use this HTML global.alert, global.confirm functions in htmlHead)
 * @return string
 */
function htmlPopup(): string {
   return '
    <div id="popup" style="display:none">
        <p class="alert alert-danger"></p>
        <div id="popupButtons">
            <button type="button" id="popupYes">'.txt('YES').'</button>
			<button type="button" id="popupNo">'.txt('NO').'</button>
			<button type="button" id="popupClose">'.txt('CLOSE').'</button>
		</div>
    </div>
    <div id="working"><span>'.txt('WORKING').'...</span></div>
	';    
}
?>