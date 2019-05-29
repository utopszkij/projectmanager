<?php
/**
 * SEQO frind URL process
 *  processed URI:   'http[s]://yourdomain/[path/]opt/coontrollerName/taskName/pName/pValue/....
 *  @return void, set $this->componentName, $this->task, $this->params
 */
    if (!isset($_SERVER)) {
        return;
    }
    if (!isset($_SERVER['REQUEST_URI'])) {
        return;
    }
    $w = Array();
    $i = 0;
    $j = 0;
    $w = explode('/', $_SERVER['REQUEST_URI']);
    $i = array_search('opt',$w);
    if (($i > 0) && ($i < count($w)-2)) {
        $_GET['option'] = $w[$i+1];
        $_GET['task'] = $w[$i+2];
        for ($j = $i+3; $j < (count($w)-1); $j = $j+2) {
            $_GET[$w[$j]] = $w[$j+1];
        }
    }
    include './app.php';
?>
