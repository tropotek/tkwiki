<?php
namespace App\Controller\Admin\User;

/**
 * @author Tropotek <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Tropotek
 */
class Profile extends \Bs\Controller\Admin\User\Profile
{




    /**
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->getActionPanel()->setEnabled(false);
    }



}