<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\CustomCasts;

use Vkovic\LaravelCustomCasts\CustomCastBase;

class Base64Cast extends CustomCastBase
{
    public function setAttribute($value)
    {
        return base64_encode($value);
    }

    public function castAttribute($value)
    {
        return base64_decode($value);
    }
}