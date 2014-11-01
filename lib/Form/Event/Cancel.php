<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *
 *
 * @package Form
 */
class Form_Event_Cancel extends Form_ButtonLinkEvent
{
    /**
     * @var Tk_Type_Url
     */
    protected $redirectUrl = null;
    
    
    /**
     * Create an instance of this object
     *
     * @param Tk_Type_Url $url
     * @param string $name
     * @return Form_Event_Cancel
     */
    static function create($url, $name = 'cancel')
    {
        $obj = new self($name);
        $obj->redirectUrl = $url;
        return $obj;
    }
    
    /**
     * If a redirect url is set then the form is redirected to there
     *
     * @param Tk_Type_Url $url
     */
    function setRedirectUrl(Tk_Type_Url $url)
    {
        $this->redirectUrl = $url;
        return $this;
    }

    
    /**
     * Executed on form submit event.
     */
    function execute()
    {
        if ($this->redirectUrl) {
            $this->setRedirect($this->redirectUrl);
        }
    }
}

