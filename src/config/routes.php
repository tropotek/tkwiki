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

use Tk\Routing\Route;

$config = \App\Config::getInstance();
$routes = $config->getRouteCollection();
if (!$routes) return;


// Site Routes
$routes->remove('public-index-php-fix');
$routes->remove('home');
$routes->remove('home-base');
$routes->remove('contact');


// Admin
$routes->add('admin-settings', new Route('/admin/settings.html', 'App\Controller\Admin\Settings::doDefault'));

// Public
$routes->add('pageView', new Route('/view.html', 'App\Controller\Page\View::doContentView'));
$routes->add('search', new Route('/search.html', 'App\Controller\Search::doDefault'));
$routes->add('contact', new Route('/contact.html', 'App\Controller\Contact::doDefault'));


// Users
$routes->add('profile', new Route('/user/profile.html', 'Bs\Controller\User\Profile::doDefault'));
$routes->add('pageEdit', new Route('/user/edit.html', 'App\Controller\Page\Edit::doDefault'));
$routes->add('pageHistory', new Route('/user/history.html', 'App\Controller\Page\History::doDefault'));
$routes->add('pageManager', new Route('/user/pageManager.html', 'App\Controller\Page\Manager::doDefault'));
$routes->add('orphaned', new Route('/user/orphaned.html', 'App\Controller\Page\Orphaned::doDefault'));

$routes->add('admin-user-manager', Route::create('/admin/userManager.html', 'Bs\Controller\User\Manager::doDefault'));
$routes->add('admin-user-edit', Route::create('/admin/userEdit.html', 'Bs\Controller\User\Edit::doDefault'));

// AJAX Routes
$routes->add('ajax-pageList', new Route('/ajax/getPageList', 'App\Ajax\Page::doGetPageList'));
$routes->add('ajax-pageLock', new Route('/ajax/lockPage', 'App\Ajax\Page::doRefreshLock'));

// DO NOT MOVE.... CatchAll must be the last route.
$routes->add('pageCatchAll', new Route('/{pageUrl}', 'App\Controller\Page\View::doDefault',
    array('pageUrl' => \App\Db\Page::DEFAULT_TAG)));


