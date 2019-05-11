<?php
namespace App\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Role extends \Bs\Db\Role
{

    // TODO: We need to deprecate these constants as they are influencing the app design
    const DEFAULT_TYPE_MODERATOR = 3;

    const TYPE_MODERATOR  = 'moderator';


}
