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
            $obj->modified = new \DateTime($row['modified']);
        if ($row['created'])
            $obj->created = new \DateTime($row['created']);
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
            $obj->modified = new \DateTime($row['modified']);
        if (isset($row['created']))
            $obj->created = new \DateTime($row['created']);

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
        return $this->select('user_id = ' . (int)$userId, $tool);
    }

    /**
     *
     * @param $url
     * @return Page
     */
    public function findByUrl($url)
    {
        return $this->select('url = ' . $this->getDb()->quote($url))->current();
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
            $w .= sprintf('a.title LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.url LIKE %s OR ', $this->getDb()->quote($kw));;
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