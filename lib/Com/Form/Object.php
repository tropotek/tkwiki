<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A form to object mapper.
 *
 * Assumes Java Bean style naming convention are use in both the object and
 * the form. For example, if an object has a property dateFrom it is assumed
 * that the object as a setter method setDateFrom() and that the HTML form
 * contains a from element with name="dateFrom".
 *
 * @package Com
 */
class Com_Form_Object extends Tk_Object
{
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_STRING = 'string';
    const TYPE_ARRAY = 'array';
    const TYPE_FILE = 'file';
    
    /**
     * @var string
     */
    private $action = '';
    
    /**
     * @var array
     */
    private $errors = null;
    
    /**
     * @var array
     */
    private $fields = null;
    
    /**
     * @var array
     */
    private static $formInstances = array();
    
    /**
     * Holder for files that have to be delete b4 being moved
     * Mapped by field name
     * @var array
     */
    private $origFilePaths = array();
    
    /**
     * Unique class value not attribute value
     * @var integer
     */
    private $formId = 1;
    
    
    
    /**
     * Known types are:
     *  o integer
     *  o string
     *  o boolean
     *
     * If the type is not known the an attempt will be made to load {type}Field, to do the mapping.
     *
     * @param string $id
     * @param Tk_Type_Url $action
     */
    function __construct($id = 'form', $action = null)
    {
        $this->id = $id;
        if ($action instanceof Tk_Type_Url) {
            $this->setAction($action->toString());
        } elseif ($action == '') {
            $this->setAction(Tk_Request::getInstance()->getRequestUri());
        } else {
            $this->setAction($action);
        }
        if (!array_key_exists($this->id, self::$formInstances)) {
            self::$formInstances[$this->id] = 1;
        } else {
            $this->formId = self::$formInstances[$this->id]++;
        }
        
        $this->fields = array();
        $this->errors = array();
    }
    
    /**
     * Adds a field to the form.
     *
     * Where $type is one of integer, string, boolean, or an iRes class. A
     * call to getValue will return a value of the type specified for the
     * field, or null if the data submitted cannot be converted to the type.
     *
     * @param string $name The field name.
     * @param string $type The field type. Defaults to string.
     * @param Tk_Type_Path $path (optional) For 'file' fields only. The destination directory of the uploaded file
     * @return Com_Form_Field
     */
    function addField($name, $type = 'string', $path = null)
    {
        switch ($type) {
            case self::TYPE_STRING :
                $this->fields[$name] = new Com_Form_StringField($name);
                break;
            case self::TYPE_INTEGER :
                $this->fields[$name] = new Com_Form_IntegerField($name);
                break;
            case self::TYPE_FLOAT :
                $this->fields[$name] = new Com_Form_FloatField($name);
                break;
            case self::TYPE_BOOLEAN :
                $this->fields[$name] = new Com_Form_BooleanField($name);
                break;
            case self::TYPE_ARRAY :
                $this->fields[$name] = new Com_Form_ArrayField($name);
                break;
            case self::TYPE_FILE :
                if ($path instanceof Tk_Type_Path) {
                    $this->fields[$name] = new Com_Form_FileField($name, $path);
                } elseif (is_string($path)) {
                    $this->fields[$name] = new Com_Form_FileField($name, new Tk_Type_Path($path));
                }
                break;
            default :
                $class = $type . 'Field';
                $class = str_replace('Tk_Type', 'Com_Type', $class);
                if (class_exists($class)) {
                    $field = new $class($name);
                    $this->fields[$name] = $field;
                } else {
                    throw new Tk_Exception("Could not find form type field `$class'.");
                }
        }
        return $this->fields[$name];
    }
    
