<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Vkovic\LaravelCustomCasts\HasCustomCasts;
use Vkovic\LaravelCustomCasts\Test\Support\CustomCasts\Base64Cast;

class ModelWithDefaultValueForCustomCasts extends Model
{
    use HasCustomCasts;

    protected $guarded = [];
    protected $table = 'data_1';

    protected $casts = [
        'field_1' => 'array'
    ];
}