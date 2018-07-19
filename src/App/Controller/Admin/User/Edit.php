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


        if ($this->getUser()->isAdmin() && !$this->user->isAdmin()) {
            $roles = \App\Db\PermissionMap::create()->findAll(\Tk\Db\Tool::create('a.id'))->toArray();
            $list = new \Tk\Form\Field\Option\ArrayObjectIterator($roles);
            $f = $this->form->addField(new \Tk\Form\Field\CheckboxGroup('permission', $list))
                ->setNotes('Select the available permissions this user has.')->setTabGroup('Permissions');

            /** @var \Tk\Form\Field\Option $option */
            foreach($f->getOptions() as $option) {
                $p = \App\Db\PermissionMap::create()->find($option->getValue());
                //vd($p->description);
                $option->setAttr('title', $p->description);
            }

            $selected = array();
            foreach($this->getConfig()->getAcl()->getRoles() as $obj) {
                $selected[] = $obj->id;
            }
            $this->form->setFieldValue('role', $selected);
        }

    }


}