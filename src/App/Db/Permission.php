<?php
namespace App\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Permission extends \Tk\Db\Map\Model
{
    /**
     * @deprecated
     */
    const ROLE_ADMIN = 'admin';
    /**
     * @deprecated
     */
    const ROLE_USER = 'user';



    const ROLE_MODERATOR = 'moderator';
    const ROLE_CREATE = 'create';
    const ROLE_EDIT = 'edit';
    const ROLE_DELETE = 'delete';
    const ROLE_EDIT_EXTRA = 'editExtra';


    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $description = '';


    /**
     *
     */
    public function __construct()
    {

    }


}