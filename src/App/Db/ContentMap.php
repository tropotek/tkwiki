<?php
namespace App\Db;

use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;

/**
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
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Integer('pageId', 'page_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('userId', 'user_id'));
            $this->dbMap->addPropertyMap(new Db\Text('html'));
            $this->dbMap->addPropertyMap(new Db\Text('keywords'));
            $this->dbMap->addPropertyMap(new Db\Text('description'));
            $this->dbMap->addPropertyMap(new Db\Text('css'));
            $this->dbMap->addPropertyMap(new Db\Text('js'));
            $this->dbMap->addPropertyMap(new Db\Integer('size'));
            $this->dbMap->addPropertyMap(new Db\Date('modified'));
            $this->dbMap->addPropertyMap(new Db\Date('created'));
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
            $this->formMap->addPropertyMap(new Form\Integer('pageId'));
            $this->formMap->addPropertyMap(new Form\Integer('userId'));
            $this->formMap->addPropertyMap(new Form\Text('html'));
            $this->formMap->addPropertyMap(new Form\Text('keywords'));
            $this->formMap->addPropertyMap(new Form\Text('description'));
            $this->formMap->addPropertyMap(new Form\Text('css'));
            $this->formMap->addPropertyMap(new Form\Text('js'));
            $this->formMap->addPropertyMap(new Form\Integer('size'));
        }
        return $this->formMap;
    }

    /**
     *
     * @param $pageId
     * @param \Tk\Db\Tool $tool
     * @return ArrayObject
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
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
     * @param array $filter
     * @param Tool $tool
     * @return ArrayObject
     * @throws \Exception
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


        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $where .= '('. $w . ') AND ';
        }

        if ($where) {
            $where = substr($where, 0, -4);
        }

        $res = $this->selectFrom($from, $where, $tool);
        return $res;
    }
}
