<?php
namespace App\Db;

use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;

/**
 * Class RoleMap
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class RoleMap extends Mapper
{

    /**
     *
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) {
            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addProperty(new Db\Number('id'), 'key');
            $this->dbMap->addProperty(new Db\Text('name'));
            $this->dbMap->addProperty(new Db\Text('description'));

            $this->setPrimaryKey($this->dbMap->currentProperty('key')->getColumnName());
        }
        return $this->dbMap;
    }

    /**
     *
     * @return \Tk\DataMap\DataMap
     */
    public function getFormMap()
    {
        if (!$this->formMap) {
            $this->formMap = new \Tk\DataMap\DataMap();
            $this->formMap->addProperty(new Form\Number('id'), 'key');
            $this->formMap->addProperty(new Form\Text('name'));
            $this->formMap->addProperty(new Form\Text('description'));

            $this->setPrimaryKey($this->formMap->currentProperty('key')->getColumnName());
        }
        return $this->formMap;
    }


    /**
     * @param $name
     * @return Role
     */
    public function findByName($name)
    {
        return $this->select('name = ' . $this->getDb()->quote($name))->current();
    }

    /**
     * @param int $userId
     * @param Tool $tool
     * @return ArrayObject
     */
    public function findByUserId($userId, $tool = null)
    {
        $from = sprintf('%s a, user_role b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = b.role_id AND b.user_id = %d', (int)$userId);
        return $this->selectFrom($from, $where, $tool);
    }

    /**
     * @param $roleId
     * @param $userId
     * @return \App\Db\Role
     */
    public function findRole($roleId, $userId)
    {
        $from = sprintf('%s a, user_role b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = %d AND a.id = b.role_id AND b.user_id = %d', (int)$roleId, (int)$userId);
        return $this->selectFrom($from, $where)->current();
    }


    /**
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function deleteAllUserRoles($userId)
    {
        $query = sprintf('DELETE FROM user_role WHERE user_id = %d', (int)$userId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param $roleId
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function deleteUserRole($roleId, $userId)
    {
        $query = sprintf('DELETE FROM user_role WHERE user_id = %d AND role_id = %d', (int)$userId, (int)$roleId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param $roleId
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function addUserRole($roleId, $userId)
    {
        $query = sprintf('INSERT INTO user_role (user_id, role_id)  VALUES (%d, %d)', (int)$userId, (int)$roleId);
        return $this->getDb()->exec($query);
    }


}