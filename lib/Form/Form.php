<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The dynamic form Controller
 *
 * `enctype` Attribute Values:
 * <code>
 *    Value                              |                 Description
 * --------------------------------------|---------------------------------------
 *  application/x-www-form-urlencoded    |  All characters are encoded before sent (this is default)
 *  multipart/form-data                  |  No characters are encoded. This value is required when you are using forms that have a file upload control
 *  text/plain                           |  Spaces are converted to "+" symbols, but no special characters are encoded
 * </code>
 *
 *
 * accept-charset is set as the $encoding parameter or use setEncoding()
 *
 * @requires Dom
 * @requires Tk
 * @package Form
 * @todo All form and field names/id's need to be prepended with a unique form ID value
 *  so that 2 forms on the same page do not conflict. If this becomes a big issue in the future
 *  it should be implemented within the form object transparent from the actual implemententation code...
 */
class Form extends Tk_Object
{
    
    const ENCTYPE_URLENCODED  = 'application/x-www-form-urlencoded';
    const ENCTYPE_MULTIPART   = 'multipart/form-data';
    const ENCTYPE_PLAIN       = 'text/plain';
    
    const METHOD_POST         = 'post';
    const METHOD_GET          = 'get';
    
    const HIDDEN_SUBMIT_ID    = '__submitId';
    
    
    /**
     * @var string
     */
    protected $formId = '';
    
    /**
     * @var Tk_Type_Url
     */
    protected $action = null;
    
    /**
     * @var array
     */
    private $errors = array();
    
    /**
     * @var array
     */
    private $messages = array();
    
    /**
     * @var array
     */
    protected $fieldList = array();
    
    /**
     * @var array
     */
    protected $attrList = array();
    
    /**
     * @var Form_EventController
     */
    protected $eventController = null;
    
    /**
     * This is generally the containing component but if
     * not using the ComLib it can be any object the developer wants
     * as long as it contains an instance of the form object.
     *
     * @var Com_Web_Component
     */
    protected $container = null;
    
    /**
     * @var string
     */
    protected $method = self::METHOD_POST;
    
    /**
     * @var string
     */
    protected $encoding = '';
    
    /**
     * @var string
     */
    protected $enctype = '';
    
    /**
     * @var string
     */
    protected $title = '';
    
    /**
     * @var string
     */
    protected $target = '';
    
    /**
     * @var string
     */
    protected $cssClass = '';
    
    /**
     * @var array
     */
    protected $helpList = array();
    
    
    /**
     * Submit event handler
     *
     * @var Form_Handler
     * @deprecated
     */
    protected $handler = null;
    
    
    
    
    
    /**
     * Create a form controller
     * NOTE: Use the function Form::create() in most comment uses
     *
     * @param string $formId
     */
    function __construct($formId)
    {
        $this->id = $formId;
        $this->addField(Form_Field_Hidden::create(self::HIDDEN_SUBMIT_ID))->setValue($this->getFormId());
        $this->setAction(Tk_Request::requestUri());
    }
    
    /**
     * Create a new form with a new form renderer
     *
     * @param string $formId
     * @param mixed $object An array or a Tk_Object
     * @return Form
     */
    static function create($formId, $object = array())
    {
        $form = new self($formId, $object);
        $form->eventController = Form_EventController::create($form, $object);
        return $form;
    }
    
    
    /**
     * Add an attribute to the form tag.
     * This method exists to allow access to attributes that are not used often.
     * 
     * @param string $name
     * @param string $value
     * @return Form
     */
    function addAttribute($name, $value)
    {
        $this->attrList[$name] = $value;
        return $this;
    }
    
    /**
     * return the attrs list
     * 
     * @return array
     */
    function getAttrList()
    {
        return $this->attrList;
    }
    
    /**
     * Delete an attribute from the form attribute list
     * 
     * @param string $name
     */
    function deleteAttribute($name)
    {
        if (isset($this->attrList[$name])) {
            unset($this->attrList[$name]);
        }
        return $this;
    }
    
    /**
     * An alias for $this->getEventController()->execute();
     *
     * @return Form
     */
    function execute()
    {
        $this->eventController->execute();
        return $this;
    }
    
    /**
     * Add an event object to the form
     * These objects should contain buttons or similar elements that submit the form
     *
     * @param Form_Event $event
     * @return Form
     */
    function addEvent(Form_Event $event)
    {
        $this->getEventController()->addEvent($event);
        return $event;
    }
    
    /**
     * Clear the event list
     * @return Form
     */
    function clearEventList()
    {
        $this->getEventController()->clearEventList();
        return $this;
    }
    
    /**
     * Get Event List
     *
     * @return array
     */
    function getEventList()
    {
        return $this->getEventController()->getEventList();
    }
    
