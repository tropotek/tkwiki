<?php
/**
 * Created by PhpStorm.
 *
 * @date 16-05-2016
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
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

$config = \Tk\Config::getInstance();

$routes = new \Tk\Routing\RouteCollection();
$config['site.routes'] = $routes;


$routes->add('login', new \Tk\Routing\Route('/login.html', 'App\Controller\Login::doDefault'));
$routes->add('logout', new \Tk\Routing\Route('/logout.html', 'App\Controller\Logout::doDefault'));
$routes->add('register', new \Tk\Routing\Route('/register.html', 'App\Controller\Register::doDefault'));
$routes->add('recover', new \Tk\Routing\Route('/recover.html', 'App\Controller\Recover::doDefault'));

$routes->add('settings', new \Tk\Routing\Route('/settings.html', 'App\Controller\Admin\Settings::doDefault'));

$routes->add('userManager', new \Tk\Routing\Route('/userManager.html', 'App\Controller\Admin\User\Manager::doDefault'));
$routes->add('userEdit', new \Tk\Routing\Route('/userEdit.html', 'App\Controller\Admin\User\Edit::doDefault'));
$routes->add('userProfile', new \Tk\Routing\Route('/profile.html', 'App\Controller\Admin\User\Edit::doDefault'));



// TODO: Implement the pages
//$routes->add('search', new \Tk\Routing\Route('/search.html', 'App\Controller\Search::doDefault'));

//$routes->add('orphaned', new \Tk\Routing\Route('/orphaned.html', 'App\Controller\Page\Orphaned::doDefault'));

//$routes->add('pageEdit', new \Tk\Routing\Route('/edit.html', 'App\Controller\Page\Edit:doDefault'));
//$routes->add('pageHistory', new \Tk\Routing\Route('/history.html', 'App\Controller\Page\History::doDefault'));

//$routes->add('myPages', new \Tk\Routing\Route('/edit.html', 'App\Controller\Page\Manager::doDefault'));

//$routes->add('contact', new \Tk\Routing\Route('/contact.html', 'App\Controller\Contact::doDefault'));



// DO NOT MOVE.... CatchAll must be the last route.
$routes->add('pageView', new \Tk\Routing\Route('/{name}', 'App\Controller\Page\View::doDefault', array('name' => 'Home')));
