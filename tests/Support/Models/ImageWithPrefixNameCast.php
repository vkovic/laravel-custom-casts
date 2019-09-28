<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

use Vkovic\LaravelCustomCasts\Test\Support\CustomCasts\PrefixNameCast;

class ImageWithPrefixNameCast extends Image
{
    protected $casts = [
        'image' => PrefixNameCast::class
    ];
}