<?php

namespace Vkovic\LaravelCustomCasts\Test\Integration;

use DB;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithDefaultValueForCustomCasts;
use Vkovic\LaravelCustomCasts\Test\TestCase;

class ModelWithDefaultValueForCustomCastFieldTest extends TestCase
{
    /**
     * @test
     */
    public function default_custom_cast_field_will_remain_default_when_field_not_present()
    {
        $model = new ModelWithDefaultValueForCustomCasts;
        $model->save(); // Save with default value (defined in migrations)

        $tableRow = DB::table('table_b')->first();

        $this->assertSame('col_1_value', base64_decode($tableRow->col_1));
    }
}