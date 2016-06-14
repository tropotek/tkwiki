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

// Default Home catchall
$routes->add('home', new \Tk\Routing\Route('/index.html', 'App\Controller\Index::doDefault'));
$routes->add('home-base', new \Tk\Routing\Route('/', 'App\Controller\Index::doDefault'));
$routes->add('contact', new \Tk\Routing\Route('/contact.html', 'App\Controller\Contact::doDefault'));

$routes->add('login', new \Tk\Routing\Route('/login.html', 'App\Controller\Login::doDefault'));
$routes->add('logout', new \Tk\Routing\Route('/logout.html', 'App\Controller\Logout::doDefault'));
$routes->add('register', new \Tk\Routing\Route('/register.html', 'App\Controller\Register::doDefault'));
$routes->add('recover', new \Tk\Routing\Route('/recover.html', 'App\Controller\Recover::doDefault'));

// Admin Pages
$params = array('test' => 'admin', 'access' => array('admin'));
$routes->add('admin-home', new \Tk\Routing\Route('/admin/index.html', 'App\Controller\Admin\Index::doDefault', $params));
$routes->add('admin-home-base', new \Tk\Routing\Route('/admin/', 'App\Controller\Admin\Index::doDefault', $params));

$routes->add('admin-user-manager', new \Tk\Routing\Route('/admin/userManager.html', 'App\Controller\Admin\User\Manager::doDefault', $params));
$routes->add('admin-user-edit', new \Tk\Routing\Route('/admin/userEdit.html', 'App\Controller\Admin\User\Edit::doDefault', $params));
$routes->add('admin-user-profile', new \Tk\Routing\Route('/admin/profile.html', 'App\Controller\Admin\User\Edit::doDefault', $params));


// User Pages
$params = array('access' => array('user'));
$routes->add('user-home', new \Tk\Routing\Route('/user/index.html', 'App\Controller\User\Index::doDefault', $params));
$routes->add('user-home-base', new \Tk\Routing\Route('/user/', 'App\Controller\User\Index::doDefault', $params));




// Example: How to do a simple controller/route all-in-one
$routes->add('simpleTest', new \Tk\Routing\Route('/test.html', function ($request) use ($config) {
    vd($config->toString());
    return '<p>This is a simple test</p>';
}, $params));

