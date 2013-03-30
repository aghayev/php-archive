<?php

/**
 *  Router.
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
class router {
	
     /***
      * Make full path to controller file
      */
      static public function makePath($controllerName) {
	return CONTROLLER_PATH .'/'. $controllerName . 'Controller.class.php';      	      
      }

      /**
       * Get Url Params
       */
      static private function getParams()
      {
      	      $uri = $_SERVER['REQUEST_URI'];
      	      $parts = explode('/', $uri);
      	      
      	      $params = new stdClass();
      	      $params->controller = $parts[1] ? $parts[1] : null;
      	      $params->id = $parts[2] ? $parts[2] : null;

		if(!$params->controller)
		{
			$params->controller = 'index';	
		}
      	      
      	      return $params;
      }

     /***
      * Dispatch
      */
     static public function getController() {
 
     	     	$params = self::getParams();
		
		$file = self::makePath($params->controller);

		if (is_readable($file) == false)
		{
			$params->controller = 'error404';
			$file = self::makePath($params->controller);
		}

		include $file;

		$class = $params->controller . 'Controller';
		return new $class($params);
     }

}


