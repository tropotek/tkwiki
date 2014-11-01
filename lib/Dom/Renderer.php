<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * For classes that render dom templates.
 *
 * This is a good base for all renderer objects that implement the Dom_Template
 * it can guide you to create templates that can be inserted into other template
 * objects.
 *
 * If the current template is null then
 * the magic method __makeTemplate() will be called to create an internal template.
 * This is a good way to create a default template. But be aware that this will
 * be a new template and will have to be inserted into its parent using the Dom_Template::insertTemplate()
 * method.
 *
 * @package Dom 
 */
abstract class Dom_Renderer implements Dom_RendererInterface
{
    
    /**
     * @var Dom_Template
     */
    protected $template = null;
    
    
    
    
    /**
     * Set a new template for this renderer.
     *
     * @param Dom_Template $template
     */
    public function setTemplate(Dom_Template $template)
    {
        $this->template = $template;
    }
    
    /**
     * Get the template
     * This method will try to call the magic method __makeTemplate
     * to get a template if non exsits.
     * Use this for objects that use internal templates.
     *
     * @return Dom_Template
     */
    public function getTemplate()
    {
        if ($this->hasTemplate()) {
            return $this->template;
        }
        $magic = '__makeTemplate';
        if (!$this->hasTemplate() && method_exists($this, $magic)) {
            $this->template = $this->$magic();
        }
        return $this->template;
    }
    
    /**
     * Test if this renderer has a template and is not NULL
     *
     * @return boolean
     */
    public function hasTemplate()
    {
        if ($this->template) {
            return true;
        }
        return false;
    }
}