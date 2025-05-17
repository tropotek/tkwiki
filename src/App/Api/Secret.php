<?php
namespace App\Api;

use App\Db\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Secret
{

    public function doGetPass(): JsonResponse
    {
        $response = new JsonResponse(['msg' => 'error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        $secret = \App\Db\Secret::findByHash($_POST['p'] ?? '');
        if ($secret && $secret->canView(User::getAuthUser())) {
            $response = new JsonResponse(['pw' => $secret->password, 'otp' => $secret->genOtpCode()]);
        }
        return $response;
    }

}

