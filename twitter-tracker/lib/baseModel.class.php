<?php

/**
 *  Base Model
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
class baseModel {

    private static $instance;

    /**
     * Db Connector
     */	
    public static function getInstance() {

        if (empty(self::$instance)) {
            self::$instance = new dal();
            self::$instance->connect();
        }

        return self::$instance;
    }

}