    /**
     * Get the event controller
     *
     * @return Form_EventController
     */
    function getEventController()
    {
        return $this->eventController;
    }
    
    /**
     * An alias for $this->eventController->addDefaultEvents($redirect)
     *
     * @param Tk_Type_Url $redirect
     * @return Form
     */
    function addDefaultEvents(Tk_Type_Url $redirect)
    {
        $this->eventController->addDefaultEvents($redirect);
        return $this;
    }
    
    /**
     * Get the form data object
     *
     * @return Tk_Object or an array()
     */
    function getObject()
    {
        return $this->eventController->getObject();
    }
    
    
    /**
     * This is generally the containing component but if
     * not using the ComLib it can be any object the developer wants
     * as long as it contains an instance of the form object.
     *
     * @param Com_Web_Component $c
     * @return Form
     */
    function setContainer($c)
    {
        $this->container = $c;
        return $this;
    }
    
    /**
     * This is generally the containing component but if
     * not using the ComLib it can be any object the developer wants
     * as long as it contains an instance of the form object.
     *
     * @return Com_Web_Component
     */
    function getContainer()
    {
        return $this->container;
    }
    
    
    /**
     * Get this form's rendered title
     *
     * @return string
     */
    function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set the form rendered title
     *
     * @param string $str
     * @return Form
     */
    function setTitle($str)
    {
        $this->title = $str;
        return $this;
    }
    
    
    /**
     * Get this form's rendered target attribute
     *
     * @return string
     */
    function getTarget()
    {
        return $this->target;
    }
    
    /**
     * Set the form rendered target attribute
     * Default: null
     * 
     * @param string $str
     * @return Form
     */
    function setTarget($str)
    {
        $this->target = $str;
        return $this;
    }
    
    
    /**
     * Get the form CSS class
     *
     * @return string
     */
    function getCssClass()
    {
        return $this->cssClass;
    }
    /**
     *
     * @param string $str
     * @return Form
     */
    function setCssClass($str)
    {
        $this->cssClass = $str;
        return $this;
    }
    
    /**
     * Get the form name/ID
     *
     * @return string
     */
    function getFormId()
    {
        return $this->getId();
    }
    
    /**
     * Set the form submit action
     *
     * @param Tk_Type_Url $url
     * @return Form
     */
    function setAction($url)
    {
        $this->action = $url;
        return $this;
    }
    
    /**
     * Get the action url
     *
     * @return Tk_Type_Url
     */
    function getAction()
    {
        return $this->action;
    }
    
    /**
     * Set the form encoding type
     * This should be set to ENCTYPE_MULTIPART for forms that submit files
     *
     * @param string $enctype
     * @return Form
     */
    function setEnctype($enctype)
    {
        $this->enctype = strtolower($enctype);
        return $this;
    }
    
    /**
     * Get the form encoding type
     *
     * @return string
     */
    function getEnctype()
    {
    	return $this->enctype;
    }
    
    /**
     * Set the characterset encoding Defaults to UTF-8
     *
     * @param string $encoding
     * @return Form
     */
    function setEncoding($encoding)
    {
        $this->encoding = strtolower($encoding);
        return $this;
    }
    
    /**
     * Get the form encoding type
     *
     * @default utf-8
     * @return string
     */
    function getEncoding()
    {
    	return $this->encoding;
    }
    
    /**
     * Set this forms method, default post
     *
     * @param string $method
     * @return Form
     */
    function setMethod($method)
    {
        if ($method) {
            $this->method = strtolower($method);
        }
        return $this;
    }
    
    /**
     * Get the form method 'GET, POST'
     *
     * @return string
     */
    function getMethod()
    {
    	return $this->method;
    }
    
    /**
     * Add a help message to the form
     *
     * @param string $title
     * @param string $msg
     */
    function addHelpMessage($title, $msg)
    {
        //$msg = strip_tags($msg, '<a>,<b>,<strong>,<u>,<i>,<em>,<img>');
        $this->helpList[$title] = $msg;
    }
    
    /**
     * Clear the help message List
     */
    function clearHelpList()
    {
        $this->helpList = array();
    }
    
    /**
     * Get the help message list.
     * This text is rendered around the form in a position helpful to the user.
     *
     * @return array
     */
    function getHelpList()
    {
    	return $this->helpList;
    }
    
    /**
     * Add a field to the form
     *
     * @param Form_Field $field
     * @return Form_Field
     */
    function addField($field)
    {
        $this->fieldList[$field->getName()] = $field;
        $field->setForm($this);
        return $field;
    }
    
