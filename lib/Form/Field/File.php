<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
if (!defined('UPLOAD_ERR_POSTMAX')) {
    define('UPLOAD_ERR_POSTMAX', 10);
}
/**
 *  A file form field
 *
 * @package Form
 */
class Form_Field_File extends Form_Field
{
    /**
     * The max size for this file upload in bytes
     * Default: Tk_Type_Path::string2Bytes(ini_get('upload_max_filesize'))
     * @var integer
     */
    protected $maxBytes = 50000; //50Kb
    
    /**
     * @var boolean
     */
    protected $autoDelete = true;
    
    
    private $postMaxError = 0;
    
    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @return Form_Field_File
     */
    static function create($name)
    {
        $obj = new self($name, Form_Type_File::create());
        $obj->maxBytes = Tk_Type_Path::string2Bytes(ini_get('upload_max_filesize'));
        $obj->setReadonly(true);
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
            $obj->postMaxError = UPLOAD_ERR_POSTMAX;
        }
        return $obj;
    }
    
    /**
     * Catch a max post error, this would have to be checked on every page load
     * and an error message added to the form not associated to a field
     *
     * @return boolean
     */
    static function hasMaxPostError()
    {
        if(empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post'){ //catch file overload error...
            return true;
        }
        return false;
    }
    
    /**
     *
     * @param Form $form
     */
    function onSetForm($form)
    {
        $object = $form->getObject();
        $getMethod = 'get' . ucfirst(str_replace('view', '', $this->getName()));
        if ($object instanceof Tk_Db_Object && $object->$getMethod()) {
            $this->addEvent(Form_Event_ViewFile::create('view' . ucfirst($this->getName()))->setLabel('View') );
            $this->addEvent(Form_Event_DeleteFile::create('delete' . ucfirst($this->getName()))->setLabel('Delete') );
        }
    }
    
    /**
     * If this is set to false then the Form objects
     * will not try to delete any existing files if a new file is uploaded.
     *
     * Note: If you create more copies of the file other than the one uploaded
     *   you must delete any changed files in your own code. This will only
     *   delete the file found in the get{FieldName} method specified.
     *
     * @param boolean $b
     */
    function setAutoDelete($b)
    {
        $this->autoDelete = ($b === true);
    }
    
    /**
     * Get the auto delete status
     * @return boolean
     */
    function getAutoDelete()
    {
        return $this->autoDelete;
    }
    
    /**
     * Set the max file upload for this field in bytes
     *
     * @param $bytes
     * @return Form_Field_File
     */
    function setMaxFileSize($bytes)
    {
        $this->maxBytes = (int)$bytes;
        return $this;
    }
    
    /**
     * Get the max filesize in bytes for this file field
     *
     * @return integer
     */
    function getMaxFileSize()
    {
        return $this->maxBytes;
    }
    
    /**
     * Set the parent form object
     *
     * @param Form $form
     * @return Form_Field_File
     */
    function setForm($form)
    {
        parent::setForm($form);
        $form->setEnctype(Form::ENCTYPE_MULTIPART);
        return $this;
    }
    
    /**
     * Has there been a file submitted?
     *
     * return boolean
     */
    function isUploadedFile()
    {
        if (!isset($_FILES[$this->getName()]['error']) || $_FILES[$this->getName()]['error'] !== UPLOAD_ERR_NO_FILE) {
            return true;
        }
        return false;
    }
    
    /**
     * Get the uploaded filename, will return empty string if no file exists
     * The original name of the file on the client machine. 
     * 
     * @return string
     */
    function getFilename()
    {
        if (isset($_FILES[$this->getName()]['name'])) {
            return $_FILES[$this->getName()]['name'];
        }
        return '';
    }
    
    /**
     * Get the uploaded file size in bytes
     * 
     * @return integer
     */
    function getFileSize()
    {
        if (isset($_FILES[$this->getName()]['size'])) {
            return $_FILES[$this->getName()]['size'];
        }
        return 0;
    }
    
    /**
     * Get the mime type of the file, if the browser provided this information. 
     * An example would be "image/gif". This mime type is however not checked on 
     * the PHP side and therefore don't take its value for granted. 
     * 
     * @return integer
     */
    function getFileType()
    {
        if (isset($_FILES[$this->getName()]['type'])) {
            return $_FILES[$this->getName()]['type'];
        }
        return '';
    }
    
    /**
     * Get the file upload error if any
     * 
     * @return integer
     */
    function getFileError()
    {
        if ($this->postMaxError) {
            return $this->postMaxError;
        }
        return $_FILES[$this->getName()]['error'];
    }
    
    /**
     * getErrorString
     *
     * @param integer $errorId
     */
    static function getErrorString($errorId)
    {
        switch ($errorId) {
            case UPLOAD_ERR_OK :
                $str = '';
                break;
            case UPLOAD_ERR_POSTMAX:
                $str = "The uploaded file exceeds post max filesize of " . ini_get('post_max_size');
                break;
            case UPLOAD_ERR_INI_SIZE :
                $str = "The uploaded file exceeds max filesize of " . ini_get('upload_max_filesize');
                break;
            case UPLOAD_ERR_FORM_SIZE :
                $str = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                break;
            case UPLOAD_ERR_PARTIAL :
                $str = "The uploaded file was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_FILE :
                $str = "No file was uploaded.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR :
                $str = "Missing a temporary folder.";
                break;
            case UPLOAD_ERR_CANT_WRITE :
                $str = "Failed to write file to disk";
                break;
            default :
                $str = "Unknown File Error";
        }
        return $str;
    }
    
    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function show($t = null)
    {
        // This needs to be here, see docs about post_max_size errors....
        // Best thing to do is have the post size double that of the max file size...
        if (self::hasMaxPostError()) {
            $postMax = ini_get('post_max_size'); //grab the size limits...
            $msg = "Please note files larger than {$postMax} will result in this error!
                Please be advised this is not a limitation in the site, This is a limitation of the hosting server.
                If you have access to the php ini file you can fix this by changing the post_max_size setting.";
            $this->getForm()->addFieldError($this->getName(), $msg);
        }
        
        $notes = 'Max File Size: ' . Tk_Type_Path::bytes2String($this->getMaxFileSize());
        $method = 'get'.ucfirst($this->getName());
        if ($this->getForm()->getEventController()->getOriginalObject()->$method()) {
            $notes .= ' (`' . $this->getForm()->getEventController()->getOriginalObject()->$method() . '`)';
        }
        if ($this->notes) {
            $this->notes = $notes . '<br/>' . $this->notes;
        } else {
            $this->notes = $notes;
        }
        $this->showDefault($t);
        $this->showElement($t);
        // No need to render readonly on file field anyway as not relevent
        $t->setAttr('element', 'readonly', null);   // Remove readonly attribute for IE browsers
    }
    
    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = sprintf('<?xml version="1.0"?>
<div class="field" var="block">
  <p class="error" var="error" choice="error"></p>
  <label for="fid-code" var="label"></label>
  <input type="file" name="" id="" class="inputText" var="element" />
  <div class="events" var="events"></div>
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
}