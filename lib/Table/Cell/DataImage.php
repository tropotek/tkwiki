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
class Table_Cell_DataImage extends Table_Cell
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
            
            $url = Tk_Type_Url::createDataUrl($this->getPropertyData($this->property, $obj));
            $str = '<a href="' . htmlentities($url->toString()) . '"><img src="'.$url->toString().'" alt="" height="50" /></a>';
        } else {
            $url = Tk_Type_Url::createDataUrl($this->getPropertyData($this->property, $obj));
            $str = '<a href="' . $url->toString() . '" class="lightbox"><img src="'.$url->toString().'" alt="" height="50" /></a>';
            //$str = htmlentities($this->getPropertyData($this->property, $obj));
        }
        return $str;
    }
    
}