    /**
     * Add a field to the form before another field
     *
     * @param string $fieldName
     * @param Form_Field $newField
     * @return Form_Field
     */
    function addBefore($fieldName, $newField)
    {
        $newArr = array();
        /* @var $field Form_Field */
        foreach ($this->fieldList as $field) {
            if ($field->getName() == $fieldName) {
                $newField->setForm($this);
                $newArr[$newField->getName()] = $newField;
            }
            $newArr[$field->getName()] = $field;
        }
        $this->fieldList = $newArr;
        return $newField;
    }
    
    /**
     * Add a field to the form after another field
     *
     * @param string $fieldName
     * @param Form_Field $newField
     * @return Form_Field
     */
    function addAfter($fieldName, $newField)
    {
        $newArr = array();
        /* @var $field Form_Field */
        foreach ($this->fieldList as $field) {
            $newArr[$field->getName()] = $field;
            if ($field->getName() == $fieldName) {
                $newField->setForm($this);
                $newArr[$newField->getName()] = $newField;
            }
        }
        $this->fieldList = $newArr;
        return $newField;
    }
    
    /**
     * Return a field object or null if not found
     *
     * @param string $name
     * @return Form_Field
     */
    function getField($name)
    {
        if (isset($this->fieldList[$name])) {
            return $this->fieldList[$name];
        }
    }
    
    /**
     * Set the field array to empty
     * @return Form
     */
    function clearFieldList()
    {
        $this->fieldList = array();
        return $this;
    }
    
    /**
     * Get Field List
     *
     * @return array
     */
    function getFieldList()
    {
        return $this->fieldList;
    }
    
    /**
     * Returns a form field value. Returns NULL if no field exists
     *
     * @param string $name The field name.
     * @return mixed
     */
    function getFieldValue($name)
    {
        $field = $this->getField($name);
        if ($field) {
            return $field->getValue();
        }
    }
    
    /**
     * Sets the value of a form field.
     *
     * @param string $name The field name.
     * @param mixed $value The field value.
     */
    function setFieldValue($name, $value)
    {
        $field = $this->getField($name);
        if (!$field) {
            throw new Tk_Exception('Field "' . $name . '" Does not exsist.');
        }
        $field->setValue($value);
    }
    
    /**
     * When using the Form object do not use move_uploaded_file unlesss you know what it affects
     * when using the file field.
     *
     * Use this method to move a file from the Form environment default location to the
     * new destination. Check your form result object or array for the current htdoc data path.
     * This path must be prepended with the config system.dataPath variable.
     *
     * The source and destination parameters should be full paths to the file locations
     *
     * NOTICE: Files in the default form folder will be deleted after 24 hours if not moved to a perminent location
     *
     * @param string $source Usually created from an object param. EG: $source = Tk_Config::get('system.dataPath').$obj->getImage();
     * @param string $destination
     * @return boolean
     */
    static function moveUploadedFile($source, $destination)
    {
        
        if (!is_file($source)) {
            Tk::log('Source file does not exist: ' . $source);
            return false;
        }
        // check dest dir, create as required.
        if (!is_dir(dirname($destination))) {
            if (!mkdir(dirname($destination), 0755, true)) {
                Tk::log('Cannot Create Directory: ' . dirname($destination));
                return false;
            }
        }
        // move file
        if (!rename($source, $destination)) {
            Tk::log('Cannot move file: ' . $source . ' - ' . $destination);
            return false;
        }
        
        // Check for old temp files and delete them, only in the form ID folder
        // TODO: Check this
        foreach (new RecursiveDirectoryIterator(dirname(dirname($source))) as $fileInfo) {
            if($fileInfo->isDir()) continue;
            if ($fileInfo->getMTime() < time()-(60*60*1)) {
                @unlink($fileInfo->getPath());
            }
        }
        
        // Check if the field has no more files, then delete the dir
        if (is_dir(dirname($source))) {
            $arr = scandir(dirname($source));
            array_shift($arr);
            array_shift($arr);
            if (!count($arr)) {
                @rmdir(dirname($source));
            }
        }
        // Check if the form has no more files, then delete the dir
        if (is_dir(dirname(dirname($source)))) {
            $arr = scandir(dirname(dirname($source)));
            array_shift($arr);
            array_shift($arr);
            if (!count($arr)) {
                @rmdir(dirname(dirname($source)));
            }
        }
        return true;
    }
    
    /**
     * Add a message to the form
     *
     * @param string $msg
     * @todo Fix message system also see event message system they should be merged
     */
    function addMessage($msg)
    {
        $this->messages[] = $msg;
        return $msg;
    }
    
    /**
     * Get the form message list
     *
     * @return array
     */
    function getMessageList()
    {
        return $this->messages;
    }
    