    /**
     * Get a field by name...
     *
     * @param string $name
     * @return Com_Form_Field
     */
    function getField($name)
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        }
        return '';
    }
    
    /**
     * Returns a list of the form fields.
     *
     * @return array
     */
    function getFields()
    {
        return $this->fields;
    }
    
    /**
     * Returns a form field value.
     *
     * @param string $name The field name.
     * @return mixed
     */
    function getFieldValue($name)
    {
        $field = $this->getField($name);
        if ($field == null) {
            return null;
        }
        return $field->getValue();
    }
    
    /**
     * Sets the value of a form field.
     *
     * @param string $name The field name.
     * @param mixed $value The field value.
     */
    function setFieldValue($name, $value)
    {
        if (!array_key_exists($name, $this->fields)) {
            throw new Tk_Exception('Field "' . $name . '" Does not exsist.');
        }
        $field = $this->getField($name);
        $field->setValue($value);
    }
    
    /**
     * Adds form field error.
     *
     * If the field is not found in the form then the error message is set to
     * the form error message.
     *
     * If $msg is null the error is unset.
     *
     * @param string $fieldName A field name.
     * @param string $msg The error message.
     */
    function addFieldError($fieldName, $msg)
    {
        if (array_key_exists($fieldName, $this->fields)) {
            /* @var $field Com_Form_Field */
            $field = $this->getField($fieldName);
            if ($msg != '') {
                $field->addError($msg);
            } else {
                $field->clearErrors();
            }
        } else {
            $this->addError($msg);
        }
    }
    
    /**
     * Adds form field errors from a map of (field name, list of errors) message pairs.
     *
     * If the field is not found in the form then the error message is added to
     * the form error messages.
     * @param array
     */
    function addFieldErrors($errors)
    {
        foreach ($errors as $field => $errorList) {
            $field = $this->getField($field);
            if ($field == null) {
                foreach ($errorList as $msg) {
                    $this->addError($msg);
                }
            } else {
                foreach ($errorList as $msg) {
                    $field->addError($msg);
                }
            }
        }
    }
    
    /**
     * Returns the form's id.
     *
     * If there is more than one instance of a form with the same name then
     * the form id can be used to tell them apart.
     *
     * @return integer
     */
    function getFormId()
    {
        return $this->formId;
    }
    
    /**
     * Set the forms id attribute
     *
     * @param string $id
     */
    function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Sets the action of the HTML form.
     *
     * @param Tk_Type_Url
     */
    function setAction($url)
    {
        if (!$url instanceof Tk_Type_Url) {
            $url = new Tk_Type_Url($url);
        }
        $this->action = $url->toString();
    }
    
    /**
     * Gets the action of the HTML form.
     *
     * @return string
     */
    function getAction()
    {
        return $this->action;
    }
    
    /**
     * Loads the object and the form fields from the request.
     *
     */
    function loadFromRequest()
    {
        $this->loadFromArray(Tk_Request::getInstance()->getAllParameters());
    }
    
    /**
     * Loads the object and the form fields from an array
     * EG:
     *   $array['field1'] = 'value1';
     *
     * @param array $array
     */
    function loadFromArray($array)
    {
        foreach ($this->fields as $field) {
            $field->loadFromArray($array);
        }
    }
    
    /**
     * Loads an object from the form field values.
     *
     * @param Com_Form_FileFieldInterface $object
     */
    function loadObject($object)
    {
        /* @var $field Com_Form_Field */
        foreach ($this->fields as $name => $field) {
            $setMethod = 'set' . ucfirst($name);
            $getMethod = 'get' . ucfirst($name);
            
            if ($field instanceof Com_Form_FileField) {
                if (!$field->hasFile()) {
                    continue;
                }
                // store old file names
                if (method_exists($object, $getMethod)) {
                    $this->origFilePaths[$name] = Tk_Type_Path::createFromRalative($object->$getMethod());
                }
                // Set the new filename
                if (method_exists($object, $setMethod)) {
                    $object->$setMethod($field->getPath()->getRalativeString() . '/' . $field->getFilename());
                }
            } else {
                // Default set field method
                if (method_exists($object, $setMethod)) {
                    $object->$setMethod($field->getValue());
                }
            }
        }
    }
    
    /**
     * Loads the form fields from the object.
     *
     * @param mixed $object The object being mapped.
     */
    function loadFromObject($object)
    {
        foreach ($this->fields as $name => $field) {
            if ($field instanceof Com_Form_FileField) {
                continue;
            }
            $method = 'get' . ucfirst($name);
            if (!method_exists($object, $method)) {
                $method = 'is' . ucfirst($name);
                if (!method_exists($object, $method)) {
                    continue;
                }
            }
            $field->setValue($object->$method());
        }
    }
    
    /**
     * Add an error to the error array.
     *
     * @param string $error
     */
    function addError($error)
    {
        $this->errors[] = $error;
    }
    
    /**
     * Returns a list of any form errors.
     *
     * @return array
     */
    function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Returns true if the form or fields have any errors set.
     * @return boolean
     */
    function hasErrors()
    {
        $hasErrors = (count($this->errors) > 0);
        /* @var $field Com_Form_Field */
        foreach ($this->fields as $field) {
            if ($field->hasErrors()) {
                $hasErrors = true;
            }
        }
        if (!$hasErrors) {
            $this->moveUplodedFiles();
        }
        return $hasErrors;
    }
    
    /**
     * Move all files to their respective directories.
     *
     */
    function moveUplodedFiles()
    {
        foreach ($this->fields as $name => $field) {
            if ($field instanceof Com_Form_FileField) {
                if ($field->hasFile()) {
                    $origPath = $this->origFilePaths[$name];
                    if ($field->moveUploadedFile() && $origPath && $field->getFilename() != $origPath->getBasename()) {
                        $path = $this->origFilePaths[$name];
                        if ($path->isFile()) {
                            @unlink($path->toString());
                        }
                    }
                
                }
            }
        }
    }

}

/**
 * A form field object.
 *
 *
 * @package Com
 */
abstract class Com_Form_Field
{
    /**
     * @var array
     */
    private $errors = null;
    
    /**
     * @var string
     */
    private $name = '';
    
    /**
     * @var mixed
     */
    private $value = null;
    
