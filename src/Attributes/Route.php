<?php

namespace App\Attributes;

#[\Attribute]
class Route
{
    public function __construct(
        public string $method,
        public string $path
    ) {}
}
