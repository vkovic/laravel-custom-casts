<?php

namespace Vkovic\LaravelCustomCasts\Test\Integration;

use DB;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithNullableValueForCustomCasts;
use Vkovic\LaravelCustomCasts\Test\TestCase;

class ModelWithNullableCustomCastFieldTest extends TestCase
{
    /**
     * @test
     */
    public function can_mutate_nullable_custom_cast_field()
    {
        $model = new ModelWithNullableValueForCustomCasts();
        $model->save(); // Save with null value (defined in migrations)

        $data = DB::table('table_c')->first();

        $this->assertNull($data->col_1);
    }
}