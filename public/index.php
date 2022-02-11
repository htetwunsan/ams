<?php

require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL); // Error/Exception engine, always use E_ALL

ini_set('ignore_repeated_errors', true); // always use TRUE

ini_set('display_errors', true); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment

// ini_set('log_errors', true); // Error/Exception file logging engine.
// ini_set('error_log', dirname(__DIR__) . '/logs/errors.log'); // Logging file path


use App\Core\Application;

use App\Controllers\Api\EpisodeController;
use App\Controllers\HomeController;

header('Access-Control-Allow-Origin: *');

$app = new Application;

(require base_path() . '/app/bootstrap.php')($app);

$router = router();

$router->get('/api/create-users', [HomeController::class, 'createUsers']);
$router->get('/api/insert-user', [HomeController::class, 'insertUser']);
$router->get('/api/select-users', [HomeController::class, 'selectUsers']);
$router->get('/api/test', [HomeController::class, 'test']);


$router->get('/api/episodes/random', [EpisodeController::class, 'random']);
$router->get('/api/episodes/recently-added-sub', [EpisodeController::class, 'recentlyAddedSub']);
$router->get('/api/episodes/recently-added-raw', [EpisodeController::class, 'recentlyAddedRaw']);
$router->get('/api/episodes/movies', [EpisodeController::class, 'movies']);
$router->get('/api/episodes/kshow', [EpisodeController::class, 'kshow']);
$router->get('/api/episodes/popular', [EpisodeController::class, 'popular']);
$router->get('/api/episodes/ongoing-series', [EpisodeController::class, 'ongoingSeries']);

$router->get('/api/search', [EpisodeController::class, 'search']);

$router->get('/api/videos/{tag}/{slug}', [EpisodeController::class, 'get']);

$router->post('/api/episodes', [EpisodeController::class, 'store']);

$router->post('/api/episodes/existing', [EpisodeController::class, 'getExistingEpisodes']);

$router->setErrorHandler(404, function () {
    http_response_code(404);
    return "404 Not Found.";
});

$app->run();