    /**
     * @var array
     */
    protected $domValues = null;
    
    
    
    /**
     * __construct
     *
     * @param string $name
     * @param string $type
     */
    function __construct($name)
    {
        $this->name = $name;
        $this->errors = array();
        $this->domValues = array();
    }
    
    /**
     * Get this field's Dom string values.
     *
     * Returns a map of HTML form element (name, value) pairs.
     *
     * @return array
     */
    function getDomValues()
    {
        return $this->domValues;
    }
    
    /**
     * Get the name of this field
     *
     * @return string
     */
    function getName()
    {
        return $this->name;
    }
    
    /**
     * Set the value of this object and update the Dom string values.
     *
     * @param mixed $value
     */
    function setValue($value)
    {
        $this->value = $value;
        $this->setDomValues($value);
    }
    
    /**
     * Get the field value.
     *
     * @return mixed
     */
    function getValue()
    {
        return $this->value;
    }
    
    /**
     * Set the value of this object without updating the string values.
     *
     * @param mixed $value
     */
    protected function setValueFromRequest($value)
    {
        $this->value = $value;
    }
    
    /**
     * Loads the object and the form fields from an array map.
     *
     * @param array $array
     */
    abstract function loadFromArray($array);
    
    /**
     * Updates the fields Dom string values.
     *
     * Converts value to HTML form element (name, value) pairs and adds them
     * to the $domValues map. Called by setValue().
     *
     * @see setValue()
     * @param mixed $value
     */
    abstract protected function setDomValues($value);
    
    /**
     * Add an error to the error array.
     *
     * @param string $error
     */
    function addError($error)
    {
        $this->errors[] = $error;
    }
    
    /**
     * Returns a list of any form errors.
     *
     * @return array
     */
    function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Returns true if the field has any errors set.
     *
     * @return boolean
     */
    function hasErrors()
    {
        return (count($this->errors) > 0);
    }
    
    /**
     * Clear the error array.
     *
     */
    function clearErrors()
    {
        $this->errors = array();
    }
}

//-------------------------------------------------------------------------


/**
 * A integer form field object.
 *
 * @package Com
 */
class Com_Form_IntegerField extends Com_Form_Field
{
    
    function loadFromArray($array)
    {
        $name = $this->getName();
        $domValue = '';
        if (isset($array[$name])) {
            $domValue = trim($array[$name]);
        }
        $this->domValues[$name] = $domValue;
        $this->setValueFromRequest(intval($domValue));
    }
    
    protected function setDomValues($value)
    {
        $this->domValues[$this->getName()] = $value;
    }
}

/**
 * A float form field object.
 *
 *
 * @package Com
 */
class Com_Form_FloatField extends Com_Form_Field
{
    
    function loadFromArray($array)
    {
        $name = $this->getName();
        $strValue = '';
        if (isset($array[$name])) {
            $strValue = trim($array[$name]);
        }
        $this->domValues[$name] = $strValue;
        $this->setValueFromRequest(floatval($strValue));
    }
    
    protected function setDomValues($value)
    {
        $this->domValues[$this->getName()] = $value;
    }
}

/**
 * A boolean form field object.
 *
 * @package Com
 */
class Com_Form_BooleanField extends Com_Form_Field
{
    function loadFromArray($array)
    {
        $name = $this->getName();
        $strValue = '';
        if (isset($array[$name])) {
            $strValue = trim($array[$name]);
        }
        $this->domValues[$name] = $strValue;
        
        $this->setValueFromRequest((strtolower($strValue) == 'true' || $strValue == $name) ? true : false);
    
    }
    
    protected function setDomValues($value)
    {
        $this->domValues[$this->getName()] = $value == true ? $this->getName() : 'false';
    }
}

/**
 * A string form field object.
 *
 * @package Com
 */
class Com_Form_StringField extends Com_Form_Field
{
    
    function loadFromArray($array)
    {
        $name = $this->getName();
        if (!array_key_exists($name, $array)) {
            return;
        }
        $strValue = $array[$name];
        if (is_string($strValue)) {
            $strValue = trim($array[$name]);
            $strValue = mb_convert_encoding($strValue, "UTF-8", "UTF-8");
        }
        $this->domValues[$name] = $strValue;
        $this->setValueFromRequest($strValue);
    }
    
    protected function setDomValues($value)
    {
        $this->domValues[$this->getName()] = $value;
    }
}

/**
 * A string form field object.
 *
 * @package Com
 */
class Com_Form_ArrayField extends Com_Form_Field
{
    
    function loadFromArray($array)
    {
        $name = $this->getName();
        $strValue = array();
        if (isset($array[$name])) {
            $strValue = $array[$name];
            if (!is_array($strValue)) {
                $strValue = array($array[$name]);
            }
        }
        //vd($array, $strValue);
        $this->domValues[$name] = $strValue;
        $this->setValueFromRequest($strValue);
    }
    
    protected function setDomValues($value)
    {
        $this->domValues[$this->getName()] = $value;
    }
}