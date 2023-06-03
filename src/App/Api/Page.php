<?php
namespace App\Api;

use App\Db\PageMap;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tk\Db\Tool;
use Tk\Traits\SystemTrait;

class Page
{
    use SystemTrait;

    /**
     *
     */
    public function doGetPublic(Request $request): string
    {
        $data = [];
vd();
        $list = PageMap::create()->findFiltered([
            'type'       => \App\Db\Page::TYPE_PAGE,
            'permission' => \App\Db\Page::PERM_PUBLIC,
            'published'  => true,
        ], Tool::create('created', 25));
        vd($list);
        $data['test'] = [
            'msg' => 'testing output'
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

}

