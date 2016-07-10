<?php
namespace App\Db;

use Tk\Db\Map\Mapper;
use Tk\Db\Map\Model;
use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;

/**
 * Class UserMap
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class UserMap extends Mapper
{
    /**
     * 
     * @param \stdClass|Model $obj
     * @return array
     */
    public function unmap($obj)
    {
        $arr = array(
            'id' => $obj->id,
            'name' => $obj->name,
            'email' => $obj->email,
            'image' => $obj->image,
            'username' => $obj->username,
            'password' => $obj->password,
            'active' => (int)$obj->active,
            'hash' => $obj->hash,
            'modified' => $obj->modified->format('Y-m-d H:i:s'),
            'created' => $obj->created->format('Y-m-d H:i:s')
        );
        if ($obj->lastLogin)
            $arr['last_login'] = $obj->lastLogin->format('Y-m-d H:i:s');
            
        return $arr;
    }

    /**
     * @param array|\stdClass|Model $row
     * @return User
     */
    public function map($row)
    {
        $obj = new User();
        $obj->id = $row['id'];
        $obj->name = $row['name'];
        $obj->email = $row['email'];
        $obj->image = $row['image'];
        $obj->username = $row['username'];
        $obj->password = $row['password'];
        $obj->active = ($row['active'] == 1);
        $obj->hash = $row['hash'];

        if ($row['last_login'])
            $obj->lastLogin = \Tk\Date::create($row['last_login']);
        if ($row['modified'])
            $obj->modified = \Tk\Date::create($row['modified']);
        if ($row['created'])
            $obj->created = \Tk\Date::create($row['created']);
        return $obj;
    }

    /**
     * @param array $row
     * @param User $obj
     * @return User
     */
    static function mapForm($row, $obj = null)
    {
        if (!$obj) {
            $obj = new User();
        }
        //$obj->id = $row['id'];
        if (isset($row['name']))
            $obj->name = $row['name'];
        if (isset($row['email']))
            $obj->email = $row['email'];
        if (isset($row['username']))
            $obj->username = $row['username'];
        if (isset($row['password']))
            $obj->password = $row['password'];
        if (isset($row['active']))
            $obj->active = ($row['active'] == 'active');

        // TODO: This has to be tested, should parse date string using config['system.date.format.php']
        if (isset($row['modified']))
            $obj->modified = \Tk\Date::create($row['modified']);
        if (isset($row['created']))
            $obj->created = \Tk\Date::create($row['created']);

        return $obj;
    }

    static function unmapForm($obj)
    {
        $arr = array(
            'id' => $obj->id,
            'name' => $obj->name,
            'email' => $obj->email,
            'username' => $obj->username,
            'password' => $obj->password,
            'active' => (int)$obj->active,
            'modified' => $obj->modified->format('Y-m-d H:i:s'),
            'created' => $obj->created->format('Y-m-d H:i:s')
        );
        return $arr;
    }
    
    /**
     * 
     * 
     * @param $username
     * @return mixed
     */
    public function findByUsername($username)
    {
        $result = $this->select('username = ' . $this->getDb()->quote($username) );
        return $result->current();
    }


    public function findByEmail($email)
    {
        return $this->select('email = ' . $this->getDb()->quote($email))->current();
    }

    public function findByHash($hash)
    {
        return $this->select('hash = ' . $this->getDb()->quote($hash))->current();
    }



    /**
     * Find filtered records
     *
     * @param array $filter
     * @param Tool $tool
     * @return ArrayObject
     */
    public function findFiltered($filter = array(), $tool = null)
    {
        $from = sprintf('%s a ', $this->getDb()->quoteParameter($this->getTable()));
        $where = '';

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->getDb()->escapeString($filter['keywords']) . '%';
            $w = '';
            $w .= sprintf('a.name LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.username LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.email LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.role LIKE %s OR ', $this->getDb()->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) {
                $where .= '(' . substr($w, 0, -3) . ') AND ';
            }
        }
        
//        if (!empty($filter['lti_context_id'])) {
//            $where .= sprintf('a.lti_context_id = %s AND ', $this->getDb()->quote($filter['lti_context_id']));
//        }


        if ($where) {
            $where = substr($where, 0, -4);
        }

        $res = $this->selectFrom($from, $where, $tool);
        return $res;
    }
}