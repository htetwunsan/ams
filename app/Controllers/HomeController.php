<?php

namespace App\Controllers;

use App\Core\Database\Contracts\QueryBuilderContract;

class HomeController
{

    public function test(QueryBuilderContract $queryBuilder): string
    {

        ob_start();
        include base_path() . '/app/Views/home.php';
        return ob_get_clean();
        // title cover description
        // $queryBuilder->upsert('videos', ['title' => 'test title', 'cover' => 'duplicate', 'description' => 'asdadas']);

        // $queryBuilder->upsert('videos', ['title' => 'Updated title', 'cover' => 'duplicates', 'description' => 'Updated']);

        // dd($queryBuilder->getPdo()->lastInsertId());
    }
}
