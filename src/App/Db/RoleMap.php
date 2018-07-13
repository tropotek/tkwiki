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
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Text('name'));
            $this->dbMap->addPropertyMap(new Db\Text('description'));
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
            $this->formMap->addPropertyMap(new Form\Integer('id'), 'key');
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Text('description'));
        }
        return $this->formMap;
    }


    /**
     * @param string $name
     * @return Role|\Tk\Db\Map\Model
     * @throws \Tk\Db\Exception
     */
    public function findByName($name)
    {
        return $this->select('name = ' . $this->getDb()->quote($name))->current();
    }

    /**
     * @param int $userId
     * @param Tool $tool
     * @return ArrayObject|Role[]
     * @throws \Tk\Db\Exception
     */
    public function findByUserId($userId, $tool = null)
    {
        $from = sprintf('%s a, user_role b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = b.role_id AND b.user_id = %d', (int)$userId);
        return $this->selectFrom($from, $where, $tool);
    }

    /**
     * @param int $roleId
     * @param int $userId
     * @return Role|\Tk\Db\Map\Model
     * @throws \Tk\Db\Exception
     */
    public function findRole($roleId, $userId)
    {
        $from = sprintf('%s a, user_role b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = %d AND a.id = b.role_id AND b.user_id = %d', (int)$roleId, (int)$userId);
        return $this->selectFrom($from, $where)->current();
    }


    /**
     * @param int $userId
     * @return \Tk\Db\PDOStatement
     * @throws \Tk\Db\Exception
     */
    public function deleteAllUserRoles($userId)
    {
        $query = sprintf('DELETE FROM user_role WHERE user_id = %d', (int)$userId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param int $roleId
     * @param int $userId
     * @return \Tk\Db\PDOStatement
     * @throws \Tk\Db\Exception
     */
    public function deleteUserRole($roleId, $userId)
    {
        $query = sprintf('DELETE FROM user_role WHERE user_id = %d AND role_id = %d', (int)$userId, (int)$roleId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param int $roleId
     * @param int $userId
     * @return \Tk\Db\PDOStatement
     * @throws \Tk\Db\Exception
     */
    public function addUserRole($roleId, $userId)
    {
        $query = sprintf('INSERT INTO user_role (user_id, role_id)  VALUES (%d, %d)', (int)$userId, (int)$roleId);
        return $this->getDb()->exec($query);
    }


}