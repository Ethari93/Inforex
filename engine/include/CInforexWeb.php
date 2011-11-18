<?php
mb_internal_encoding("UTF-8");

class InforexWeb{
	
	function __construct(){
		global $config;
		set_exception_handler('InforexWeb::custom_exception_handler');		

		/********************************************************************8
		 * Aktywuj FireBug-a
		 */
		FB::setEnabled(true);
		
		/********************************************************************8
		 * Rozpocznij sesję
		 */
		HTTP_Session2::useCookies(true);
		HTTP_Session2::start($config->key);
		HTTP_Session2::setExpire(time() + $config->session_time);
	}

	static function custom_exception_handler($exception){
		echo "<h1 style='background:red; color:white; margin: 0px'>Exception</h1>";
		echo "<pre style='border: 1px solid red; padding: 5px; background: #FFE1D0; margin: 0px'>";
		print_r($exception);
		echo "</pre>";
	}


	/********************************************************************
	 * 
	 */
	function doAction($action, &$variables){
	 	global $user, $corpus, $config;
		
		include($config->path_engine . "/actions/a_{$action}.php");
		$class_name = "Action_{$action}";
		$o = new $class_name();
	
		// Autoryzuj dostęp do akcji.
		if ($o->isSecure && !$auth->getAuth()){
			// Akcja wymaga autoryzacji, która się nie powiodła.
			fb("Auth required");
		}else{
			// Sprawdź dodatkowe ograniczenia dostępu do akcji.
			if ( ($permission = $o->checkPermission()) === true )
			{
				$page = $o->execute();	
				$page = $page ? $page : $_GET['page']; 
				
				$variables = array_merge($o->getVariables(), $o->getRefs());
			}else{
				$variables = array('action_permission_denied'=> $permission);
				fb("PERMISSION: ".$permission);
			}		
		}		
		
		return $page;
	}
	
	/********************************************************************
	 * 
	 */
	function doAjax($ajax, &$variables){
	 	global $user, $corpus, $config;

		/** Process an ajax request */
		include($config->path_engine . "/ajax/a_{$ajax}.php");
		$class_name = "Ajax_{$ajax}";
		$o = new $class_name();
	
		if ( $o->isSecure && !$auth->getAuth() ) {
			echo json_encode(array("error"=>"Ta operacja wymaga autoryzacji.", "error_code"=>"ERROR_AUTHORIZATION"));				
		}	
		elseif ( ($permission = $o->checkPermission()) === true ) {
			if (is_array($variables))		
				$o->setVariables($variables);
			return $o->execute();	
		}
		else {
			echo json_encode(array("error"=>$permission));		
		}
		
	}

	/********************************************************************
	 * 
	 */
	 function doPage($page, &$variables){
	 	global $user, $corpus, $config;
	 	
		$stamp_start = time();

		/** Show a page content */
		// If the page is not set the set the default 'home'
		$page = $page ? $page : 'home';
	
		// If the required module does not exist, change it silently to the default.
		if (!file_exists($config->path_engine . "/pages/{$page}.php"))
			$page = "home"; 

		require_once ($config->path_engine . "/pages/{$page}.php");
		$page_class_name = "Page_{$page}";	
		$o = new $page_class_name();
		if (is_array($variables))	
			$o->setVariables($variables);
		
		/** The user is logged in or the page is not secured */
		
		// Assign objects to the page		
		$o->set('user', $user);
		$o->set('page', $page);
		$o->set('corpus', $corpus);
		$o->set('release', RELEASE);
		$o->loadAnnotations();


		// Check, if the current user can see the real content of the page
		if ( hasRole('admin') 
				|| isCorpusOwner()
	    		|| ( count($o->roles) > 0 
	    				&& isset($user)
	    				&& count( array_intersect( array_keys($user['role']), $o->roles)) > 0
	    				&& $o->checkPermission() === true ) 
				|| ( count($o->roles) == 0 
						&& $o->checkPermission() === true ) ) {
							
			/* User can see the page */
			$o->execute();
			
			if (file_exists($config->path_www . "/js/page_{$page}.js")){
				$o->set('page_js_file', $config->url . "/js/page_{$page}.js");
			}
		}
		else{
			
			/** User cannot see the page */
			$page = 'norole';
		}
	
		$page_generation_time = (time() - $stamp_start);
	
		$o->set('page_generation_time', $page_generation_time);
		$o->display($page);	 	
	 }

	/********************************************************************
	 * Generate the output that will be send to the browser. 
	 * Determine the type of acction according to $_GET and $_POST arrays.
	 */
	function execute(){
		global $config, $user, $auth;
				
		$variables = array();
		$action = $_POST['action'];
		$page = $_GET['page'];
		$ajax = $_GET['ajax'];
																
		if ($action && file_exists($config->path_engine . "/actions/a_{$action}.php"))
			$page = $this->doAction($action, $variables);
		
		if ($ajax)
			$this->doAjax($ajax, $variables);
		else
			$this->doPage($page, $variables);			
	}

}

?>
