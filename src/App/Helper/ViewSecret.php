<?php
namespace App\Helper;

use App\Db\Secret;
use Bs\Traits\SystemTrait;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Uri;

class ViewSecret extends Renderer implements DisplayInterface
{
    use SystemTrait;

    protected Secret $secret;


    public function __construct(Secret $secret)
    {
        $this->secret = $secret;
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        $template->setAttr('secret', 'data-secret-hash', $this->secret->hash);
        $template->setText('name', $this->secret->name);

        if ($this->secret->keys || $this->secret->notes) {
            $template->prependText('name', '* ');
        }
        if ($this->secret->url) {
            $template->setAttr('name', 'href', $this->secret->url);
            $template->setVisible('url');
        } else {
            $template->setVisible('no-url');
        }

        if ($this->secret->canView($this->getAuthUser())) {
            if ($this->secret->publish) {
                if ($this->secret->username || $this->secret->password) {
                    if ($this->secret->username) {
                        $template->setText('username', $this->secret->username);
                        $template->setVisible('user');
                    }
                    if ($this->secret->password) {
                        $template->setText('password', str_repeat('*', strlen($this->secret->password)));
                        $template->setVisible('pass');
                    }
                    $template->setVisible('userpass');
                }

                if ($this->secret->otp) {
                    $template->setVisible('o');
                }
            } else {
                $template->addCss('name', 'text-strike');
                $template->setAttr('name', 'title', 'Unpublished');
            }
        }

        if ($this->secret->canEdit($this->getAuthUser())) {
            $template->setAttr('edit', 'href', Uri::create('/secretEdit')->set('h', $this->secret->hash));
            $template->setVisible('edit');
        }

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div class="wk-secret align-top" var="secret">
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
