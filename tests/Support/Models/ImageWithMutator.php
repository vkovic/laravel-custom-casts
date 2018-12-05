<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

class ImageWithMutator extends Image
{
    public function setImageAttribute($value)
    {
        $this->attributes['image'] = $value;
    }
}