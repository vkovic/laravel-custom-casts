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
    public function nullable_custom_cast_field_will_remain_null_when_field_not_present()
    {
        $model = new ModelWithNullableValueForCustomCasts();
        $model->save(); // Save with null value (defined in migrations)

        $tableRow = DB::table('table_c')->first();

        $this->assertNull($tableRow->col_1);
    }
}