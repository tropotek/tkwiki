<?php
namespace App\Controller;

use App\Db\User;
use Bs\ControllerPublic;
use Dom\Template;
use Tk\Log;
use Tk\Uri;

/**
 * This is only an example contact form.
 *
 * For commercial sites you should redirect to a "thank you" page or new thank you content template
 * showing a message rather than only an alert message.
 * Most clients prefer this type of
 *
 */
class Test extends ControllerPublic
{


    public function doDefault(): void
    {
        $this->getPage()->setTitle('Test Page');

        if (!$this->getConfig()->isDev() && !User::getAuthUser()->isAdmin()) {
            Uri::create('/')->redirect();
        }

        // log level test
//        Log::debug("Log level: " . $this->getConfig()->get('log.logLevel'));
//        Log::debug("Debug Message");
//        Log::info("Info Message");
//        Log::notice("Notice Message");
//        Log::warning("Warning Message");
//        Log::error("Error Message");
//        Log::critical("Critical Message");
//        Log::alert("Alert Message");
//        Log::emergency("Emergency Message");

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
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-envelope"></i> Test</div>
    <div class="card-body" var="content">
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
    </div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}


