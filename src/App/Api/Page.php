<?php
namespace App\Api;

use App\Db\Lock;
use App\Db\PageMap;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tk\Traits\SystemTrait;

class Page
{
    use SystemTrait;


    /**
     * Refresh the lock timeout to prevent user losing the lock over long edits.
     */
    public function doRefreshLock(Request $request): Response
    {
        $data = ['status' => 'ok', 'lock' => false];
        $pageId = $request->get('pid');
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
    public function doCategorySearch(Request $request): Response
    {
        $data = PageMap::create()->getCategoryList(trim($request->query->getString('term', '')));
        return new JsonResponse($data, Response::HTTP_OK);
    }

}

