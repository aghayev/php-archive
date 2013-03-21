<?php

/**
 *  Index Controller
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
class indexController extends baseController {

    /**
     * Execute
     */
    public function execute() {
    	    $this->handles = Handles::selectAll();    	    
    }

}
