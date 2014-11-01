<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Use this type for File fields
 *
 * @package Form
 */
class Form_Type_File extends Form_Type
{

    /**
     * Create an instance of this object
     *
     * @return Form_Type_File
     */
    static function create()
    {
        return new self();
    }
    
    /**
     * Load the field value object from a data sorce array.
     * This is usually, but not limited to, the request array
     * 
     * @param array $array
     */
    function loadFromArray($array)
    {
        
        $name = $this->field->getName();
        if (!array_key_exists($name, $_FILES) || (isset($_FILES[$name]['error']) && $_FILES[$name]['error'] == UPLOAD_ERR_NO_FILE)) {
            return;
        }
        if ($this->field->getFileError()) {
            $this->field->getForm()->addFieldError($name, Form_Field_File::getErrorString($this->field->getFileError()));
        }
        if ($this->field->getForm()->hasErrors() || !$this->field->isUploadedFile()) {
            return;
        }
        $htdst = '/Form/' . $array[Form::HIDDEN_SUBMIT_ID] . '-' . Tk_Session::getInstance()->getName() . '/' . $name . '/' . $_FILES[$name]['name'];
        //$htdst = '/Form/' . $array[Form::HIDDEN_SUBMIT_ID]  . '/' . $name . '/' . $_FILES[$name]['name'];
        $dst = Tk_Config::get('system.dataPath') . $htdst;
        $_FILES[$name]['_htdst'] = $htdst;  // This will get loaded into the object
        $_FILES[$name]['_dst'] = $dst;     // This is the full path to the file
        
        $this->field->setSubFieldValue($name, $htdst);
        $this->field->setRawValue($htdst);
        
        // Move the uploaded file into the form's temp filesystem.
        // SEE: Form::moveUploadedFile() for more info
        if (!is_dir(dirname($dst))) {
            mkdir(dirname($dst), 0755, true);
        }
        
        if (move_uploaded_file($_FILES[$name]['tmp_name'], $dst)) {
            // Delete original file from the object if required
            $obj = $this->field->getForm()->getEventController()->getOriginalObject();
            if ($obj instanceof Tk_Object) {
                $method = 'get' . ucfirst($this->field->getName());
                if ($this->field->getAutoDelete() && $obj->$method() != $htdst) {
                    $path = Tk_Config::getDataPath() . $obj->$method();
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
            }
            $this->field->setReadonly(false);
        } else {
            $this->field->getForm()->addFieldError($name, Form_Field_File::getErrorString($this->field->getFileError()));
        }
    }
    
    
    
    /**
     * not used for files
     * @param unknown_type $obj
     */
    function setSubFieldValues($obj) { }
    
    
}