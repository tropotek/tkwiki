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
$routes->remove('public-index-php-fix');
$routes->remove('home');
$routes->remove('home-base');
$routes->remove('contact');


// Public
$params = array();

$routes->add('pageView', new \Tk\Routing\Route('/view.html', 'App\Controller\Page\View::doContentView'));
$routes->add('search', new \Tk\Routing\Route('/search.html', 'App\Controller\Search::doDefault'));
$routes->add('contact', new \Tk\Routing\Route('/contact.html', 'App\Controller\Contact::doDefault'));


// Users
$params = array('role' => array('user', 'admin'));
$routes->add('profile', new \Tk\Routing\Route('/profile.html', 'App\Controller\Admin\User\Profile::doDefault', $params));


// Admin
$params = array('role' => array('admin'));
$routes->add('settings', new \Tk\Routing\Route('/settings.html', 'App\Controller\Admin\Settings::doDefault', $params));
$routes->add('pageManager', new \Tk\Routing\Route('/pageManager.html', 'App\Controller\Page\Manager::doDefault', $params));
$routes->add('pageEdit', new \Tk\Routing\Route('/edit.html', 'App\Controller\Page\Edit::doDefault', $params));
$routes->add('pageHistory', new \Tk\Routing\Route('/history.html', 'App\Controller\Page\History::doDefault', $params));
$routes->add('orphaned', new \Tk\Routing\Route('/orphaned.html', 'App\Controller\Page\Orphaned::doDefault', $params));


$routes->add('admin-user-manager', new \Tk\Routing\Route('/admin/userManager.html', 'App\Controller\Admin\User\Manager::doDefault', $params));
$routes->add('admin-user-edit', new \Tk\Routing\Route('/admin/userEdit.html', 'App\Controller\Admin\User\Edit::doDefault', $params));
$routes->add('admin-user-profile', new \Tk\Routing\Route('/admin/profile.html', 'App\Controller\Admin\User\Profile::doDefault', $params));


// AJAX Routes
$routes->add('ajax-pageList', new \Tk\Routing\Route('/ajax/getPageList', 'App\Ajax\Page::doGetPageList'));
$routes->add('ajax-pageLock', new \Tk\Routing\Route('/ajax/lockPage', 'App\Ajax\Page::doRefreshLock'));

// DO NOT MOVE.... CatchAll must be the last route.
$routes->add('pageCatchAll', new \Tk\Routing\Route('/{pageUrl}', 'App\Controller\Page\View::doDefault',
    array('pageUrl' => \App\Db\Page::DEFAULT_TAG)));
