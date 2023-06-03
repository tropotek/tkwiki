<?php
/**
 * Remember to refresh the cache after editing
 *
 * Reload the page with <Ctrl>+<Shift>+R
 */

use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;

// @see https://symfony.com/doc/current/routing.html
return function (CollectionConfigurator $routes) {

    // Site public pages
    $routes->add('home-base', '/')
        ->controller([\App\Controller\Home::class, 'doDefault']);
    $routes->add('home', '/home')
        ->controller([\App\Controller\Home::class, 'doDefault']);
    $routes->add('user-dashboard', '/dashboard')
        ->controller([\App\Controller\Dashboard::class, 'doDefault']);
    $routes->add('contact', '/contact')
        ->controller([\App\Controller\Contact::class, 'doDefault']);


    // Auth pages
    $routes->add('login', '/login')
        ->controller([\App\Controller\Login::class, 'doLogin']);
    $routes->add('logout', '/logout')
        ->controller([\App\Controller\Login::class, 'doLogout']);
    $routes->add('recover', '/recover')
        ->controller([\App\Controller\Recover::class, 'doDefault']);
    $routes->add('recover-pass', '/recoverUpdate')
        ->controller([\App\Controller\Recover::class, 'doRecover']);
    $routes->add('register', '/register')
        ->controller([\App\Controller\Register::class, 'doDefault']);
    $routes->add('register-activate', '/registerActivate')
        ->controller([\App\Controller\Register::class, 'doActivate']);

    // System pages
    $routes->add('settings-edit', '/settings')
        ->controller([\App\Controller\Admin\Settings::class, 'doDefault']);
    $routes->add('staff-manager', '/staffManager')
        ->controller([\App\Controller\User\Manager::class, 'doDefault']);
    $routes->add('user-manager', '/userManager')
        ->controller([\App\Controller\User\Manager::class, 'doDefault']);
    $routes->add('user-edit', '/userEdit')
        ->controller([\App\Controller\User\Edit::class, 'doDefault']);
    $routes->add('user-profile', '/profile')
        ->controller([\App\Controller\User\Profile::class, 'doDefault']);


    // Wiki pages





    // Content View API (Returns HTML|text response)
//    $routes->add('v-table-user', '/vapi/table/user/{type}')
//        ->controller([\App\Table\User::class, 'doDefault'])
//    ->methods([\Symfony\Component\HttpFoundation\Request::METHOD_GET])
//        ->defaults(['type' => \App\Db\User::TYPE_USER]);



    // API calls (Returns JSON response)

    // get all public pages
    $routes->add('api-pages-public', '/api/pages/public')
        ->controller([\App\Api\Page::class, 'doGetPublic'])
        ->methods([\Symfony\Component\HttpFoundation\Request::METHOD_GET]);





};