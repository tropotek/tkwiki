<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @author Darryl Ross <darryl.ross@aot.com.au>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Represents a currency.
 * Currencies are identified by their currency codes.
 *  o AUSTRALIA    Australian Dollar   AUD  036
 *  o NEW ZEALAND  New Zealand Dollar  NZD  554
 *
 * The class is designed so that there's never more than one Currency instance
 * for any given currency. Therefore, there's no public constructor. You obtain
 * a Currency instance using the getInstance methods.
 *
 * @link http://www.iso.org/iso/en/prods-services/popstds/currencycodeslist.html
 * @package Tk
 */
class Tk_Type_Currency extends Tk_Object
{
    
    /**
     * @var string
     */
    private $currencyCode = '';
    
    /**
     * @var array
     */
    private static $objects = array();
    
    /**
     * @var array
     */
    static $currencyList = array('AUD' => array('name' => 'Australian Dollar', 'locale' => 'Australia', 'symbol' => '$', 'altSymbol' => 'AUD$', 'digits' => 2), 'NZD' => array('name' => 'New Zealand Dollar', 'locale' => 'New Zealand', 'symbol' => '$', 'altSymbol' => 'NZD$', 'digits' => 2), 'USD' => array('name' => 'US Dollar', 'locale' => 'United Stated Of America', 'symbol' => '$', 'altSymbol' => 'USD$', 'digits' => 2), 'THB' => array('name' => 'Thai Baht', 'locale' => 'Thailand', 'symbol' => 'THB', 'altSymbol' => 'THB$', 'digits' => 2));
    
    /**
     * __construct
     *
     * @param string $currencyCode (optional) Default value is 'AUD'
     */
    private function __construct($currencyCode = 'AUD')
    {
        $this->currencyCode = $currencyCode;
    }
    
    /**
     * Returns the Currency instance for the given currency code.
     *
     * @return Tk_Type_Currency
     * @throws Tk_ExceptionIllegalArgument
     */
    static function getInstance($currencyCode = 'AUD')
    {
        if (!array_key_exists($currencyCode, Tk_Type_Currency::$currencyList)) {
            throw new Tk_ExceptionIllegalArgument('Invalid Currency code: ' . $currencyCode);
        }
        if (!isset(Tk_Type_Currency::$objects[$currencyCode])) {
            Tk_Type_Currency::$objects[$currencyCode] = new Tk_Type_Currency($currencyCode);
        }
        return Tk_Type_Currency::$objects[$currencyCode];
    }
    
    /**
     * Gets the ISO 4217 currency code of this currency.
     *
     * @return string
     */
    function getCurrencyCode()
    {
        return $this->currencyCode;
    }
    
    /**
     * Gets the symbol of this currency for the specified locale.
     *
     * For example, for the US Dollar, the symbol is "$" if the specified
     * locale is the US, while for other locales it may be "US$". If no
     * symbol can be determined, the ISO 4217 currency code is returned.
     *
     * If locale is null, then the default locale is used.
     *
     * @param string $locale (optional) The locale, default 'AUSTRALIA'.
     * @return string The symbol of this currency for the specified locale.
     */
    function getSymbol($code = 'AUD')
    {
        if ($code == $this->currencyCode) {
            return Tk_Type_Currency::$currencyList[$this->currencyCode]['symbol'];
        } else {
            return Tk_Type_Currency::$currencyList[$this->currencyCode]['altSymbol'];
        }
    }
    
    /**
     * Gets the default number of fraction digits used with this currency.
     *
     * For example, the default number of fraction digits for the Euro is 2,
     * while for the Japanese Yen it's 0. In the case of pseudo-currencies,
     * such as IMF Special Drawing Rights, -1 is returned.
     *
     * @return integer
     */
    function getDefaultFractionDigits()
    {
        return Tk_Type_Currency::$currencyList[$this->currencyCode]['digits'];
    }
}