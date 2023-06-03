<?php
namespace App\Controller;

use App\Db\PageMap;
use App\Factory;
use Dom\Mvc\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;

class Home extends PageController
{

    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Home');
        $this->getFactory()->getCrumbs()->reset();
    }

    public function doDefault(Request $request)
    {
        $reg = $this->getFactory()->getRegistry();
        $reg->save();

        $pg = PageMap::create()->find(1);
        vd($pg->getContent());

//        $userId = Factory::instance()->getAuthUser()?->getId() ?? 0;
//        vd($userId);

        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());


        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
    <h1 class="" var="title"></h1>
    <div class="" var="content"></div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}


