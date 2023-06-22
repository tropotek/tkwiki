<?php
namespace App\Api;

use App\Db\SecretMap;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tk\Traits\SystemTrait;

class Secret
{
    use SystemTrait;

    /**
     * TODO: we should implement a hash for a more secure fetch.
     */
    public function doGetPass(Request $request)
    {
        $data = ['p' => ''];
        $id = $request->query->getInt('id', 0);
        if ($this->getFactory()->getAuthUser() && $id) {
            /** @var \App\Db\Secret $secret */
            $secret = SecretMap::create()->find($id);
            if ($secret?->canView($this->getFactory()->getAuthUser())) {
                $data = ['p' => $secret->getPassword()];
            }
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

}

