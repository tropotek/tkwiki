<?php
namespace App\Controller\Admin\User;

/**
 * @author Tropotek <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Tropotek
 */
class Manager extends \Bs\Controller\Admin\User\Manager
{




    /**
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        //$this->getActionPanel()->setEnabled(false);
        $this->editUrl = \Bs\Uri::create('/admin/userEdit.html');
    }



}