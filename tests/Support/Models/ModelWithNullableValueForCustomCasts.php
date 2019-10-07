<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

use Vkovic\LaravelCustomCasts\Test\Support\CustomCasts\Base64Cast;

class ModelWithNullableValueForCustomCasts extends ModelWithCustomCasts
{
    protected $table = 'table_c';

    protected $casts = [
        'col_1' => Base64Cast::class
    ];
}