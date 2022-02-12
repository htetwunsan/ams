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

switch ($argv[1] ?? '') {
    case '-r':
        echo "This actions is destructive and cannot be undone.\nType \"I am so fucking sure\" exactly without \" to continue" . PHP_EOL;
        $handle = fopen('php://stdin', 'r');
        $line = fgets($handle);
        if ($line = "I am so fucking sure") {
            $db->resetMigrations();
        }
        break;
    default:
        $db->applyMigrations();
        break;
}
