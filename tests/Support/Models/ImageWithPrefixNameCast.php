<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

use Vkovic\LaravelCustomCasts\Test\Support\CustomCasts\PrefixNameCast;

class ImageWithPrefixNameCast extends Image
{
    protected $casts = [
        'image' => 'prefix_name',
    ];
}