    /**
     * Set the form message list
     *
     * @param array $list
     * @return Form
     */
    function setMessageList($list)
    {
        $this->messages = $list;
        return $this;
    }
    
    /**
     * Test if the form has any messages
     *
     * @return boolean
     */
    function hasMessages()
    {
        return (count($this->messages) > 0);
    }
    
    /**
     * Test to see if the fields in this form use tab groups
     *
     * @return boolean
     */
    function hasTabs()
    {
        foreach ($this->fieldList as $field) {
            if ($field->getTabGroup()) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Add an error to the error array.
     *
     * @param string $error
     * @return Form
     */
    function addError($error)
    {
        $this->errors[] = $error;
        return $this;
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
    function hasErrors($includeFields = true)
    {
        if (count($this->errors) > 0) {
            return true;
        }
        if ($includeFields) {
            /* @var $field Form_Field */
            foreach ($this->getFieldList() as $field) {
                if ($field->hasErrors()) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Returns true if the fields in a tabgroup has errors
     * @return boolean
     */
    function tabGroupHasErrors($tabGroup)
    {
        /* @var $field Form_Field */
        foreach ($this->getFieldList() as $field) {
            if ($field->getTabGroup() == $tabGroup && $field->hasErrors()) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Adds field error.
     *
     * If the field is not found in the form then the error message is set to
     * the form error message.
     *
     * If $msg is null the field's error list is cleared
     *
     * @param string $name A field name.
     * @param string $msg The error message.
     */
    function addFieldError($name, $msg)
    {
        /* @var $field Form_Field */
        $field = $this->getField($name);
        if ($field) {
            $field->addError($msg);
            if (!$msg) {
                $field->clearErrorList();
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
     *
     * @param array $errors
     */
    function addFieldErrors($errors)
    {
        foreach ($errors as $field => $errorList) {
            $field = $this->getField($field);
            if (!$field) {
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
     * Loads the object and the form fields from an array
     * EG:
     *   $array['field1'] = 'value1';
     *
     * @param array $array
     * @return Form
     */
    function loadFromArray($array)
    {
        /* @var $field Form_Type */
        foreach ($this->getFieldList() as $field) {
            if (!$field->isLoadable()) {
                continue;
            }
            $field->getType()->loadFromArray($array);
        }
        return $this;
    }
    
    /**
     * Loads the form fields from the object.
     *
     * @param mixed $object The object being mapped.
     * @return mixed
     */
    function loadFromObject($object)
    {
        /* @var $field Form_Field */
        foreach ($this->getFieldList() as $name => $field) {
            if (!$field->isLoadable()) {
                continue;
            }
            $method = 'get' . ucfirst($name);
            if (method_exists($object, $method)) {
                $field->setValue($object->$method());
                continue;
            }
            $method = 'is' . ucfirst($name);
            if (method_exists($object, $method)) {
                $field->setValue($object->$method());
                continue;
            }
            if (array_key_exists($name, get_object_vars($object))) {
                $field->setValue($object->$name);
                continue;
            }
        }
        return $object;
    }
    
    
    /**
     * load() Loads and object or an array with the contents of the form.
     *
     * @param mixed $mixed
     * @return Form
     */
    function load(&$mixed = array())
    {
    	if (is_array($mixed)) {
    		$this->loadArray($mixed);
    	} else {
    		$this->loadObject($mixed);
    	}
    	return $this;
    }
    
    
    /**
     * loadArray
     *
     * @param array $array
     * @return Form
     */
    function loadArray(&$array = array())
    {
    	if (!is_array($array)) {
    		throw new Tk_Exception('Parameter not of type array()');
    	}
        foreach ($this->getFieldList() as $name => $field) {
            if ($field->isReadonly() || !$name) {
                continue;
            }
            $array[$name] = $field->getValue();
        }
    	return $this;
    }
    
    /**
     * Loads an object from the form field values.
     *
     * @param mixed $object
     * @return Form
     */
    function loadObject($object)
    {
    	if (!is_object($object)) {
    		throw new Tk_Exception('Invalid object type for parameter.');
    	}
        /* @var $field Form_Field */
        foreach ($this->getFieldList() as $name => $field) {
            if ($field->isReadonly() || !$name) {
                continue;
            }
            $setMethod = 'set' . ucfirst($name);
            if (method_exists($object, $setMethod)) {
                $object->$setMethod($field->getValue());
            }else if (in_array($name, get_object_vars($object))) {
                $object->$name = $field->getValue();
            }
        }
        return $object;
    }
    
    /**
     * Get an array of the form field values
     *
     * @return array
     */
    function getValuesArray()
    {
        $array = array();
        $this->loadArray($array);
        return $array;
    }
    
    
}