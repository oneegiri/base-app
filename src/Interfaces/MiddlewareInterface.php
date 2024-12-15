<?php

namespace App\Interfaces;

interface MiddlewareInterface
{
    public function handle(array $params): ?string;
}
