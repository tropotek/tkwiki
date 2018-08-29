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
            /** @var \Tk\Form\Field\CheckboxGroup $f */
            $f = $this->form->addField(new \Tk\Form\Field\CheckboxGroup('permission', $list))
                ->setNotes('Select the available permissions this user has.')->setTabGroup('Permissions');

            /** @var \Tk\Form\Field\Option $option */
            foreach($f->getOptions() as $option) {
                /** @var \App\Db\Permission $p */
                $p = \App\Db\PermissionMap::create()->find($option->getValue());
                $option->setAttr('title', $p->description);
            }

            $selected = array();
            $userPerms = \App\Db\PermissionMap::create()->findByUserId($this->user->id);
            foreach($userPerms as $obj) {
                $selected[] = $obj->id;
            }
            $f->setValue($selected);

            /** @var \Tk\Form\Event\Submit $e */
            $e = $this->form->getField('update');
            $e->addCallback(array($this, 'doAppSubmit'));
            $e = $this->form->getField('save');
            $e->addCallback(array($this, 'doAppSubmit'));
        }

    }


    /**
     * @param \Tk\Form $form
     * @param \Tk\Form\Event\Iface $event
     * @throws \Exception
     */
    public function doAppSubmit($form, $event)
    {


        if ($form->hasErrors()) {
            return;
        }

        // Update user role list if not admin
        if ($this->user->id != 1) {
            \App\Db\PermissionMap::create()->deleteAllUserRoles($this->user->id);
            foreach ($form->getFieldValue('permission') as $roleId) {
                \App\Db\PermissionMap::create()->addUserRole($roleId, $this->user->id);
            }
        }



    }

}