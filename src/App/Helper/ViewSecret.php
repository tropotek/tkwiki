<?php
namespace App\Helper;

use App\Db\Secret;
use App\Db\SecretMap;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use JetBrains\PhpStorm\NoReturn;
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
        if (isset($_POST['o'])) {
            $this->doOtp();
        }
    }

    #[NoReturn] public function doOtp()
    {
        $response = new JsonResponse(['msg' => 'error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        $secret = Secret::find($_GET['o'] ?? 0);
        if ($secret && $secret->canView($this->getFactory()->getAuthUser())) {
            $response = new JsonResponse(['otp' => $secret->genOtpCode()]);
        }
        $response->send();
        exit;
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        $template->setText('name', $this->secret->name);
        if ($this->secret->keys || $this->secret->notes) {
            $template->prependText('name', '* ');
        }
        if ($this->secret->url) {
            $template->setAttr('name', 'href', $this->secret->url);
            $template->setVisible('url', true);
        } else {
            $template->setVisible('no-url', true);
        }
        if ($this->secret->username || $this->secret->password) {
            if ($this->secret->username) {
                $template->setText('username', $this->secret->username);
                $template->setAttr('username', 'data-text', $this->secret->username);
                $template->setVisible('user');
            }
            if ($this->secret->password) {
                $template->setText('password', str_repeat('*', strlen($this->secret->password)));
                $template->setAttr('password', 'data-text', str_repeat('*', strlen($this->secret->password)));
                $template->setAttr('password', 'data-id', $this->secret->secretId);
                $template->setVisible('pass');
            }
            $template->setVisible('userpass');
        }

        if ($this->secret->otp) {
            $template->setAttr('otp', 'data-id', $this->secret->secretId);
            $template->setVisible('o');
        }

        if ($this->secret->canEdit($this->getFactory()->getAuthUser())) {
            $template->setAttr('edit', 'href', Uri::create('/secretEdit')->set('secretId', $this->secret->secretId));
            $template->setVisible('edit');
        }

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div class="wk-secret align-top" >
  <a href="#" class="wk-secret-edit" title="Edit" choice="edit"><i class="fa fa-light fa-pen-to-square"></i></a>
  <span class="strong" var="name" choice="no-url"></span><br choice="no-url"/>
  <a href="#" target="_blank" class="strong" title="Visit site" var="name" choice="url"></a><br choice="url"/>
  <span class="userpass" choice="userpass">
    <span choice="user">U: <span class="usr" var="username"></span> <i class="fa fa-copy cp-usr" data-target=".usr" title="Copy"></i><br /></span>
    <span choice="pass">P: <span class="pas" var="password"></span> <i class="fa fa-eye pw-show" title="Show/Hide"></i> <i class="fa fa-copy cp-pas" data-target=".pas" title="Copy"></i><br choice="o"/></span>
    <span var="otp" choice="o" title="Gen and copy OTP code"><i class="fa fa-compass cp-otp"></i> <span class="otp-code">------</span></span>
  </span>
</div>
HTML;

        return $this->loadTemplate($html);
    }

}
