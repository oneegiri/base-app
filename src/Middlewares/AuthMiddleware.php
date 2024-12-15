<?php

namespace App\Middlewares;

use App\Interfaces\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(array $params): ?string
    {
        if (!isset($_SESSION['user'])) {
            http_response_code(403);
            return '403 Forbidden';
        }

        return null; // Proceed to the next middleware or controller
    }
}

