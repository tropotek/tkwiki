<?php
namespace App\Controller\Admin\User;

/**
 * @author Tropotek <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Tropotek
 */
class Edit extends \Bs\Controller\Admin\User\Edit
{




    /**
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        //$this->getActionPanel()->setEnabled(false);
    }

    /**
     * @throws \Exception
     */
    public function buildForm()
    {
        parent::buildForm();
        if ($this->user->getId() == 1 || !$this->getUser()->isAdmin()) {
            $this->form->removeField('role');

            $tab = 'Details';
            $this->form->addFieldBefore('username', new \Tk\Form\Field\Html('role'))->setTabGroup($tab);
        }
    }


}