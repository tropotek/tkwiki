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

    public function __makeTemplate()
    {
        $html = <<<HTML
<div>
  <div class="card mb-3">
    <div class="card-header"><i class="fa fa-cogs"></i> Actions</div>
    <div class="card-body" var="actions">
      <a href="/" title="Back" class="btn btn-outline-secondary" var="back"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-users"></i> Home</div>
    <div class="card-body" var="content"></div>
  </div>
</div>
<div>
    <h3 var="title">Welcome Home</h3>
    <p var="content"></p>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}


