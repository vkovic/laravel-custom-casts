<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class ModelWithDefaultValue extends Model
{
    use HasCustomCasts;

    protected $guarded = [];
    protected $table = 'table_b';
}