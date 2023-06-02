<?php
namespace App\Controller;

use App\Db\PageMap;
use Dom\Mvc\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;

class Home extends PageController
{

    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Home');
    }

    public function doDefault(Request $request)
    {
        $reg = $this->getFactory()->getRegistry();
        $reg->save();

        $pg = PageMap::create()->find(1);
        vd($pg);


        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setText('title', $this->getPage()->getTitle());


        return $template;
    }

    public function __makeTemplate()
    {
        $html = <<<HTML
<div>
    <h3 var="title">Welcome Home</h3>
    <p var="content"></p>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}


