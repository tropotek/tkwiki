<?php
namespace App\Ajax;

use Tk\Request;

/**
 * 
 *
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
        parent::__construct('Home');
    }

    /**
     * @param Request $request
     * @return \App\Page\Iface
     */
    public function doGetPageList(Request $request)
    {
        $keywords = trim(strip_tags($request->get('keywords')));
        $tool = \Tk\Db\Tool::createFromArray($request->all(), 'title', 5);
        
        $filter = array('keywords' => $keywords);
        /** @var \Tk\Db\Map\ArrayObject $pageList */
        $pageList = \App\Db\Page::getMapper()->findFiltered($filter, $tool);
        $list = [];
        foreach($pageList as $page) {
            if (!$this->getUser()->getAccess()->canView($page)) continue;
            $page->modified = $page->modified->format(\Tk\Date::SHORT_DATETIME);
            $page->created = $page->created->format(\Tk\Date::SHORT_DATETIME);
            $list[] = $page;
        }
        $data = array(
            'list' => $list,
            'tool' => array(
                'orderBy' => $tool->getOrderBy(),
                'offset' => $tool->getOffset(),
                'limit' => $tool->getLimit(),
                'total' => $pageList->getFoundRows(),
                'keywords' => $keywords
            )
        );
        
        
        // TODO: Make a JsonResponse object
        $json = json_encode($data);
        $response = new \Tk\Response($json);
        return $response;
    }


}