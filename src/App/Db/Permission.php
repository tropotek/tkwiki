<?php
namespace App\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Permission extends \Bs\Db\Permission
{

    const TYPE_MODERATOR = 'type.moderator';

    /**
     *
     */
    const PAGE_CREATE = 'perm.create';

    /**
     *
     */
    const PAGE_EDIT = 'perm.edit';

    /**
     *
     */
    const PAGE_DELETE = 'perm.delete';

    /**
     *
     */
    const PAGE_EDIT_EXTRA = 'perm.editExtra';



}