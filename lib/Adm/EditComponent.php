<?php
/*
 * This file is part of the DkLib.
 *   You can redistribute it and/or modify
 *   it under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   You should have received a copy of the GNU Lesser General Public License
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A base component object.
 *
 *
 * @package Adm
 */
class Adm_EditComponent extends Adm_Component
{
    
    
    /**
     *
     * @return boolean
     */
    function execute()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        $ret = parent::execute();
        
        
        return $ret;
    }

    /**
     * Render
     *
     * @return boolean
     */
    function render()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        $nameStr = preg_replace('/[A-Z]/', ' $0', $this->getName());
        $this->getPage()->getTemplate()->insertText('_contentTitle', 'Edit ' . $nameStr);
        
        $js = "
$(function() {
  // Ensure that there is an alert before leaving the edit window
  $('.edit input, .edit textarea, .edit select').change(function (e) {
      $(window).bind('beforeunload', function(e) {
        var str = 'WARNING: Unsaved changes will be lost, Continue?';
        if (e) { e.returnValue = str; }
        return str;
      });
  });
});";
        $this->getTemplate()->appendJs($js);
        
        $ret = parent::render();
        
        
        return $ret;
    }
}