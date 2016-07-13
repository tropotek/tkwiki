<?php
namespace App\Db;

use Tk\Db\Map\Mapper;
use Tk\Db\Map\Model;
use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;

/**
 * Class ContentMap
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ContentMap extends Mapper
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
            'page_id' => $obj->pageId,
            'user_id' => $obj->userId,
            'html' => $obj->html,
            'keywords' => $obj->keywords,
            'description' => $obj->description,
            'css' => $obj->css,
            'js' => $obj->js,
            'size' => (int)$obj->size,
            'modified' => $obj->modified->format('Y-m-d H:i:s'),
            'created' => $obj->created->format('Y-m-d H:i:s')
        );
        return $arr;
    }

    /**
     * @param array|\stdClass|Model $row
     * @return User
     */
    public function map($row)
    {
        $obj = new Content();
        $obj->id = $row['id'];
        $obj->pageId = $row['page_id'];
        $obj->userId = $row['user_id'];
        $obj->html = $row['html'];
        $obj->keywords = $row['keywords'];
        $obj->description = $row['description'];
        $obj->css = $row['css'];
        $obj->js = $row['js'];
        $obj->size = (int)$row['size'];

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
            $obj = new Content();
        }
        //$obj->id = $row['id'];
        if (isset($row['html']))
            $obj->html = $row['html'];
        if (isset($row['keywords']))
            $obj->keywords = $row['keywords'];
        if (isset($row['description']))
            $obj->discription = $row['description'];
        if (isset($row['css']))
            $obj->css = $row['css'];
        if (isset($row['js']))
            $obj->js = $row['js'];

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
            'html' => $obj->html,
            'keywords' => $obj->keywords,
            'description' => $obj->description,
            'css' => $obj->css,
            'js' => $obj->js,
            'modified' => $obj->modified->format('Y-m-d H:i:s'),
            'created' => $obj->created->format('Y-m-d H:i:s')
        );
        return $arr;
    }

    /**
     * 
     * @param $pageId
     * @param \Tk\Db\Tool $tool
     * @return ArrayObject
     */
    public function findByPageId($pageId, $tool = null)
    {
        return $this->select('page_id = ' . (int)$pageId, $tool);
    }

    /**
     * 
     * @param $userId
     * @param \Tk\Db\Tool $tool
     * @return ArrayObject
     */
    public function findByUserId($userId, $tool = null)
    {
        return $this->select('user_id = ' . (int)$userId, $tool);
    }

    /**
     * returns an array of \stdClass objects with the user_id, modified, created fields:
     * 
     * Array (
     *   [0] => stdClass Object (
     *     [user_id] => 114
     *     [modified] => 2016-06-23 08:37:27
     *     [created] => 2016-06-23 08:37:27
     *   )
     * )
     * 
     * @param $pageId
     * @return array
     */
    public function findContributors($pageId) 
    {
        // pgsql
        $sql = sprintf('SELECT DISTINCT ON (user_id) user_id, created FROM %s WHERE page_id = %s ORDER BY user_id, created DESC ',
            $this->getDb()->quoteParameter($this->getTable()), (int)$pageId);
        // Mysql
        if($this->getDb()->getDriver() == 'mysql') {
            $sql = sprintf('SELECT DISTINCT user_id, created FROM %s WHERE page_id = %s GROUP BY user_id ORDER BY user_id, created DESC ',
                $this->getDb()->quoteParameter($this->getTable()), (int)$pageId);
        }
        
        $stmt = $this->getDb()->query($sql);
        $res = array();
        foreach($stmt as $row) {
            $res[] = $row;
        }
        return $res;
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
            $w .= sprintf('a.keywords LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.description LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.html LIKE %s OR ', $this->getDb()->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) {
                $where .= '(' . substr($w, 0, -3) . ') AND ';
            }
        }

        if (!empty($filter['pageId'])) {
            $where .= sprintf('a.page_id = %d AND ', (int)$filter['pageId']);
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