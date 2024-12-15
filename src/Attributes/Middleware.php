<?php

namespace App\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Middleware
{
    public function __construct(
        public array $middlewares
    ) {}
}
