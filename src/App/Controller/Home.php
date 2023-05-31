<?php
namespace App\Controller;

use Dom\Mvc\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Alert;
use Tk\Exception;
use Tk\Uri;

class Home extends PageController
{

    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Home');
    }

    public function doDefault(Request $request)
    {
        if ($request->query->has('e')) {
            throw new Exception('This is a test exception...', 500);
        }
        if ($request->query->has('a')) {
            Alert::addSuccess('This is a success alert', '', 'fa-solid fa-circle-check');
            Alert::addInfo('This is a info alert', '', 'fa-solid fa-circle-info');
            Alert::addWarning('This is a warning alert', '', 'fa-solid fa-triangle-exclamation');
            Alert::addError('This is a error alert', '', 'fa-solid fa-circle-exclamation');
            Uri::create()->remove('a')->redirect();
        }
        $reg = $this->getFactory()->getRegistry();
        $reg->save();

        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setText('title', $this->getPage()->getTitle());

        $template->setAttr('eurl', 'href', Uri::create()->set('e', true));
        $template->setAttr('aurl', 'href', Uri::create()->set('a', true));

        return $template;
    }

    public function __makeTemplate()
    {
        $html = <<<HTML
<div>
    <h3 var="title">Welcome Home</h3>
    <p var="content"></p>

    <p app-is-user="true">Status: You are logged in!</p>
    <p app-is-user="false">Status You are not logged in</p>

    <ul>
      <li><a href="#?e" var="eurl">Test Exception</a></li>
      <li><a href="/info" title="Confirmation Dialog Test" data-confirm="<p><em>Are you sure?</em></p>" data-cancel="Nuh!!">Confirm Test</a></li>
      <li><a href="#?a" var="aurl">Alert Test</a></li>
<!--      <li><a href="/install">Install Page</a></li>-->
    </ul>
    <p>&nbsp;</p>


</div>
HTML;
        return $this->loadTemplate($html);
    }

}


