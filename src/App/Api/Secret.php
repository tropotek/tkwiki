<?php
namespace App\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tk\Traits\SystemTrait;

class Secret
{
    use SystemTrait;


    public function doGetPass(): JsonResponse
    {
        $response = new JsonResponse(['msg' => 'error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        $secret = \App\Db\Secret::findByHash($_POST['p'] ?? '');
        if ($secret && $secret->canView($this->getFactory()->getAuthUser())) {
            $response = new JsonResponse(['pw' => $secret->password, 'otp' => $secret->genOtpCode()]);
        }
        return $response;
    }

}

