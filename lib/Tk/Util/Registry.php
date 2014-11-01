<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A registry object.
 *
 * @package Tk
 */
class Tk_Util_Registry extends Tk_Object
{
    
    /**
     * @var array
     */
    private $cache = array();
    
    /**
     * Parse a config file either XML or INI
     *
     * @param Tk_Type_Path $file
     * @param string $prependKey
     * @throws Tk_ExceptionRuntime
     */
    function parseConfigFile($file, $prependKey = '',  $overwrite = true)
    {
        if (!$file instanceof Tk_Type_Path) {
            throw new Tk_ExceptionIllegalArgument('Invalid file path type.');
        }
        if (!$file->isReadable()) {
            return;
        }
        if ($file->getExtension() == 'ini') {
            $array = $this->parseIniFile($file);
        } elseif ($file->getExtension() == 'xml') {
            $array = $this->parseXmlFile($file);
        } elseif ($file->getExtension() == 'php') {
            $array = $this->parsePhpFile($file);
        } else {
            throw new Tk_ExceptionRuntime('Invalid config file: ' . $file->toString());
        }
        if ($prependKey) {
            $arr = array();
            foreach ($array as $k => $v) {
                $nk = $prependKey.'.'.$k;
                $arr[$nk] = $v;
            }
            $array = $arr;
        }
        $this->load($array, $overwrite);
    }
    
    /**
     * Load the values using the setter methods if available
     * NOTE: The array must be a one dimensional array
     *
     * @param array $array The array of parameters to find setters for
     */
    function load($array,  $overwrite = true)
    {
        foreach ($array as $k => $v) {
            if ($overwrite) {
                $method = 'set' . ucfirst($k);
                if (method_exists($this, $method)) {
                    $this->$method($v);
                } else {
                    $this->setEntry($k, $v);
                }
            } else {
                if (!$this->entryExists($k)) {
                    $this->setEntry($k, $v);
                }
            }
        }
    }
    
    /**
     * Read and apply the ini file to the registry.
     *
     * @param Tk_Type_Path $iniFile
     */
    private function parseIniFile(Tk_Type_Path $iniFile)
    {
        $array = parse_ini_file($iniFile->toString(), false);
        return $array;
    }
    
    /**
     * read a php config file. The file cshould be in the following format.
     * <code>
     *   $config['database.default.host'] = 'hostname';
     *   $config['system.timezone'] = 'Australia';
     *
     * </code>
     * @param Tk_Type_Path $file
     */
    private function parsePhpFile(Tk_Type_Path $file)
    {
        $config = array();
        require $file->toString();
        return $config;
    }
    
    /**
     * Parse a config xml file.
     * Example Config File:
     *  <code>
     *    <config>
     *     <name1>value1</name1>
     *     <name2>value2</name2>
     *     <name3 type="boolean">true</name3>
     *    </config>
     * </code>
     *
     * @param Tk_Type_Path $xmlFile
     */
    private function parseXmlFile(Tk_Type_Path $xmlFile)
    {
        $doc = DOMDocument::load($xmlFile->toString());
        $firstChild = $doc->documentElement;
        $array = array();
        foreach ($firstChild->childNodes as $node) {
            if ($node->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $k = $node->nodeName;
            $v = $node->nodeValue;
            $type = 'string';
            if ($node->hasAttribute('type')) {
                $type = $node->getAttribute('type');
            }
            if ($type == 'boolean') {
                $v = (strtolower($v) == 'true' || strtolower($v) == 'yes' || strtolower($v) == '1');
            }
            $array[$k] = $v;
        }
        return $array;
    }
    
    
    /**
     * Set an entry into the registry cache
     *
     * @param string $key
     * @param object $item
     */
    protected function setEntry($key, $item)
    {
        Tk::setDotKey($this->cache, $key, $item);
    }
    
    /**
     * Return an entry from the registry cache
     *
     * @param string $key
     * @return object
     */
    protected function getEntry($key)
    {
        return Tk::getDotKey($this->cache, $key);
    }
    
    
    /**
     * Test if an entry exists
     *
     * @param string $key
     * @return boolean
     */
    protected function entryExists($key)
    {
        return Tk::existsDotKey($this->cache, $key);
    }
    
    
    /**
     * Return a string representation of the registry object
     *
     * @return string
     */
    function toString($cache = null)
    {
        if (!$cache) {
            $cache = $this->cache;
        }
        ksort($cache);
        $str = "";
        foreach ($cache as $k => $v) {
            if (is_object($v)) {
                $str .= "[$k] => {" . get_class($v) . "}\n";
            } elseif (is_array($v)) {
                $str .= "[$k] =>  " . print_r($v, true) . "\n";
            } else {
                $str .= "[$k] => $v\n";
            }
        }
        return $str;
    }
    
}