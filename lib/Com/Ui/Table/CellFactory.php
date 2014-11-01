<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Render an array of Dk objects to a table
 *
 *
 * @package Com
 */
class Com_Ui_Table_CellFactory extends Tk_Object
{
    
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    
    /**
     * Create A cell array from an array of parameter names
     *
     * @param string[] $array
     * @param string $actionParam
     * @param Tk_Type_Url $actionUrl
     * @return Com_Ui_Table_Cell[]
     */
    static function createCellsFromArray($array, $actionParam = 'id', $actionUrl = null, $actionUrlParam = '')
    {
        $cells = array();
        foreach ($array as $parameter => $type) {
            $cell = self::makeCell($parameter, $type);
            $cell->setAlign(Com_Ui_Table_Cell::ALIGN_RIGHT);
            if ($parameter == $actionParam) {
                $cell = self::makeCell($parameter, $type, $actionUrl, $actionUrlParam);
                $cell->setAlign(Com_Ui_Table_Cell::ALIGN_LEFT);
                $cell->setKey(true);
            }
            $cells[] = $cell;
        }
        return $cells;
    }
    
    /**
     * Make a column map
     *
     * @param string $property
     * @param string $type
     * @param Tk_Type_Url $actionUrl
     * @return Com_Ui_Table_Cell
     */
    static function makeCell($property, $type, $actionUrl = null, $actionUrlParam = '')
    {
        switch ($type) {
            case self::TYPE_BOOLEAN :
                $class = 'Com_Ui_Table_BooleanCell';
                break;
            case self::TYPE_INTEGER :
                $class = 'Com_Ui_Table_IntegerCell';
                break;
            case self::TYPE_FLOAT :
                $class = 'Com_Ui_Table_FloatCell';
                break;
            case self::TYPE_STRING :
                $class = 'Com_Ui_Table_Cell';
                break;
            default :
                if (substr($type, -4) == 'Cell') {
                    $class = $type;
                } else {
                    $class = $type . 'Cell';
                }
                $class = str_replace('Tk_Type_', 'Com_Type_', $class);
                
                if (!class_exists($class)) {
                    throw new Tk_ExceptionIllegalArgument("Could not find Table Cell `$class'.");
                }
                if (!is_subclass_of($class, 'Com_Ui_Table_Cell')) {
                    throw new Tk_ExceptionIllegalArgument("`$class' is not a Com_Ui_Table_Cell.");
                }
        }
        return new $class(ucfirst(preg_replace('/[A-Z]/', ' $0', $property)), $property, $actionUrl, $actionUrlParam);
    }

}