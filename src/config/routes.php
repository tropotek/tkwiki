<?php
/* 
 * NOTE: Be sure to add routes in correct order as the first match will win
 * 
 * Route Structure
 * $route = new Route(
 *     '/archive/{month}',              // path
 *     '\Namespace\Class::method',      // Callable or class::method string
 *     array('month' => 'Jan'),         // Params and defaults to path params... all will be sent to the request object.
 *     array('GET', 'POST', 'HEAD')     // methods
 * );
 */

$config = \App\Config::getInstance();
$routes = $config->getRouteCollection();
if (!$routes) return;


// Site Routes

$routes->add('settings', new \Tk\Routing\Route('/settings.html', 'App\Controller\Admin\Settings::doDefault',
    array('role' => array('admin'))));
$routes->add('pageManager', new \Tk\Routing\Route('/pageManager.html', 'App\Controller\Page\Manager::doDefault',
    array('role' => array('admin'))));
$routes->add('pageEdit', new \Tk\Routing\Route('/edit.html', 'App\Controller\Page\Edit::doDefault',
    array('role' => array('admin', 'edit', 'moderator'))));
$routes->add('pageHistory', new \Tk\Routing\Route('/history.html', 'App\Controller\Page\History::doDefault',
    array('role' => array('admin', 'edit', 'moderator'))));
$routes->add('orphaned', new \Tk\Routing\Route('/orphaned.html', 'App\Controller\Page\Orphaned::doDefault',
    array('role' => array('admin', 'moderator'))));


$routes->add('pageView', new \Tk\Routing\Route('/view.html', 'App\Controller\Page\View::doContentView'));
$routes->add('search', new \Tk\Routing\Route('/search.html', 'App\Controller\Search::doDefault'));


//// TODO: Fix to only use /plugins.html
//$routes->add('admin-plugin-manager', new \Tk\Routing\Route('/plugins.html', 'App\Controller\Admin\PluginManager::doDefault', array('role' => array('admin'))));
//$routes->add('admin-plugin-manager', new \Tk\Routing\Route('/admin/plugins.html', 'App\Controller\Admin\PluginManager::doDefault', array('role' => array('admin'))));

//$routes->add('userManager', new \Tk\Routing\Route('/userManager.html', 'App\Controller\Admin\User\Manager::doDefault', array('role' => array('admin'))));
//$routes->add('userEdit', new \Tk\Routing\Route('/userEdit.html', 'App\Controller\Admin\User\Edit::doDefault'));
//$routes->add('userProfile', new \Tk\Routing\Route('/profile.html', 'App\Controller\Admin\User\Edit::doDefault'));




// AJAX Routes
$routes->add('ajax-pageList', new \Tk\Routing\Route('/ajax/getPageList', 'App\Ajax\Page::doGetPageList'));
$routes->add('ajax-pageLock', new \Tk\Routing\Route('/ajax/lockPage', 'App\Ajax\Page::doRefreshLock'));



// DO NOT MOVE.... CatchAll must be the last route.
$routes->add('pageCatchAll', new \Tk\Routing\Route('/{pageUrl}', 'App\Controller\Page\View::doDefault', array('pageUrl' => \App\Db\Page::DEFAULT_TAG)));
