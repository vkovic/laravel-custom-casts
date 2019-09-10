<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Vkovic\LaravelCustomCasts\HasCustomCasts;
use Vkovic\LaravelCustomCasts\Test\Support\CustomCasts\Base64ImageCast;

class Image extends Model
{
    use HasCustomCasts {
    	guessCastClassName as traitGuessCastClassName;
    }

    protected $guarded = [];
    protected $table = 'images';

    protected $casts = [
        'data' => 'array',
        // The same underlying cast expressed in two different ways
        'image' => 'base64_image',
        'thumb' => Base64ImageCast::class,
    ];

    protected function guessCastClassName($identifier)
    {
    	$class = str_replace(' ', '', ucwords(str_replace('_', ' ', $identifier))) . 'Cast';
    	$fqcn = 'Vkovic\\LaravelCustomCasts\\Test\\Support\\CustomCasts\\'  . $class;

    	return class_exists($fqcn)
			? $fqcn
			: $identifier;
    }
}