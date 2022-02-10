<?php

require __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Database\Database;

$app = new Application(__DIR__);

(require base_path() . '/app/bootstrap.php')($app);

$_ENV['host'] = '127.0.0.1';
/**
 * @var Database $db
 */
$db = $app->get(Database::class);
$db->applyMigrations();
