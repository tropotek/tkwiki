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
abstract class Table_Cell extends Table_Element
{
    const ORDER_NONE = '';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';


    /**
     * This will be used for the cell header title
     * @var string
     */
    protected $label = '';

    /**
     * This is the row object's property name to access
     * it could be a getter starting with is, get, has or a public property
     * @var string
     */
    protected $property = '';

    /**
     * All classes appended to this tableData cell
     * @var array
     */
    protected $class = array();

    /**
     * Any classes to append to this cell's parent <tr>
     * @var array
     */
    protected $rowClass = array();

    /**
     * This cell will contain the 'key' css class
     * @var boolean
     */
    protected $key = false;

    /**
     * This cell's orderby property
     * By default this is set to use the cell property
     * if '' is used then ordering will be dissabled for this cell
     * @var boolean
     */
    protected $orderProperty = null;

    /**
     * @var Tk_Type_Url
     */
    protected $url = null;

    /**
     * @var array
     */
    protected $urlPropertyList = array();




    /**
     * Create
     *
     * @param string $property
     * @param string $name If null the property name is used EG: 'propName' = 'Prop Name'
     * @param string $orderProperty The header order property field (Default: $property)
     */
    function __construct($property, $label = null)
    {
        $this->property = $property;
        if (!$label) {
            $label = ucfirst(preg_replace('/[A-Z]/', ' $0', $property));
        }
        $this->label = $label;
        $this->setOrderProperty($property);
    }

    /**
     * (non-PHPdoc)
     * @see Table_Element::execute()
     */
    function execute($list) { }

    /**
     * Set the default cell data url
     *
     * @param Tk_Type_Url $url
     * @param array $urlPropertyList
     */
    function setUrl($url, $urlPropertyList = null)
    {
        $this->url = $url;
        if ($urlPropertyList) {
            $this->setUrlPropertyList($urlPropertyList);
        }
        return $this;
    }

    /**
     * Get the default data URL
     *
     * @return Tk_Type_Url
     */
    function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the url property list
     *
     * @param array $list
     * @return Tk_Type_Url
     */
    function setUrlPropertyList($list)
    {
        if (!is_array($list)) {
            $list = array($list);
        }
        $this->urlPropertyList = $list;
        return $this;
    }

    /**
     * Set the property that the order header uses by default this is the same as property
     *
     * @param string $orderProperty
     * @return Table_Cell
     */
    function setOrderProperty($orderProperty)
    {
        $this->orderProperty = $orderProperty;
        return $this;
    }

    /**
     * Get the order by property name
     *
     * @return string
     */
    function getOrderProperty()
    {
        return $this->orderProperty;
    }

    /**
     * Get the cell label
     *
     * @return string
     */
    function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the cell label
     *
     * @param string $str
     * @return Table_Cell
     */
    function setLabel($str)
    {
        $this->label = $str;
        return $this;
    }

    /**
     * Get the object property name to get data from
     *
     * @return string
     */
    function getProperty()
    {
        return $this->property;
    }

    /**
     * Add a row css class
     *
     * @param string $class
     * @return Table_Cell
     */
    function addRowClass($class)
    {
        $this->rowClass[$class] = $class;
        return $this;
    }

    /**
     * remove a row css class
     *
     * @param string $class
     * @return Table_Cell
     */
    function removeRowClass($class)
    {
        unset($this->rowClass[$class]);
        return $this;
    }

    /**
     * reset and clear the row class
     *
     * @return Table_Cell
     */
    function clearRowClass()
    {
        $this->rowClass = array();
        return $this;
    }

    /**
     * Get the css row class list
     *
     * @return array
     */
    function getRowClassList()
    {
        return $this->rowClass;
    }

    /**
     * Add a cell css class
     *
     * @param string $class
     * @return Table_Cell
     */
    function addClass($class)
    {
        $this->class[$class] = $class;
        return $this;
    }

    /**
     * remove a css class
     *
     * @param string $class
     * @return Table_Cell
     */
    function removeClass($class)
    {
        unset($this->class[$class]);
        return $this;
    }
    /**
     * reset and clear the class array
     *
     * @return Table_Cell
     */
    function clearClass()
    {
        $this->class = array();
        return $this;
    }

    /**
     * Get the css class list
     *
     * @return array
     */
    function getClassList()
    {
        return $this->class;
    }

