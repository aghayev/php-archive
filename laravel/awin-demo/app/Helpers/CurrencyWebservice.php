<?php

namespace App\Helpers;

/**
 * Dummy web service returning random exchange rates
 *
 */
class CurrencyWebservice
{

    /**
     * @todo return random value here for basic currencies like GBP USD EUR (simulates real API)
     * ...the report should be in GBP.!
     *
     */
    public function getExchangeRate($currency)
    {
        switch ($currency) {
            case 'GBP':
                return 1;
                break;
            case 'EUR':
                return 1.16;
                break;
            case 'USD':
                return 1.30;
                break;
        }
    }
}