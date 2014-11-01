<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @author Darryl Ross <darryl.ross@aot.com.au>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The Money object.
 *
 * @package Tk
 */
class Tk_Type_Money extends Tk_Object
{
    
    /**
     * Default Currency code
     * @var string
     */
    public static $defaultCurrencyCode = 'AUD';
    
    /**
     * @var integer
     */
    private $amount = 0;
    
    /**
     * @var string
     */
    private $currencyCode = 'AUD';
    
    /**
     * @var Tk_Type_Currency
     */
    private $currency = null;
    
    /**
     *
     *
     * @param integer $amount The amount in cents.
     * @param Tk_Type_Currency $currency The currency, Default 'AUD'.
     */
    function __construct($amount = 0, $currency = null)
    {
        $this->amount = intval($amount);
        if ($currency == null) {
            $this->currency = Tk_Type_Currency::getInstance(self::$defaultCurrencyCode);
        } else {
            $this->currency = $currency;
        }
    }
    
    /**
     * Create a money object
     *
     * @return Tk_Type_Money
     */
    static function create($amount = 0, $currency = null)
    {
        return new self($amount, $currency);
    }
    
    /**
     * Create a money object
     *
     * @return Tk_Type_Money
     * @deprecated  Use ::create()
     */
    static function createMoney($amount = 0, $currency = null)
    {
        return self::create($amount, $currency);
    }
    
    /**
     * Create a money object from a string
     *
     * @param string $amount An amount string: '200.00', '$200.00'
     * @return Tk_Type_Money Returns null on invalid format
     */
    static function parseFromString($amount, $currency = null)
    {
        if (!($currency instanceof Tk_Type_Currency)) {
            $currency = Tk_Type_Currency::getInstance(self::$defaultCurrencyCode);
        } else {
            $currency = $currency;
        }
        $digits = $currency->getDefaultFractionDigits();
        if (!preg_match("/^(\$)?(\-)?[0-9]+((\.)[0-9]{1,{$digits}})?$/", $amount)) {
            return null;
        }
        $amount = str_replace(array(',', '$'), array('', ''), $amount);
        $amount = floatval($amount);
        return new self($amount * 100, $currency);
    }
    
    /**
     * Serialise Read
     *
     */
    function __wakeup()
    {
        $this->currency = Tk_Type_Currency::getInstance($this->currencyCode);
    }
    
    /**
     * Serialise Write.
     *
     * @return array
     */
    function __sleep()
    {
        $this->currencyCode = $this->currency->getCurrencyCode();
        $class = "\0" . __CLASS__ . "\0";
        return array($class . "amount", $class . "currencyCode");
    }
    
    /**
     * Returns the amount in cents.
     *
     * @return integer
     */
    function getAmount()
    {
        return $this->amount;
    }
    
    /**
     * Returns the currency.
     *
     * @return Tk_Type_Currency
     */
    function getCurrency()
    {
        return $this->currency;
    }
    
    /**
     * Adds the value of another instance of money and returns a new instance.
     *
     * @param Tk_Type_Money $other
     * @return Tk_Type_Money
     */
    function add(Tk_Type_Money $other)
    {
        $this->assertCurrency($other);
        return new self($this->amount + $other->amount);
    }
    
    /**
     * Subtracts the value of another instance of money and returns a new instance.
     *
     * @param Tk_Type_Money $other
     * @return Tk_Type_Money
     */
    function subtract(Tk_Type_Money $other)
    {
        $this->assertCurrency($other);
        return new self($this->amount - $other->amount);
    }
    
    /**
     * Divide the amount by the denominator.
     *
     * @param float $denominator
     * @throws Tk_IllegalArgumentException
     */
    function divideBy($denominator)
    {
        if ($denominator === 0) {
            throw new Tk_IllegalArgumentException('Divide by zero exception.');
        }
        return new self($this->amount / $denominator);
    }
    
    /**
     * Multiplies the value of the money by an amount and returns a new instance.
     *
     * @param double $multiplyer
     * @return Tk_Type_Money
     */
    function multiply($multiplyer)
    {
        return new self((int)round($this->amount * $multiplyer), $this->currency);
    }
    
    /**
     * return an absolute value of this money object
     *
     * @return Tk_Type_Money
     */
    function abs()
    {
        return new self((int)abs($this->amount), $this->currency);
    }
    
    /**
     * Compares the value to another instance of money.
     *
     * @param Tk_Type_Money $other
     * @return integer Returns the difference, 0 = equal.
     */
    function compareTo(Tk_Type_Money $other)
    {
        $this->assertCurrency($other);
        return $this->getAmount() - $other->getAmount();
    }
    
    /**
     * Checks if the money value is greater than the value of another instance of money.
     *
     * @param Tk_Type_Money $other
     * @return boolean
     */
    function greaterThan(Tk_Type_Money $other)
    {
        return $this->compareTo($other) > 0;
    }
    
    /**
     * Checks if the money value is greater than or equal the value of another instance of money.
     *
     * @param Tk_Type_Money $other
     * @return boolean
     */
    function greaterThanEqual(Tk_Type_Money $other)
    {
        return ($this->compareTo($other) > 0) || ($other->amount == $this->amount);
    }
    
    /**
     * Checks if the money value is less than the value of another instance of money.
     *
     * @param Tk_Type_Money $other
     * @return boolean
     */
    function lessThan(Tk_Type_Money $other)
    {
        return $this->compareTo($other) < 0;
    }
    
    /**
     * Checks if the money value is less than or equal the value of another instance of money.
     *
     * @param Tk_Type_Money $other
     * @return boolean
     */
    function lessThanEqual(Tk_Type_Money $other)
    {
        return ($this->compareTo($other) < 0) || ($other->amount == $this->amount);
    }
    
    /**
     * Checks if the money value is equal to the value of another instance of money.
     *
     * @param Tk_Type_Money $other
     * @return boolean
     */
    function equals(Tk_Type_Money $other)
    {
        return ($this->compareTo($other) == 0);
    }
    
    /**
     * Return a formatted string to the nearest dollar representing the currency
     *
     * @return string
     */
    function toNearestDollarString()
    {
        $amount = round(($this->getAmount() / 100) + .205);
        return $this->currency->getSymbol() . $amount;
    }
    
    /**
     * Return a string amount as a 2 point presision float. Eg: '200.00'
     *
     * @return string
     */
    function toFloatString()
    {
        return sprintf("%.02f", ($this->getAmount() / 100));
    }
    
    /**
     * Return a formatted string representing the currency
     *
     * @return string
     */
    function toString($decSep = '.', $thousandthSep = ',')
    {
        $strValue = $this->currency->getSymbol($this->getCurrency()->getCurrencyCode()) . number_format(($this->getAmount() / 100), $this->currency->getDefaultFractionDigits(), $decSep, $thousandthSep);
        return $strValue;
    }
    
    /**
     * Test for the same currency instance
     *
     * @param Tk_Type_Money $arg
     * @throws Tk_Exception
     */
    private function assertCurrency(Tk_Type_Money $arg)
    {
        if ($this->currency !== $arg->currency) {
            throw new Tk_ExceptionIllegalArgument('Money math currency instance mismatch.');
        }
    }

}
?>
