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


// Admin
$routes->add('admin-settings', new \Tk\Routing\Route('/admin/settings.html', 'App\Controller\Admin\Settings::doDefault'));


// Public
$routes->add('pageView', new \Tk\Routing\Route('/view.html', 'App\Controller\Page\View::doContentView'));
$routes->add('search', new \Tk\Routing\Route('/search.html', 'App\Controller\Search::doDefault'));
$routes->add('contact', new \Tk\Routing\Route('/contact.html', 'App\Controller\Contact::doDefault'));


// Users
$routes->add('profile', new \Tk\Routing\Route('/user/profile.html', 'Bs\Controller\User\Profile::doDefault'));
$routes->add('pageEdit', new \Tk\Routing\Route('/user/edit.html', 'App\Controller\Page\Edit::doDefault'));
$routes->add('pageHistory', new \Tk\Routing\Route('/user/history.html', 'App\Controller\Page\History::doDefault'));
$routes->add('pageManager', new \Tk\Routing\Route('/user/pageManager.html', 'App\Controller\Page\Manager::doDefault'));
$routes->add('orphaned', new \Tk\Routing\Route('/user/orphaned.html', 'App\Controller\Page\Orphaned::doDefault'));


// AJAX Routes
$routes->add('ajax-pageList', new \Tk\Routing\Route('/ajax/getPageList', 'App\Ajax\Page::doGetPageList'));
$routes->add('ajax-pageLock', new \Tk\Routing\Route('/ajax/lockPage', 'App\Ajax\Page::doRefreshLock'));

// DO NOT MOVE.... CatchAll must be the last route.
$routes->add('pageCatchAll', new \Tk\Routing\Route('/{pageUrl}', 'App\Controller\Page\View::doDefault',
    array('pageUrl' => \App\Db\Page::DEFAULT_TAG)));


