<?php
class ExampleView {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function example($p) {
	    echo htmlHead();
        ?>	    
        <body ng-app="app">
          <div ng-controller="ctrl" id="scope" style="display:none">
          	<?php echo htmlPopup(); ?>
        	<h1>{{title}}</h1>
        	<p>param1 = {{param1}}</p>
        	<p>errorMsg = {{errorMsg}}</p>
        	<p>avatar = {{avatar}}</p>
        	<p>sid = {{sid}}</p>
			<p>{{txt('YES')}}</p>			
          </div>
          <?php loadJavaScriptAngular('example',$p); ?>
        </body>
        </html>
        <?php 		
	}
}
?>