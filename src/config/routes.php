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
//    $routes->add('home-base', '/')
//        ->controller([\App\Controller\Home::class, 'doDefault']);
//    $routes->add('home', '/home')
//        ->controller([\App\Controller\Home::class, 'doDefault']);

    $routes->add('wiki-page-view', '/view')
        ->controller([\App\Controller\Page\View::class, 'doContentView']);
    $routes->add('wiki-page-edit', '/edit')
        ->controller([\App\Controller\Page\Edit::class, 'doDefault']);

//    $routes->add('wiki-search', '/search')
//        ->controller([\App\Controller\Search::class, 'doDefault']);
//    $routes->add('wiki-history', '/historyManager')
//        ->controller([\App\Controller\Page\History::class, 'doDefault']);


    $routes->add('wiki-page', '/pageManager')
        ->controller([\App\Controller\Page\Manager::class, 'doDefault']);
    $routes->add('wiki-orphaned', '/orphanManager')
        ->controller([\App\Controller\Page\Orphaned::class, 'doDefault']);

    $routes->add('wiki-contact', '/contact')
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


    // API calls (Returns JSON response)
    $routes->add('api-lock-refresh', '/api/lock/refresh')
        ->controller([\App\Api\Page::class, 'doRefreshLock'])
        ->methods([\Symfony\Component\HttpFoundation\Request::METHOD_GET]);


    // DO NOT MOVE.... CatchAll must be the last route.
    $routes->add('wiki-catch-all', '/{pageUrl}')
        ->controller([\App\Controller\Page\View::class, 'doDefault'])
        ->defaults(['pageUrl' => \App\Db\Page::DEFAULT_TAG]);
};