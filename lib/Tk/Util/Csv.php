<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The aim is to provide functionality to read, write and modify csv data
 *
 * To create a csv object use:<br/>
 *   Tk_Util_Csv::load('/somefile.csv');
 *
 * @package Tk
 * @todo See if we need to keep this object.
 */
class Tk_Util_Csv extends Tk_Object
{
    
    /**
     * A multidimentional array that will be converted to a csv file
     * @var array
     */
    private $data = array();
    
    /**
     * @var array
     */
    private $headings = array();
    
    /**
     * @var string
     */
    private $delimiter = ',';
    
    /**
     * @var string
     */
    private $enclosure = '"';
    
    /**
     *
     *
     * @param string $delimiter Default ,
     * @param string $enclosure default "
     */
    function __construct($delimiter = ',', $enclosure = '"')
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
    }
    
    /**
     * Set the column labels
     *
     * @param array $labels
     */
    function setHeadings($arr)
    {
        $this->headings = $arr;
    }
    
    /**
     * Get the column labels
     *
     * @return array
     */
    function getHeadings()
    {
        return $this->headings;
    }
    
    /**
     * Set the data array
     *
     * @param array $data
     */
    function setData($data)
    {
        $this->data = $data;
    }
    
    /**
     * Get the data multidimentional array
     *
     * @return array
     */
    function getData()
    {
        return $this->data;
    }
    
    /**
     * Add a row of data
     *
     * @param array $row
     */
    function addRow($row)
    {
        $this->data[] = $row;
    }
    
    /**
     * Get a row of data
     *
     * @param integer $i
     * @return array
     */
    function getRow($i)
    {
        if (isset($this->data[$i])) {
            return $this->data[$i];
        }
    }
    
    /**
     * Get a cell's data
     *
     * @param integer $row
     * @param integer $col
     * @return string
     */
    function getCell($row, $col)
    {
        if (isset($this->data[$row][$col])) {
            return $this->data[$row][$col];
        }
    }
    
    /**
     * load
     *
     * @param string $file
     * @param boolean $hasHeadings
     * @param string $delimiter
     * @param string $enclosure
     * @return Tk_Util_Csv
     * @throws Sdk_IllegalArgumentException
     */
    static function load($file, $hasHeadings = false, $delimiter = ',', $enclosure = '"')
    {
        $row = 1;
        $rows = array();
        $headings = array();
        if (!is_readable($file)) {
            throw new Tk_ExceptionIllegalArgument('File Cannot be read. Check file exists.');
        }
        
        if (($fp = fopen($file, 'r')) === false) {
            return null;
        }
        
        while (($data = fgetcsv($fp, 2000, $delimiter, $enclosure)) !== false) {
            if (!($hasHeadings == false) && ($row == 1)) {
                $headings = $data;
            } elseif (!($hasHeadings == false)) {
                foreach ($data as $key => $value) {
                    unset($data[$key]);
                    $data[$headings[$key]] = $value;
                }
                $rows[] = $data;
            } else {
                $rows[] = $data;
            }
            $row++;
        }
        
        fclose($fp);
        $csv = new Tk_Util_Csv($delimiter = ',', $enclosure = '"');
        if ($hasHeadings && (count($headings) > 0)) {
            $csv->setHeadings($headings);
        }
        
        $csv->setData($rows);
        return $csv;
    }
    
    /**
     * Save this object into a csv format
     *
     * @param string $file
     * @return boolean
     */
    function save($file)
    {
        if (($fp = fopen($file, 'w')) === false) {
            return false;
        }
        if (count($this->headings) > 0) {
            fputcsv($fp, $this->headings, $this->delimiter, $this->enclosure);
        }
        foreach ($this->data as $line) {
            $sorted = array();
            if (count($this->headings) > 0) {
                foreach ($this->headings as $v) {
                    $sorted[$v] = $line[$v];
                }
            } else {
                $sorted = $line;
            }
            fputcsv($fp, $sorted, $this->delimiter, $this->enclosure);
        }
        fclose($fp);
        return true;
    }
    
    /**
     * toString
     *
     * @return string
     */
    function toString()
    {
        $csvData = '';
        $row = '';
        if (count($this->headings) > 0) {
            foreach ($this->headings as $heading) {
                $row .= self::csvEncode($heading) . $this->delimiter;
            }
            $csvData .= substr($row, 0, -1) . "\015\012";
        }
        foreach ($this->data as $item) {
            $row = '';
            foreach ($this->headings as $v) {
                $row .= self::csvEncode($item[$v]) . $this->delimiter;
            }
            $csvData .= substr($row, 0, -1) . "\015\012";
        }
        return $csvData;
    }
    /**
     * This version is conditional - it only adds quotes if needed:
     *
     * @param string $str
     * @return string
     * @notes Used for the toString function only
     */
    private function csvEncode($str)
    {
        $count = 0;
        $str = str_replace(array($this->enclosure, $this->delimiter, "\n", "\r"), array($this->enclosure . $this->enclosure, $this->delimiter, "\n", "\r"), $str, $count);
        if ($count) {
            return $this->enclosure . $str . $this->enclosure;
        } else {
            return $str;
        }
    }
}