<?php
namespace App\Db;

use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;

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
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) {
            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addProperty(new Db\Number('id'), 'key');
            $this->dbMap->addProperty(new Db\Number('pageId', 'page_id'));
            $this->dbMap->addProperty(new Db\Number('userId', 'user_id'));
            $this->dbMap->addProperty(new Db\Text('html'));
            $this->dbMap->addProperty(new Db\Text('keywords'));
            $this->dbMap->addProperty(new Db\Text('description'));
            $this->dbMap->addProperty(new Db\Text('css'));
            $this->dbMap->addProperty(new Db\Text('js'));
            $this->dbMap->addProperty(new Db\Number('size'));
            $this->dbMap->addProperty(new Db\Date('modified'));
            $this->dbMap->addProperty(new Db\Date('created'));

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
            $this->dbMap->addProperty(new Form\Number('pageId'));
            $this->dbMap->addProperty(new Form\Number('userId'));
            $this->dbMap->addProperty(new Form\Text('html'));
            $this->dbMap->addProperty(new Form\Text('keywords'));
            $this->dbMap->addProperty(new Form\Text('description'));
            $this->dbMap->addProperty(new Form\Text('css'));
            $this->dbMap->addProperty(new Form\Text('js'));
            $this->dbMap->addProperty(new Form\Number('size'));
//            $this->dbMap->addProperty(new Form\Date('modified'));
//            $this->dbMap->addProperty(new Form\Date('created'));

            $this->setPrimaryKey($this->formMap->currentProperty('key')->getColumnName());
        }
        return $this->formMap;
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