<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The base mapper object that controls the mapping of REST XML columns to objects
 * The format of the XML packet should be:
 * <ClassName>
 *   <parameter1></parameter1>
 *   <parameter2></parameter2>
 *   <parameter3></parameter3>
 * </ClassName>
 *
 * The parameters and ClassName's are case sensative.
 * The Class name is the classname without the prepended namespace.
 * EG:
 *   Ext_Obj_User = <User></User>
 *
 * The default prepend to locate a class is `Ext_Obj_`
 * This can be set in the Tk_Xml_Mapper::_constructor or the Tk_Xml_Mapper::getInstance() call.
 *
 * @package Tk
 */
class Tk_Xml_Mapper extends Tk_Object
{
    /**
     * @var Tk_Xml_Mapper
     */
    protected static $instance = null;
    
    /**
     * @var string
     */
    protected $prepend = 'Ext_Obj_';
    
    /**
     * @var Tk_Loader_DataMap
     */
    protected $dataMap = null;
    
    /**
     * __construct
     *
     * @param string $prepend The test to prepent a class instance
     */
    protected function __construct($prepend = 'Ext_Obj_')
    {
        $this->prepend = $prepend;
    }
    
    /**
     * Create a mapper with the selected data map,
     * If an object of Tk_Loader_Interface is supplied then the default getDataMap() function is used
     * to obtain the datamap.
     *
     * @param Tk_Loader_DataMap $dataMap Can also take a Tk_Loader_Interface type
     * @return Tk_Xml_Mapper
     */
    static function getInstance($dataMap, $prepend = 'Ext_Obj_')
    {
        if (self::$instance == null) {
            self::$instance = new self($prepend);
        }
        // Get the default data map
        if ($dataMap instanceof Tk_Loader_Interface) {
            $dataMap = $dataMap->getDataMap();
        } else if (!$dataMap instanceof Tk_Loader_DataMap) {
            throw new Tk_ExceptionIllegalArgument('Invalid datamap object: ' . get_class($dataMap));
        }
        self::$instance->setDataMap($dataMap);
        return self::$instance;
    }
    
    /**
     * Get the object mapper
     *
     * @return Dk_Loader_DataMap
     */
    function getDataMap()
    {
        return $this->dataMap;
    }
    
    /**
     * Set the data map object
     *
     * @param Tk_Loader_DataMap $dataMap
     */
    function setDataMap(Tk_Loader_DataMap $dataMap)
    {
        $this->dataMap = $dataMap;
    }
    
    /**
     * The class name this mapper is used for.
     *
     * @return string
     */
    function getClass()
    {
        return $this->dataMap->getClass();
    }
    
    /**
     * Create a
     *
     * @param Tk_Db_MyResult $result
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
     */
    function makeCollection(Tk_Db_MyResult $result, $tool = null)
    {
        //        $rows = array();
    //        foreach($result as $row) {
    //            $rows[] = $row;
    //        }
    //        if ($tool == null) {
    //            $tool = new Tk_Db_Tool();
    //        }
    //        $collection = new Tk_Loader_Collection($this->dataMap, $rows);
    //        $collection->setDbTool($tool);
    //        if ($tool->hasTotal()) {
    //            $total = $this->db->countQuery($this->db->getLastQuery());
    //            $tool->setTotalRows($total);
    //        } else {
    //            $tool->setTotalRows(count($rows));
    //        }
    //        $result->free();
    //        return $collection;
    }
    
    /**
     * Return an XML string of the object values according to the mapper
     *
     * @param Tk_Loader_Collection $list
     * @return string
     */
    function serialiseCollection(Tk_Loader_Collection $list)
    {
        $array = array();
        foreach ($list as $obj) {
            $array[] = $obj;
        }
        return $this->serialiseArray($array);
    }
    
    /**
     * Serialise an array of object defined by the mapper into an XML string
     *
     * @param array $list
     * @return string
     */
    function serialiseArray($list)
    {
        $xml = '';
        if (count($list) == 0) {
            return '';
        }
        $class = str_replace($this->prepend, '', $this->getClass());
        foreach ($list as $obj) {
            $str = '';
            $row = Tk_Loader_Factory::getInstance()->getObjectValues($obj);
            foreach ($this->dataMap->getIdPropertyList() as $map) {
                $str .= $this->makeProperty($map, $row) . "\n";
            }
            foreach ($this->dataMap->getPropertyList() as $map) {
                $str .= $this->makeProperty($map, $row) . "\n";
            }
            $xml .= sprintf("  <%s>\n%s  </%s>\n", $class, $str, $class);
        }
        $xml = sprintf("<%s>\n%s</%s>", $this->dataMap->getDataSrc(), $xml, $this->dataMap->getDataSrc());
        return $xml;
    }
    
    /**
     * Create a name value element for the xml
     *
     * @param Tk_Loader_PropertyMap $map
     * @param array $row
     * @return string
     */
    private function makeProperty($map, $row)
    {
        $type = '';
        if (false) {
            $type = ' type="' . $map->getPropertyType() . '"';
        }
        return sprintf('    <%s%s>%s</%s>', $map->getColumnName(), $type, $map->getColumnValue($row), $map->getColumnName());
    }
    
    function unserialiseCollection($array)
    {
    
    }

}
?>