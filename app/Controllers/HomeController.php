<?php

namespace App\Controllers;

use App\Core\Database\Contracts\QueryBuilderContract;
use App\Core\Request;

class HomeController
{

    public function test(Request $request): string
    {
        $url = $request->getBody()['url'] ?? "";
        $content = "";
        if ($url) $content = file_get_contents($url);
        ob_start();
        include base_path() . '/app/Views/home.php';
        echo str_replace("<script>", "", $content);
        return ob_get_clean();
        // title cover description
        // $queryBuilder->upsert('videos', ['title' => 'test title', 'cover' => 'duplicate', 'description' => 'asdadas']);

        // $queryBuilder->upsert('videos', ['title' => 'Updated title', 'cover' => 'duplicates', 'description' => 'Updated']);

        // dd($queryBuilder->getPdo()->lastInsertId());
    }
}
