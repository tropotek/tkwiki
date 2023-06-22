<?php
namespace App\Helper;

use App\Db\Secret;
use App\Db\SecretMap;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tk\Traits\SystemTrait;
use Tk\Uri;

/**
 * Render the secret output
 */
class ViewSecret extends Renderer implements DisplayInterface
{
    use SystemTrait;

    protected Secret $secret;


    public function __construct(Secret $secret)
    {
        $this->secret = $secret;
        if ($this->getRequest()->request->getInt('o')) {
            $this->doOtp($this->getRequest());
        }
    }

    public function doOtp(Request $request)
    {
        $response = new JsonResponse(['msg' => 'error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        /** @var \App\Db\Secret $secret */
        $secret = SecretMap::create()->find($request->request->getInt('o', 0));
        if ($secret && $secret->canView($this->getFactory()->getAuthUser())) {
            $response = new JsonResponse(['otp' => $secret->genOtpCode()]);
        }
        $response->send();
        exit;
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        $template->setText('name', $this->secret->getName());
        if ($this->secret->getKeys() || $this->secret->getNotes()) {
            $template->prependText('name', '* ');
        }
        if ($this->secret->getUrl()) {
            $template->setAttr('name', 'href', $this->secret->getUrl());
            $template->setVisible('url', true);
        } else {
            $template->setVisible('no-url', true);
        }
        if ($this->secret->getUsername()) {
            $template->setText('username', $this->secret->getUsername());
            $template->setAttr('username','data-text', $this->secret->getUsername());
            $template->setText('password', str_repeat('*', strlen($this->secret->getPassword())));
            $template->setAttr('password','data-text', $this->secret->getPassword());
            $template->setVisible('userpass');
        }

        if ($this->secret->getOtp()) {
            $template->setAttr('otp', 'data-id', $this->secret->getId());
            $template->setVisible('o');
        }

        if ($this->secret->canEdit($this->getFactory()->getAuthUser())) {
            $template->setAttr('edit', 'href', Uri::create('/secretEdit')->set('id', $this->secret->getId()));
            $template->setVisible('edit');
        }

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div class="wk-secret">
  <a href="#" class="wk-secret-edit" title="Edit" choice="edit"><i class="fa fa-light fa-pen-to-square"></i></a>
  <span class="strong" var="name" choice="no-url"></span><br choice="no-url"/>
  <a href="#" target="_blank" class="strong" title="Visit site" var="name" choice="url"></a><br choice="url"/>
  <span class="userpass" choice="userpass">
    U: <span class="usr" var="username"></span> <i class="fa fa-copy cp-usr" data-target=".usr" title="Copy"></i><br />
    P: <span class="pas" var="password"></span> <i class="fa fa-eye pw-show" title="Show/Hide"></i> <i class="fa fa-copy cp-pas" data-target=".pas" title="Copy"></i><br choice="o"/>
    <span var="otp" choice="o" title="Gen and copy OTP code"><i class="fa fa-compass cp-otp"></i> <span class="otp-code">------</span></span>
  </span>
</div>
HTML;

        return $this->loadTemplate($html);
    }

}
