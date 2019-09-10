<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Vkovic\LaravelCustomCasts\HasCustomCasts;
use Vkovic\LaravelCustomCasts\Test\Support\CustomCasts\Base64ImageCast;

class Image extends Model
{
    use HasCustomCasts;

    protected $guarded = [];
    protected $table = 'images';

    protected $casts = [
        'data' => 'array',
        'image' => 'base64_image',
        'thumb' => 'base64_image',
    ];
}