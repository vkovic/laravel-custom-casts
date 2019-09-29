<?php

namespace Vkovic\LaravelCustomCasts\Test\Integration;

use Vkovic\LaravelCustomCasts\Test\Support\Models\Image;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ImageWithMutator;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithDefaultValueForCustomCasts;
use Vkovic\LaravelCustomCasts\Test\TestCase;

class ModelWithDefaultValueForCustomCastFieldTest extends TestCase
{
    /**
     * @test
     */
    public function can_mutate_custom_cast_field_with_default_db_value()
    {
        $model = new ModelWithDefaultValueForCustomCasts;
        $model->col_1 = 'test';
        $model->save();

        $model->refresh();

        $this->assertSame('test', $model->col_1);
    }

    /**
     * @test
     */
    public function can_access_custom_cast_field_with_default_db_value()
    {
        $model = new ModelWithDefaultValueForCustomCasts;
        $model->save(); // Save with default value (defined in migrations)

        $model->refresh();

        $this->assertSame('col_1_value', $model->col_1);
    }
}