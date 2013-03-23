<?php

/**
 *  Texts Controller
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
class textsController extends baseController {

    /**
     * Execute
     */
    public function execute() {
    	    if ($this->getParam('id')) {
   	    	   $this->texts = Texts::selectAll($this->getParam('id'));
    	    }
    }

}
