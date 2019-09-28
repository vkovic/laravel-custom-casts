<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

class ImageWithHumanReadableCasts extends Image
{
    protected $casts = [
        'data' => 'array',
        'image' => 'b64image',
    ];
}