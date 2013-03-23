<?php

/**
 *  Base Controller
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
abstract class baseController {

   private $paramsHolder = null;
   private $varHolder = null;

  /**
   * Class constructor
   */
   public function __construct($params) {
   	   $this->paramsHolder = $params;
   	   register_shutdown_function(array($this, 'view'));
 }

  /**
   * Virtual method execute
   */
  abstract function execute();

  /**
   * Set variable
   */
  public function __set($key, $value) {
	$this->varHolder[$key] = $value;
  }

  /**
   * Get variable
   */
  public function & __get($key) {
	return $this->varHolder[$key];
  }

  /**
   * Get parameter
   */
  public function getParam($key) {
	return $this->paramsHolder->$key;
  }
  
  /**
   * Set View
   */
  public function view() {  	  
	include $this->paramsHolder->controller . '.tpl';      	      
  }

}