    /**
     * Set the key cell property
     *
     * @param boolean $b
     */
    function setKey($b = true)
    {
        $this->key = ($b === true);
        return $this;
    }

    /**
     * Is this cell a key cell
     * @return boolean
     */
    function isKey()
    {
        return ($this->key === true);
    }

    /**
     * Get the property value from the object using the supplied property name
     *
     * @param string $property
     * @param stdClass $obj
     * @return string
     */
    function getPropertyData($property, $obj)
    {
        if (!$this->property || !$obj) {
            return '';
        }
        // Get property by method if accessor exists
        $method = 'get' . ucfirst($this->property);
        if (!method_exists($obj, $method)) {
            $method = 'is' . ucfirst($this->property);
        }
        if (!method_exists($obj, $method)) {
            $method = 'has' . ucfirst($this->property);
        }
        if (!method_exists($obj, $method)) {
            $method = '';
        }
        if ($method) {
            return $obj->$method();
        }

        // Get property value if a public property
        if (is_Array(get_object_vars($obj)) && in_array($this->property, get_object_vars($obj))) {
            $prop = $this->property;
            return $obj->$prop;
        }
        return '';
    }

    /**
     * get the table data from an object if available
     *   Overide getTd() to add data to the cell.
     *
     * @param Tk_Object $obj
     * @return Dom_Template Alternativly you can return a plain HTML string
     */
    function getTd($obj)
    {
        $this->rowClass = array(); // reset row class list
        $str = '';

        $url = $this->getUrl();
        if ($url) {
            if (count($this->urlPropertyList)) {
                foreach ($this->urlPropertyList as $prop) {
                    $url->set($prop, $this->getPropertyData($prop, $obj));
                }
            } else {
                $pos = strrpos(get_class($obj), '_');
                $name = substr(get_class($obj), $pos + 1);
                $prop = strtolower($name[0]) . substr($name, 1) . 'Id';
                $url->set($prop, $obj->getId());
            }
            $str = '<a href="' . htmlentities($url->toString()) . '">' . htmlentities($this->getPropertyData($this->property, $obj)) . '</a>';
        } else {
            $str = htmlentities($this->getPropertyData($this->property, $obj));
        }
        return $str;
    }

    /**
     * Get the table data from an object if available
     *   Overide getTh() to add new text to the header.
     *
     * @return Dom_Template Alternativly you can return a plain HTML string
     */
    function getTh()
    {
        $url = $this->getOrderUrl();
        if ($url) {
            $str = '<a href="' . htmlentities($url->toString()) . '" title="Click to order by: ' . $url->get($this->getEventKey('orderBy')) . '">' . $this->getLabel() . '</a>';
        } else {
            $str = '<a href="javascript:alert(\'This field is not sortable.\');" title="Not Sortable">' . $this->getLabel() . '</a>';
        }
        return $str;
    }

    /**
     * getOrderByUrl
     *
     * @return Tk_Type_Url
     */
    function getOrderUrl()
    {
        if (!$this->getOrderProperty()) {
            return;
        }
        $pre = '`' . $this->getOrderProperty() . '` ';
        $eventKey = $this->getEventKey('orderBy');
        $url = Tk_Request::requestUri();
        $url->delete($eventKey);

        $order = $this->getOrder();
        if ($order == self::ORDER_ASC) {
            $url->set($eventKey, $pre . self::ORDER_DESC);
        } else if ($order == self::ORDER_DESC) {
            $url->set($eventKey, self::ORDER_NONE);
        } else if ($order == self::ORDER_NONE) {
            $url->set($eventKey, $pre . self::ORDER_ASC);
        }
        return $url;
    }


    /**
     * Get the order status of this cell
     *
     * @return string
     * @todo: This does not take into account multiple orders EG: `id` DESC, `field2` ASC, etc
     *   Only the first one will be compared
     */
    function getOrder()
    {
        $pre = $this->orderProperty;
        $orderByStr = $this->getTable()->getDbTool()->getOrderBy();
        if (preg_match('/^(`)?' . $pre . '(`)? ' . self::ORDER_DESC . '/i', $orderByStr)) {
            return self::ORDER_DESC;
        } else if (preg_match('/^(`)?' . $pre . '(`)?( ' . self::ORDER_ASC . ')?/i', $orderByStr)) {
            return self::ORDER_ASC;
        }
        return self::ORDER_NONE;
    }

}
