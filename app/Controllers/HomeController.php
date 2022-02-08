<?php

namespace App\Controllers;

class HomeController
{
    public function __invoke(): string
    {
        ob_start();
        include base_path() . '/app/views/home.php';
        return ob_get_clean();
    }
}
