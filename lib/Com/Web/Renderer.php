<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @author Darryl Ross <darryl.ross@aot.com.au>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * For all classes that render dom templates.
 * This is a good base for all renderer objects that implement the Dom_Template
 * it can guide you to create tempaltes that can be inserted into other template
 * objects.
 *
 * For objects that have a null template when the method getTemplate() is called
 * the magic method __makeTemplate() will be called to create a template automaticaly
 * this is a good place for creating a default template. But be aware that this will
 * be a new template and will have to be inserted into its parent using the DOM_Template::replaceTemplate()
 * method.
 *
 * @package Com
 */
abstract class Com_Web_Renderer extends Tk_Object implements Dom_RendererInterface
{
    
    /**
     * @var Dom_Template
     */
    private $template = null;
    
    
    /**
     * Set a new template for this renderer.
     *
     * @param Dom_Template $template
     */
    function setTemplate(Dom_Template $template)
    {
        $this->template = $template;
    }
    
    /**
     * Get the template
     * This method will try to call the magic method __makeTemplate
     * to get a template if non exsits.
     * Use this for object that use internal templates.
     *
     * @return Dom_Template
     */
    function getTemplate()
    {
        if (!$this->hasTemplate() && method_exists($this, '__makeTemplate')) {
            $this->template = $this->__makeTemplate();
        }
        return $this->template;
    }
    
    /**
     * Test if this renderer has a template and is not NULL
     *
     * @return boolean
     */
    function hasTemplate()
    {
        if ($this->template) {
            return true;
        }
        return false;
    }

}