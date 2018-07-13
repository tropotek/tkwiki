<?php
namespace App\Db;

use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;

/**
 * 
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PageMap extends Mapper
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
            $this->dbMap->addPropertyMap(new Db\Integer('userId', 'user_id'));
            $this->dbMap->addPropertyMap(new Db\Text('type'));
            $this->dbMap->addPropertyMap(new Db\Text('template'));
            $this->dbMap->addPropertyMap(new Db\Text('title'));
            $this->dbMap->addPropertyMap(new Db\Text('url'));
            $this->dbMap->addPropertyMap(new Db\Integer('permission'));
            $this->dbMap->addPropertyMap(new Db\Integer('views'));
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
            $this->formMap->addPropertyMap(new Form\Integer('userId'));
            $this->formMap->addPropertyMap(new Form\Text('type'));
            $this->formMap->addPropertyMap(new Form\Text('template'));
            $this->formMap->addPropertyMap(new Form\Text('title'));
            $this->formMap->addPropertyMap(new Form\Text('url'));
            $this->formMap->addPropertyMap(new Form\Integer('permission'));
            $this->formMap->addPropertyMap(new Form\Integer('views'));
        }
        return $this->formMap;
    }




    /**
     *
     * @param $userId
     * @param \Tk\Db\Tool $tool
     * @return ArrayObject
     */
    public function findByUserId($userId, $tool = null)
    {
        $sql = sprintf('user_id = %s AND type = %s', (int)$userId, $this->getDb()->quote(\App\Db\Page::TYPE_PAGE));
        return $this->select($sql, $tool);
    }

    /**
     *
     * @param $userId
     * @param \Tk\Db\Tool $tool
     * @return ArrayObject
     */
    public function findUserPages($userId, $permissions = array(), $tool = null)
    {
        $sql = sprintf('user_id = %s AND type = %s', (int)$userId, $this->getDb()->quote(\App\Db\Page::TYPE_PAGE));
        $perms = '';
        foreach($permissions as $p) {
            $perms .= sprintf(' permission = %s OR ', $this->getDb()->quote($p));
        }
        if ($perms) {
            $sql .= ' AND (' . rtrim($perms, 'OR ') . ') ';
        }
        return $this->select($sql, $tool);
    }

    /**
     *
     * @param \Tk\Db\Tool $tool
     * @return ArrayObject
     */
    public function findNavPages($tool = null)
    {
        $sql = sprintf('type = %s', $this->getDb()->quote(\App\Db\Page::TYPE_NAV));
        return $this->select($sql, $tool);
    }

    /**
     *
     * @param $url
     * @return Page
     */
    public function findByUrl($url)
    {
        $sql = sprintf('url = %s AND type = %s', $this->getDb()->quote($url), $this->getDb()->quote(\App\Db\Page::TYPE_PAGE));
        return $this->select($sql)->current();
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
            $w .= sprintf('LOWER(a.title) LIKE %s OR ', strtolower($this->getDb()->quote($kw)));
            $w .= sprintf('LOWER(a.url) LIKE %s OR ', strtolower($this->getDb()->quote($kw)));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) {
                $where .= '(' . substr($w, 0, -3) . ') AND ';
            }
        }

        if (isset($filter['permission'])) {
            $where .= sprintf('a.permission = %s AND ', (int)$filter['permission']);
        }
        if (isset($filter['type'])) {
            $where .= sprintf('a.type = %s AND ', $this->getDb()->quote($filter['type']));
        }
        
        if ($where) {
            $where = substr($where, 0, -4);
        }

        $res = $this->selectFrom($from, $where, $tool);
        return $res;
    }
    
    
    /**
     * Return all the Orphaned Pages
     *
     * @param \Tk\Db\Tool $tool
     * @return ArrayObject
     */
    public function findOrphanedPages($tool)
    {

        $homeUrl = \App\Config::getInstance()->get('wiki.page.default');
        
        $from = sprintf('%s a LEFT JOIN %s b ON (a.%s = b.%s)', $this->getDb()->quoteParameter($this->getTable()), $this->getDb()->quoteParameter('links'),
            $this->getDb()->quoteParameter('url'), $this->getDb()->quoteParameter('page_url'));
        $where = sprintf('b.%s IS NULL AND (a.%s != %s AND a.%s != %s)', $this->getDb()->quoteParameter('page_id'), 
            $this->getDb()->quoteParameter('url'), $this->getDb()->quote($homeUrl), 
            $this->getDb()->quoteParameter('type'), $this->getDb()->quote(Page::TYPE_NAV));

        $res = $this->selectFrom($from, $where, $tool);
        return $res;
        
        
//        $sql = sprintf('SELECT a.* FROM %s a LEFT JOIN links b ON (a.url = b.page_url)
//WHERE b.page_id IS NULL AND (a.url != %s AND a.type != %s)', $this->getDb()->quoteParameter($this->getTable()),
//            $this->getDb()->quote($homeUrl), $this->getDb()->quote(Page::TYPE_NAV) );
//        $res = $this->getDb()->query($sql);
//        $arr = ArrayObject::createFromMapper($this, $res, $tool);
//        return $arr;
    }

    /**
     * Return all the Orphaned Pages
     *
     * @param $pageId
     * @return bool
     */
    public function isOrphan($pageId)
    {
        $homeUrl = \App\Config::getInstance()->get('wiki.page.default');
        $sql = sprintf('SELECT a.* FROM %s a LEFT JOIN links b ON (a.url = b.page_url)
WHERE b.page_id IS NULL AND (a.url != %s AND a.type != %s AND a.id = %s)', $this->getDb()->quoteParameter($this->getTable()),
            $this->getDb()->quote($homeUrl), $this->getDb()->quote(Page::TYPE_NAV), (int)$pageId );
        $res = $this->getDb()->query($sql);
        if ($res->rowCount() > 0) return true;
        return false;
    }
    
    /**
     * insert a page link record
     *
     * @param integer $pageId   The current page ID
     * @param string $pageUrl   The link page url
     * @return boolean
     */
    public function insertLink($pageId, $pageUrl)
    {
        if ($this->linkExists($pageId, $pageUrl)) {
            return false;
        }
        $sql = sprintf('INSERT INTO links VALUES (%d, %s)', (int)$pageId, $this->getDb()->quote($pageUrl));
        $this->getDb()->exec($sql);
        return true;
    }

    /**
     * Check if a page link already exists
     *
     * @param integer $pageId   The current page ID
     * @param string $pageUrl   The link page url
     * @return boolean
     */
    public function linkExists($pageId, $pageUrl)
    {
        $sql = sprintf('SELECT COUNT(*) as i FROM links WHERE page_id = %d AND page_url = %s', (int)$pageId, $this->getDb()->quote($pageUrl));
        $res = $this->getDb()->query($sql);
        $value = $res->fetch();
        if (!$value) return false;
        return ($value->i > 0);
    }

    /**
     * delete a specific page link
     *
     * @param integer $pageId   The current page ID
     * @param string $pageUrl   The link page url
     * @return integer
     */
    public function deleteLink($pageId, $pageUrl)
    {
        if (!$this->linkExists($pageId, $pageUrl)) {
            return false;
        }
        $sql = sprintf('DELETE FROM links WHERE page_id = %s AND page_url = %s LIMIT 1', (int)$pageId, $this->getDb()->quote($pageUrl));
        $this->getDb()->exec($sql);
        return true;
    }

    /**
     * Delete all links to and from a pageId
     *
     * @param integer $pageId
     * @return integer
     */
    public function deleteLinkByPageId($pageId)
    {
        $sql = sprintf('DELETE FROM links WHERE page_id = %s', (int)$pageId);
        $res = $this->getDb()->exec($sql);
        return $res;
    }
}