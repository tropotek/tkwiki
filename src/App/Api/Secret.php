<?php
namespace App\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tk\Traits\SystemTrait;

class Secret
{
    use SystemTrait;

    /**
     * TODO:  implement a hash for a more secure fetch.
     */
    public function doGetPass()
    {
        $data = ['p' => ''];
        $id = intval($_POST['id'] ?? 0);
        if ($this->getFactory()->getAuthUser() && $id) {
            $secret = \App\Db\Secret::find($id);
            if ($secret?->canView($this->getFactory()->getAuthUser())) {
                $data = ['p' => $secret->password];
            }
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

}

