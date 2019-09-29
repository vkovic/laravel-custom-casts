<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\Models;

class ModelWithMutatorAndCustomCasts extends ModelWithCustomCasts
{
    public function setField1Attribute($value)
    {
        $this->attributes['field_1'] = 'mutated_via_mutator';
    }

    public function getField1Attribute()
    {
        return 'accessed_via_accessor';
    }
}