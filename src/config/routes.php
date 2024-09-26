<?php
/**
 * Remember to refresh the cache after editing
 *
 * Reload the page with <Ctrl>+<Shift>+R
 */

use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;

/**
 * Reload the page with <Ctrl>+<Shift>+R
 *
 * @see https://symfony.com/doc/current/routing.html
 */
return function (CollectionConfigurator $routes) {

    // Public
    $routes->add('wiki-contact', '/contact')
        ->controller([\App\Controller\Contact::class, 'doDefault']);
    $routes->add('wiki-search', '/search')
        ->controller([\App\Controller\Page\Search::class, 'doDefault']);
    $routes->add('wiki-page-edit', '/edit')
        ->controller([\App\Controller\Page\Edit::class, 'doDefault']);
    $routes->add('wiki-menu-edit', '/menuEdit')
        ->controller([\App\Controller\Menu\Edit::class, 'doDefault']);
    $routes->add('wiki-page', '/pageManager')
        ->controller([\App\Controller\Page\Manager::class, 'doDefault']);
    $routes->add('wiki-history', '/historyManager')
        ->controller([\App\Controller\Page\History::class, 'doDefault']);
    $routes->add('wiki-page-view', '/view')
        ->controller([\App\Controller\Page\View::class, 'doContentView']);
    $routes->add('wiki-test-page', '/_test')
        ->controller([\App\Controller\Test::class, 'doDefault']);

    // User Public
    $routes->add('login', '/login')
        ->controller([\App\Controller\User\Login::class, 'doLogin']);
    $routes->add('logout', '/logout')
        ->controller([\App\Controller\User\Login::class, 'doLogout']);
    $routes->add('login-ssi', '/_ssi')
        ->controller([\App\Controller\User\Ssi::class, 'doDefault']);
    $routes->add('recover', '/recover')
        ->controller([\App\Controller\User\Recover::class, 'doDefault']);
    $routes->add('recover-pass', '/recoverUpdate')
        ->controller([\App\Controller\User\Recover::class, 'doRecover']);
    $routes->add('register-activate', '/registerActivate')
        ->controller([\App\Controller\User\Register::class, 'doActivate']);
    $routes->add('register', '/register')
        ->controller([\App\Controller\User\Register::class, 'doDefault']);

    // User Member
    $routes->add('user-profile', '/profile')
        ->controller([\App\Controller\User\Profile::class, 'doDefault']);

    // User Admin
    $routes->add('settings-edit', '/settings')
        ->controller([\App\Controller\Admin\Settings::class, 'doDefault']);
    $routes->add('user-type-manager', '/user/{type}Manager')
        ->controller([\App\Controller\User\Manager::class, 'doByType'])
        ->defaults(['type' => \App\Db\User::TYPE_MEMBER]);
    $routes->add('user-type-edit', '/user/{type}Edit')
        ->controller([\App\Controller\User\Edit::class, 'doDefault'])
        ->defaults(['type' => \App\Db\User::TYPE_MEMBER]);

    // Secret
    $routes->add('secret-manager', '/secretManager')
        ->controller([App\Controller\Secret\Manager::class, 'doDefault']);
    $routes->add('secret-edit', '/secretEdit')
        ->controller([App\Controller\Secret\Edit::class, 'doDefault']);

    // System pages
    $routes->add('settings-edit', '/settings')
        ->controller([\App\Controller\Admin\Settings::class, 'doDefault']);


    // API calls (Returns JSON response)
    $routes->add('api-lock-refresh', '/api/lock/refresh')
        ->controller([\App\Api\Page::class, 'doRefreshLock'])
        ->methods([\Symfony\Component\HttpFoundation\Request::METHOD_GET]);
    $routes->add('api-category-lookup', '/api/page/category')
        ->controller([\App\Api\Page::class, 'doCategorySearch'])
        ->methods([\Symfony\Component\HttpFoundation\Request::METHOD_GET]);
    $routes->add('api-secret-pass', '/api/secret/pass')
        ->controller([\App\Api\Secret::class, 'doGetPass'])
        ->methods([\Symfony\Component\HttpFoundation\Request::METHOD_POST])
        ->schemes(['https']);


    // DO NOT MOVE.... CatchAll must be the last route.
    $routes->add('wiki-catch-all', '/{pageUrl}')
        ->controller([\App\Controller\Page\View::class, 'doDefault'])
        ->defaults(['pageUrl' => \App\Db\Page::DEFAULT_TAG]);
};