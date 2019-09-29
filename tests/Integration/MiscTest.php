<?php

namespace Vkovic\LaravelCustomCasts\Test\Integration;

use DB;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithDefaultValue;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithDefaultValueForCustomCasts;
use Vkovic\LaravelCustomCasts\Test\TestCase;

class MiscTest extends TestCase
{
    /**
     * To be able to understand this test see:
     * https://github.com/vkovic/laravel-custom-casts/issues/5
     *
     * Until better solutions is found, we'll act upon decision from mentioned issue
     *
     * @test
     */
    public function it_mimics_default_eloquent_behavior_on_model_creation_with_default_value()
    {
        //
        // Laravel logic
        //

        $model = ModelWithDefaultValue::create();

        $this->assertNull($model->col_1);
        $this->assertSame(base64_encode('col_1_value'), $model->refresh()->col_1);

        //
        // Our logic
        //

        $model = ModelWithDefaultValueForCustomCasts::create();

        $this->assertNull($model->col_1);
        $this->assertSame('col_1_value', $model->refresh()->col_1);
    }
}



