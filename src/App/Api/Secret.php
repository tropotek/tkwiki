<?php
namespace App\Api;

use Bs\Traits\SystemTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Secret
{
    use SystemTrait;

    public function doGetPass(): JsonResponse
    {
        $response = new JsonResponse(['msg' => 'error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        $secret = \App\Db\Secret::findByHash($_POST['p'] ?? '');
        if ($secret && $secret->canView($this->getAuthUser())) {
            $response = new JsonResponse(['pw' => $secret->password, 'otp' => $secret->genOtpCode()]);
        }
        return $response;
    }

}

