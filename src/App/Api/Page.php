<?php
namespace App\Api;

use App\Db\Lock;
use Bs\Traits\SystemTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Page
{
    use SystemTrait;

    /**
     * Refresh the lock timeout to prevent user losing the lock over long edits.
     */
    public function doRefreshLock(): Response
    {
        $data = ['status' => 'ok', 'lock' => false];
        $pageId = $_REQUEST['pid'] ?? 0;
        if ($this->getFactory()->getAuthUser()) {
            $lock = new Lock($this->getFactory()->getAuthUser());
            if ($lock->isLocked($pageId)) {
                $b = $lock->lock($pageId);
                $data['lock'] = $b;
            }
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Search for all available categories using a supplied search term
     * Used for the jquery UI autocomplete component
     */
    public function doCategorySearch(): Response
    {
        $data = \App\Db\Page::getCategoryList(trim($_GET['term'] ?? ''));
        //$data = PageMap::create()->getCategoryList(trim($request->query->getString('term', '')));
        return new JsonResponse($data, Response::HTTP_OK);
    }

}

