<?php

use League\OAuth2\Client\Provider\Github;
use PickleWeb\Auth\GithubProvider;

require __DIR__ . '/../vendor/autoload.php';

function check_or_create_json_dir(\PickleWeb\Application $app)
{
    if (is_dir($app->config('json_path')) === false) {
        mkdir($app->config('json_path'), 0777, true);
        mkdir($app->config('json_path') . 'users/github', 0777, true);
        mkdir($app->config('json_path') . 'extensions', 0777, true);
    }
}

$app = new \PickleWeb\Application(
    [
        'view'      => new \PickleWeb\View\Twig(),
        'json_path' => __DIR__ . '/json/',
        'cache_dir' => __DIR__ . '/../cache-dir/',
    ]
);

/*
 * Declare service
 *
 * example :
 *
 * $app->container->singleton('authorization.oauth2.github', function ($container) {
 *     return new \PickleWeb\Action\AuthAction('github');
 * });
 */

// Github Authorization provider
$app->container->singleton(
    'authentication.provider.github',
    function ($container) {
        return new GithubProvider(
            new Github(
                [
                    'clientId'     => getenv('GITHUB_CLIENT_ID'),
                    'clientSecret' => getenv('GITHUB_CLIENT_SECRET'),
                    'scopes'       => ['user:email', 'read:repo_hook'],
                ]
            )
        );
    }
);

/*
 * Declare controllers if you need to inject dependancies in it
 *
 * example :
 *
 * $app->container->singleton('PickleWeb\Controller\AuthController', function ($container) {
 *     return new PickleWeb\Controller\AuthController($container['authorization.oauth2.github']);
 * });
 */

/*
 * Declare routes
 */

// Default
$app->get('/', 'PickleWeb\Controller\DefaultController:indexAction');

// Authorization
$app->get('/login', 'PickleWeb\Controller\AuthController:loginAction');
$app->get('/logout', 'PickleWeb\Controller\AuthController:logoutAction');
$app->get('/login/:provider', 'PickleWeb\Controller\AuthController:loginWithProviderAction');

// Packages
$app->getSecured('/package/register', 'PickleWeb\Controller\PackageController:registerAction');
$app->postSecured('/package/register', 'PickleWeb\Controller\PackageController:registerPackageAction');
$app->get('/package/:vendor/:package', 'PickleWeb\Controller\PackageController:viewPackageAction');

// Users
$app->getSecured('/profile', 'PickleWeb\Controller\UserController:profileAction');
$app->get('/account(/:name)', 'PickleWeb\Controller\UserController:viewAccountAction');

/*
 * Run application
 */
$app->run();
