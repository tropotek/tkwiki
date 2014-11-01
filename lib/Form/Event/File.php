<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A default image handling event
 * This can be used in situations where you want the files for an object saved
 *  to '{data_dir}/{className}/{fieldName}_{id}.{ext}
 *
 * @package Form
 */
class Form_Event_File extends Form_Event
{
    /**
     * @var integer
     */
    protected $width = 0;
    
    /**
     * @var integer
     */
    protected $height = 0;
    
    
    /**
     * if both height and width are not 0, then the system will attempt to
     * resize the image to the specified dimensions, otherwise no resize attempt is made.
     *
     * @param integer $width
     * @param integer $height
     * @return Form_Event_File
     */
    static function create($width = 0, $height = 0)
    {
        $obj = new self();
        $obj->width = $width;
        $obj->height = $height;
        return $obj;
    }
    
    
    /**
     * init()
     */
    function init()
    {
        $this->setTrigerList(array('add', 'save', 'update'));
    }
    
    
    function postExecute()
    {
        if (!$this->getField()->isUploadedFile() || $this->getForm()->hasErrors()) {
            return;
        }
        $object = $this->getObject();
        $get = 'get' . ucfirst($this->getField()->getName());
        $set = 'set' . ucfirst($this->getField()->getName());
        
        $htorg = $object->$get();
        $src = Tk_Config::get('system.dataPath') . $htorg;
        $ext = Tk_Type_Path::getFileExtension($src);
        $arr = explode('_', get_class($object));
        $class = array_pop($arr);
        
        $htdst = '/' . ucfirst($class) . '/' . $object->getVolitileId() . '/' . $this->getField()->getName() . '.' . $ext;
        //$htdst = '/' . ucfirst($class) . '/' . $this->getField()->getName() . '_' . $object->getVolitileId() . '.' . $ext;
        
        $object->$set($htdst);
        $object->save();
        
        if (!Form::moveUploadedFile($src, Tk_Config::get('system.dataPath') . $htdst)) {
            Tk::log('Error Moving file');
            return;
        }
        // delete any old image
        if ($htorg != $htdst && is_file(Tk_Config::get('system.dataPath') . $htorg)) {
            @unlink(Tk_Config::get('system.dataPath') . $htorg);
        }
        
        
        
        if (Tk_Util_GdImage::isValidImage(basename($htdst))) {
            if ($this->width > 0 && $this->height > 0) {
                $path = Tk_Type_Path::create(Tk_Config::get('system.dataPath') . $htdst);
                Tk_Util_GdImage::conditionalPropScale($path, $path, $this->width, $this->height);
                //Tk_Util_GdImage::propScaleSimple($path, $path, $this->width, $this->height);
                //Tk_Util_GdImage::propScale($path, $path, $this->width, $this->height);
            }
        }
        
    }
    
    
}