<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

class ModelWithMutatorAndCustomCasts extends ModelWithCustomCasts
{
    public function setCol1Attribute($value)
    {
        $this->attributes['col_1'] = 'mutated_via_mutator';
    }

    public function getCol1Attribute()
    {
        return 'accessed_via_accessor';
    }
}