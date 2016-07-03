<?php
namespace App\Db;

/**
 * Class LinkMap
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class LinkMap
{
    /**
     * @var LinkMap
     */
    static private $instance = null;

    /**
     * @var \Tk\Db\Pdo
     */
    protected $db = null;

    /**
     * @var Page
     */
    protected $page = null;


    /**
     *
     * @param $page
     * @param $db
     */
    public function __construct($page, $db)
    {
        $this->page = $page;
        $this->db = $db;
    }

    /**
     * 
     * @param \Tk\Db\Pdo $db
     * @return LinkMap
     */
    static function instance($page, $db = null)
    {
        if (!self::$instance) {
            if (!$db) {
                $db = \App\Factory::getDb();
            }
            self::$instance = new static($page, $db);
        }
        return self::$instance;
    }


    
    


    /**
     * Return all the Orphaned Pages
     *
     * @return Tk_Loader_Collection
     */
    static function findOrphanedPages()
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        
        
        $query = sprintf('SELECT %s FROM `%s` p LEFT JOIN `pageLink` pl ON (p.`name` = pl.`pageToName`)
WHERE pl.`pageFrom` IS NULL  AND (p.`name` != \'Home\' AND p.`name` != \'Menu\')', $mapper->getSelectList(), $mapper->getTable());
        
        $result = $mapper->getDb()->query($query);
        $coll = $mapper->makeCollection($result);
        return $coll;
    }

    /**
     * Return all the Orphaned Pages
     *
     * @return Tk_Loader_Collection
     */
    static function isOrphan($pageId)
    {
        $pageId = (int)$pageId;
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        $query = sprintf('SELECT %s FROM `%s` p LEFT JOIN `pageLink` pl ON (p.`name` = pl.`pageToName`)
WHERE pl.`pageFrom` IS NULL AND (p.`name` != \'Home\' AND p.`name` != \'Menu\') AND p.`id` = %d', $mapper->getSelectList(), $mapper->getTable(), $pageId);
        $result = $mapper->getDb()->query($query);
        if ($result->current()) {
            return true;
        }
        return false;
    }

    /**
     * insert a page link record
     *
     * @param integer $pageFromId
     * @param string $pageToName
     * @return boolean
     */
    static function insertPageLink($pageFromId, $pageToName)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        $pageFromId = intval($pageFromId);
        $pageToName = $mapper->getDb()->escapeString($pageToName);
        if (self::pageLinkExists($pageFromId, $pageToName)) {
            return false;
        }
        $query = sprintf("INSERT INTO `pageLink` VALUES (%d, '%s')", $pageFromId, $pageToName);
        $mapper->getDb()->query($query);
        return true;
    }

    /**
     * Check if a page link already exists
     *
     * @param integer $pageFromId
     * @param string $pageToName
     * @return boolean
     */
    static function pageLinkExists($pageFromId, $pageToName)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        $pageFromId = intval($pageFromId);
        $pageToName = $mapper->getDb()->escapeString($pageToName);
        $query = sprintf("SELECT COUNT(*) as i FROM `pageLink` WHERE `pageFrom` = %d AND `pageToName` = '%s'", $pageFromId, $pageToName);
        $result = $mapper->getDb()->query($query);
        $value = $result->current();
        return $value['i'] > 0;
    }

    /**
     * delete a specific page link
     *
     * @param integer $pageFromId
     * @param string $pageToName
     * @return integer
     */
    static function deletePageLink($pageFromId, $pageToName)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        $pageFromId = intval($pageFromId);
        $pageToName = $mapper->getDb()->escapeString($pageToName);
        if (!self::pageLinkExists($pageFromId, $pageToName)) {
            return 0;
        }
        $where = "`pageFrom` = $pageFromId AND `pageToName` = '$pageToName'";
        $query = sprintf('DELETE FROM `pageLink` WHERE %s LIMIT 1', $where);
        $mapper->getDb()->query($query);
        return $mapper->getDb()->getAffectedRows();
    }

    /**
     * Delete all links to and from a pageId
     *
     * @param integer $pageId
     * @return integer
     */
    static function deleteLinkByPageId($pageId)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        $pageId = intval($pageId);
        $where = "`pageFrom` = $pageId";
        $query = sprintf('DELETE FROM `pageLink` WHERE %s', $where);
        $mapper->getDb()->query($query);
        return $mapper->getDb()->getAffectedRows();
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

}