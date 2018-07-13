<?php
namespace App\Ajax;

use Tk\Request;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Page extends \App\Controller\Iface
{

    /**
     *
     */
    public function __construct()
    {
        $this->setPageTitle('Home');
    }

    /**
     * doRefreshLock
     *
     * @param Request $request
     * @return \Tk\Response
     * @throws \Tk\Db\Exception
     */
    public function doRefreshLock(Request $request)
    {
        $pageId = $request->get('pid');
        // Refresh the lock timeout to prevent user loosing the lock over long edits.
        $data = ['status' => 'ok', 'lock' => false];
        if ($this->getConfig()->getLockMap()->isLocked($pageId)) {
            $b = $this->getConfig()->getLockMap()->lock($pageId);
            $data['lock'] = $b;
        }
        $json = json_encode($data);
        $response = new \Tk\Response($json);
        return $response;
    }

    /**
     * doGetPageList
     * 
     * @param Request $request
     * @return \Tk\Response
     */
    public function doGetPageList(Request $request)
    {
        $keywords = trim(strip_tags($request->get('keywords')));
        $tool = \Tk\Db\Tool::createFromArray($request->all(), 'title', 5);
        
        $filter = array('keywords' => $keywords, 'type' => \App\Db\Page::TYPE_PAGE);
        /** @var \Tk\Db\Map\ArrayObject $pageList */
        $pageList = \App\Db\PageMap::create()->findFiltered($filter, $tool);
        $list = array();
        /** @var \App\Db\Page $page */
        foreach($pageList as $page) {
            if (!$this->getConfig()->getAcl()->canView($page)) continue;
            $page->modified = $page->modified->format(\Tk\Date::FORMAT_SHORT_DATETIME);
            $page->created = $page->created->format(\Tk\Date::FORMAT_SHORT_DATETIME);
            $list[] = $page;
        }
        $data = array(
            'list' => $list,
            'tool' => array(
                'orderBy' => $tool->getOrderBy(),
                'offset' => $tool->getOffset(),
                'limit' => $tool->getLimit(),
                'total' => $pageList->countAll(),
                'keywords' => $keywords
            )
        );
        
        $json = json_encode($data);
        $response = new \Tk\Response($json);
        return $response;
    }


}