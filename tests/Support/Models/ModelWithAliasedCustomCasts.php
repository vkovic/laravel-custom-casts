<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

class ModelWithAliasedCustomCasts extends ModelWithCustomCasts
{
    protected $casts = [
        'col_1' => 'base64'
    ];
}