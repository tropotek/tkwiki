<?php
namespace App\Db;

use Tk\Db\Map\Mapper;
use Tk\Db\Map\Model;
use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;

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
     * @param \stdClass|Model $obj
     * @return array
     */
    public function unmap($obj)
    {
        $arr = array(
            'id' => $obj->id,
            'user_id' => $obj->userId,
            'type' => $obj->type,
            'template' => $obj->template,
            'title' => $obj->title,
            'url' => $obj->url,
            'permission' => $obj->permission,
            'views' => (int)$obj->views,
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
        $obj = new Page();
        $obj->id = $row['id'];
        $obj->userId = $row['user_id'];
        $obj->type = $row['type'];
        $obj->template = $row['template'];
        $obj->title = $row['title'];
        $obj->url = $row['url'];
        $obj->permission = (int)$row['permission'];
        $obj->views = (int)$row['views'];

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
            $obj = new Page();
        }
        //$obj->id = $row['id'];
        if (isset($row['userId']))
            $obj->userId = $row['userId'];
        if (isset($row['type']))
            $obj->type = $row['type'];
        if (isset($row['template']))
            $obj->template = $row['template'];
        if (isset($row['title']))
            $obj->title = $row['title'];
        if (isset($row['url']))
            $obj->url = $row['url'];
        if (isset($row['views']))
            $obj->views = $row['views'];
        if (isset($row['permission']))
            $obj->permission = $row['permission'];

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
            'userId' => $obj->userId,
            'type' => $obj->type,
            'template' => $obj->template,
            'title' => $obj->title,
            'url' => $obj->url,
            'permission' => $obj->permission,
            'views' => $obj->views,
            'modified' => $obj->modified->format('Y-m-d H:i:s'),
            'created' => $obj->created->format('Y-m-d H:i:s')
        );
        return $arr;
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
    public function findUserPages($userId, $permissions = [], $tool = null)
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
            //$where .= sprintf('a.type = %s AND ', $this->getDb()->quote(\App\Db\Page::TYPE_PAGE));
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
        $homeUrl = \App\Factory::getConfig()->get('wiki.page.default');
        $sql = sprintf('SELECT a.* FROM %s a LEFT JOIN links b ON (a.url = b.page_url)
WHERE b.page_id IS NULL AND (a.url != %s AND a.type != %s)', $this->getDb()->quoteParameter($this->getTable()),
            $this->getDb()->quote($homeUrl), $this->getDb()->quote(Page::TYPE_NAV) );
        $res = $this->getDb()->query($sql);
        $arr = ArrayObject::createFromMapper($this, $res, $tool);
        return $arr;
    }

    /**
     * Return all the Orphaned Pages
     *
     * @param $pageId
     * @return bool
     */
    public function isOrphan($pageId)
    {
        $homeUrl = \App\Factory::getConfig()->get('wiki.page.default');
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