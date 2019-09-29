<?php

namespace Vkovic\LaravelCustomCasts\Test\Integration;

use DB;
use Illuminate\Support\Str;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithAliasedCustomCasts;
use Vkovic\LaravelCustomCasts\Test\TestCase;

class ModelWithAliasedCustomCastsTest extends TestCase
{
    /**
     * @test
     */
    public function can_mutate_attribute_via_aliased_custom_casts()
    {
        // Write model data via `Model` object with aliased casts
        $string = Str::random();
        $model = new ModelWithAliasedCustomCasts;
        $model->id = 2;
        $model->col_1 = $string;
        $model->save();

        // Get raw data (as stdClass) without using `Model`
        $data = DB::table('table_a')->find(2);

        // Raw data should be base 64 encoded string
        $this->assertSame(base64_encode($string), $data->col_1);
    }

    /**
     * @test
     */
    public function can_access_attribute_via_aliased_custom_casts()
    {
        $string = Str::random();
        $b64String = base64_encode($string);

        // Save field directly without using `Model`
        DB::table('table_a')->insert([
            'col_1' => $b64String
        ]);

        $model = ModelWithAliasedCustomCasts::first();

        // Retrieved data should be same as initial string
        $this->assertSame($string, $model->col_1);
    }
}



