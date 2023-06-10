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
     * @throws \Exception
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

}
