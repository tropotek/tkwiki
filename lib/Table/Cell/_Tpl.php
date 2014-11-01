<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The dynamic table Cell
 *
 *
 * @package Table
 */
class Table_Cell_ extends Table_Cell
{
    static function create($property, $name = '')
    {
        $obj = new self($property, $name);
        return $obj;
    }
    function getPropertyData($property, $obj)
    {
        $value = parent::getPropertyData($property, $obj);
        if ($value) {
            
        }
        return $value;
    }
}
