<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A DomTemplate Renderer interface
 *
 * @package Dom
 */
interface Dom_RendererInterface
{

    /**
     * Execute the renderer.
     * This method can optionally return a Dom_Template
     * or HTML/XML string depending on your framework requirements
     *
     * @param Dom_Template $template
     * @return Dom_Template | string
     */
    public function show();

    /**
     * Get the Dom_Template
     *
     * @return Dom_Template
     */
    public function getTemplate();

    /**
     * Set the Dom_Template
     *
     * @param Dom_Template $template
     */
    public function setTemplate(Dom_Template $template);

}