<?php
namespace App\Event;

use Tk\Event\RequestEvent;
use Tk\Form;
use Tk\Request;


/**
 * Class FormEvent
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class FormEvent extends RequestEvent
{
    /**
     * @var Form
     */
    private $form = null;


    /**
     * __construct
     *
     * @param Form $form
     */
    public function __construct($form)
    {
        parent::__construct($form->getRequest());
        $this->form = $form;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    
}