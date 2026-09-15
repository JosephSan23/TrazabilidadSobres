<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

final class HomeController {
    public function index(array $parameters = []): void 
    {
        View::render('home.index', [
            'title' => 'Modulo de Custodia',
        ]);
    }
}