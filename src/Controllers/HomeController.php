<?php

namespace App\Controllers;

use App\Attributes\Route;
use App\Attributes\Middleware;
use App\Middlewares\AuthMiddleware;

class HomeController
{
    #[Route(method: 'GET', path: '/home')]
    #[Middleware(middlewares: [AuthMiddleware::class])]
    public function index(array $params)
    {
        return 'Welcome to Home!';
    }

    #[Route(method: 'POST', path: '/submit')]
    public function submit(array $params)
    {
        return 'Form submitted!';
    }
}

