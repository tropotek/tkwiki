<?php
include dirname(__FILE__) . '/lib/Tk/Tk.php';

try {
    // Initalize lib
    Tk::init(dirname(__FILE__), dirname(__FILE__).'/lib', 'Ext/_prepend.php');
    // Setup site front controller
    $controller =  new Tk_Web_SiteFrontController();
    
    // Add Auth Controller
    $controller->addController(new Auth_Controller(new Wik_Auth_Event(
        array (
          '/admin' => Wik_Auth_Event::GROUP_ADMIN,
          '/settings.html' => Wik_Auth_Event::GROUP_ADMIN,
          '/edit.html' => Wik_Auth_Event::GROUP_USER
        ),
        Tk_Type_Url::create('/login.html')
    )));
    
    // Component Controller
    $resourceMapper = new Wik_Web_ResourceMapper(new Tk_Type_Path(Tk_Config::get('system.templatePath')));
    $comCon = $controller->addController(new Com_Web_ComponentController($controller, $resourceMapper));
    
    // WIKI Controller
    $controller->addController(new Wik_Web_SiteController($comCon));
    $controller->execute();
    
    // Show Debug console
    if (Tk_Config::getInstance()->isDebugMode()) {
        $dcon = new Tk_Web_DebugConsole(Tk_Response::getInstance()->toString(), Tk::$scriptTime);
        $dcon->addExtra('Tk_Config', htmlentities(Tk_Config::getInstance()->toString()));
        Tk_Response::getInstance()->reset();
        Tk_Response::getInstance()->write($dcon->getHtml());
    }
    
    Tk_Response::getInstance()->flushBuffer();
    exit();
} catch (Exception $e) {
    var_dump("index.php: \n" . $e->__toString());
}
