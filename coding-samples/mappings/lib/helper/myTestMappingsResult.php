<?php

/**
 * This Data Object keeps fixtures loaded by csVasTestHelper::getMappings
 * 
 */
class myTestMappingsResult
{
    private $timeOffset = null;
    private $inOffset = null;
    
    private $activeMms = array();
    
    private $activeNickname = array();
    private $activeMsisdn = array();

    private $reserveNickname = array();
    private $reserveMsisdn = array();

    private $fakeNickname = array();
    private $fakeMsisdn = array();

    private $activeMisc = array();

    /**
     * Constructor
     *
     */
    public function __construct($config)
    {
        $this->timeOffset = $config['time_offset'];
        $this->inOffset = $config['in_offset'];
        
        $this->activeMms = $config['active_mms'];

        $this->activeNickname = $config['active_nickname'];
        $this->activeMsisdn = $config['active_msisdn'];

        $this->reserveNickname = $config['reserve_nickname'];
        $this->reserveMsisdn = $config['reserve_msisdn'];        

        $this->fakeNickname = $config['fake_nickname'];
        $this->fakeMsisdn = $config['fake_msisdn'];
        
        $this->activeMisc = $config['active_misc'];
    }

    /**
     * Get TimeOffset
     * 
     * @return type
     */
    public function getTimeOffset()
    {
        if (isset($this->timeOffset)) {
            return $this->timeOffset;
        }
    }

    /**
     * Get InOffset
     * 
     * @return type
     */
    public function getInOffset()
    {
        if (isset($this->inOffset)) {
            return $this->inOffset;
        }
    }
    
    /**
     * Has Active Nicknames
     *
     * @return string
     */
    public function hasActiveNicknames()
    {
        return count($this->activeNickname);
    }

    /**
     * Has Reserve Nicknames
     *
     * @return string
     */
    public function hasReserveNicknames()
    {
        return count($this->reserveNickname);
    }

    /**
     * Has Fake Nicknames
     *
     * @return string
     */
    public function hasFakeNicknames()
    {
        return count($this->fakeNickname);
    }
    
    /**
     * Gets Active Nicknames
     *
     * @return string
     */
    public function getActiveNicknames()
    {
        if (isset($this->activeNickname)) {
            return $this->activeNickname;
        }
    }

    /**
     * Gets Reserve Nicknames
     *
     * @return string
     */
    public function getReserveNicknames()
    {
        if (isset($this->reserveNickname)) {
            return $this->reserveNickname;
        }
    }
    
    /**
     * Gets Fake Nicknames
     *
     * @return string
     */
    public function getFakeNicknames()
    {
        if (isset($this->fakeNickname)) {
            return $this->fakeNickname;
        }
    }
    
    /**
     * Gets Active Nickname Number
     *
     * @return string
     */
    public function getActiveNickname($key)
    {
        if (isset($this->activeNickname[$key])) {
            return $this->activeNickname[$key];
        }
    }

    /**
     * Gets Reserve Nickname Number
     *
     * @return string
     */
    public function getReserveNickname($key)
    {
        if (isset($this->reserveNickname[$key])) {
            return $this->reserveNickname[$key];
        }
    }

    /**
     * Gets Fake Nickname Number
     *
     * @return string
     */
    public function getFakeNickname($key)
    {
        if (isset($this->fakeNickname[$key])) {
            return $this->fakeNickname[$key];
        }
    }

    /**
     * Sets Active Nickname Number
     *
     * @return string
     */
    public function setActiveNickname($key, $value)
    {
        $this->activeNickname[$key] = $value;
    }

    /**
     * Sets Reserve Nickname Number
     *
     * @return string
     */
    public function setReserveNickname($key, $value)
    {
        $this->reserveNickname[$key] = $value;
    }

    /**
     * Sets Fake Nickname Number
     *
     * @return string
     */
    public function setFakeNickname($key, $value)
    {
        $this->fakeNickname[$key] = $value;
    }

    /**
     * Has Active Msisdn
     *
     * @return string
     */
    public function hasActiveMsisdns()
    {
        return count($this->activeMsisdn);
    }

    /**
     * Has Reserve Msisdn
     *
     * @return string
     */
    public function hasReserveMsisdns()
    {
        return count($this->reserveMsisdn);
    }

    /**
     * Has Fake Msisdn
     *
     * @return string
     */
    public function hasFakeMsisdns()
    {
        return count($this->fakeMsisdn);
    }

    /**
     * Gets Active Msisdns
     *
     * @return string
     */
    public function getActiveMsisdns()
    {
        if (isset($this->activeMsisdn)) {
            return $this->activeMsisdn;
        }
    }

    /**
     * Gets Reserve Msisdns
     *
     * @return string
     */
    public function getReserveMsisdns()
    {
        if (isset($this->reserveMsisdn)) {
            return $this->reserveMsisdn;
        }
    }

    /**
     * Gets Fake Msisdns
     *
     * @return string
     */
    public function getFakeMsisdns()
    {
        if (isset($this->fakeMsisdn)) {
            return $this->fakeMsisdn;
        }
    }

    /**
     * Gets Active Msisdn Number
     *
     * @return string
     */
    public function getActiveMsisdn($key)
    {
        if (isset($this->activeMsisdn[$key])) {
            return $this->activeMsisdn[$key];
        }
    }

    /**
     * Gets Reserve Msisdn Number
     *
     * @return string
     */
    public function getReserveMsisdn($key)
    {
        if (isset($this->reserveMsisdn[$key])) {
            return $this->reserveMsisdn[$key];
        }
    }

    /**
     * Gets Fake Msisdn Number
     *
     * @return string
     */
    public function getFakeMsisdn($key)
    {
        if (isset($this->fakeMsisdn[$key])) {
            return $this->fakeMsisdn[$key];
        }
    }

    /**
     * Sets Active Msisdn Number
     *
     * @return string
     */
    public function setActiveMsisdn($key, $value)
    {
        $this->activeMsisdn[$key] = $value;
    }

    /**
     * Sets Reserve Msisdn Number
     *
     * @return string
     */
    public function setReserveMsisdn($key, $value)
    {
        $this->reserveMsisdn[$key] = $value;
    }

    /**
     * Sets Fake Msisdn Number
     *
     * @return string
     */
    public function setFakeMsisdn($key, $value)
    {
        $this->fakeMsisdn[$key] = $value;
    }

    /**
     * Gets Active Mms Array
     *
     * @return string
     */
    public function getActiveMms()
    {
        if (isset($this->activeMms)) {
            return $this->activeMms;
        }
    }

    /**
     * Gets Active Misc Array
     *
     * @return string
     */
    public function getActiveMisc()
    {
        if (isset($this->activeMisc)) {
            return $this->activeMisc;
        }
    }

    /**
     * Retrieves an array of parameters
     *
     * @return array An associative array of parameters
     */
    public function getAll()
    {
        return array('time_offset' => $this->timeOffset,
            'in_offset' => $this->inOffset,
            'active_mms' => $this->activeMms,
            'active_nickname' => $this->activeNickname,
            'active_msisdn' => $this->activeMsisdn,
            'reserve_nickname' => $this->reserveNickname,
            'reserve_msisdn' => $this->reserveMsisdn,
            'fake_nickname' => $this->fakeNickname,
            'fake_msisdn' => $this->fakeMsisdn,
            'active_misc' => $this->activeMisc);
    }

}
