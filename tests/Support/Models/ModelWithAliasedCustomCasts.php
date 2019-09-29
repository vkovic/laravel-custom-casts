<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

class ModelWithAliasedCustomCasts extends ModelWithCustomCasts
{
    protected $casts = [
        'field_1' => 'base64'
    ];
}