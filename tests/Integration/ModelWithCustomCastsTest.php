<?php

namespace Vkovic\LaravelCustomCasts\Test\Integration;

use DB;
use Illuminate\Support\Str;
use Vkovic\LaravelCustomCasts\Test\Support\CustomCasts\Base64Cast;
use Vkovic\LaravelCustomCasts\Test\Support\Models\Image;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ImageWithMutator;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithAliasedCustomCasts;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithCustomCasts;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithMutatorAndCustomCasts;
use Vkovic\LaravelCustomCasts\Test\TestCase;

class ModelWithCustomCastsTest extends TestCase
{
    /**
     * @test
     */
    public function can_mutate_attribute_via_custom_casts()
    {
        // Write model data via `Model` object
        $string = Str::random();

        $model = new ModelWithCustomCasts;
        $model->col_1 = $string;
        $model->save();

        // Get raw data (as stdClass) without using `Model`
        $tableRow = DB::table('table_a')->find(1);

        // Raw data should be base 64 encoded string
        $this->assertSame(base64_encode($string), $tableRow->col_1);
    }

    /**
     * @test
     */
    public function can_access_attribute_via_custom_casts()
    {
        $string = Str::random();
        $b64String = base64_encode($string);

        // Save field directly without using `Model`
        DB::table('table_a')->insert([
            'col_1' => $b64String
        ]);

        $model = ModelWithCustomCasts::first();

        // Retrieved data should be same as initial string
        $this->assertSame($string, $model->col_1);
    }

    /**
     * @test
     */
    public function can_mutate_attribute_via_custom_casts_when_using_create()
    {
        // Write model data via `Model` object
        $string = Str::random();

        ModelWithCustomCasts::create([
            'col_1' => $string
        ]);

        // Get raw data (as stdClass) without using `Model`
        $tableRow = DB::table('table_a')->find(1);

        // Raw data should be base 64 encoded string
        $this->assertSame(base64_encode($string), $tableRow->col_1);
    }

    /**
     * @test
     */
    public function can_update_custom_cast_attribute()
    {
        DB::table('table_a')->insert([
            'col_1' => ''
        ]);

        $string = Str::random();

        $model = ModelWithCustomCasts::first();
        $model->col_1 = $string;
        $model->save();

        $tableRow = DB::table('table_a')->first();

        $this->assertSame(base64_encode($string), $tableRow->col_1);
    }

    /**
     * @test
     */
    public function mutator_has_priority_over_custom_casts()
    {
        $model = new ModelWithMutatorAndCustomCasts;
        $model->col_1 = 'mutated_via_custom_casts';
        $model->save();

        $tableRow = DB::table('table_a')->first();

        $this->assertEquals('mutated_via_mutator', $tableRow->col_1);
    }

    /**
     * @test
     */
    public function accessor_has_priority_over_custom_casts()
    {
        DB::table('table_a')->insert(['col_1' => '']);

        $model = ModelWithMutatorAndCustomCasts::first();

        $this->assertEquals('accessed_via_accessor', $model->col_1);
    }

    /**
     * @test
     */
    public function can_get_list_of_custom_casts()
    {
        $model1 = new ModelWithCustomCasts;
        $model2 = new ModelWithAliasedCustomCasts;

        // This is actual custom casts defined in both models (from above)
        // but in second as and alias (which should resolve to a class)
        $customCasts = [
            'col_1' => Base64Cast::class,
        ];

        $this->assertEquals($customCasts, $model1->getCustomCasts());
        $this->assertEquals($customCasts, $model2->getCustomCasts());
    }

    //
    // NOT DONE
    //

    public function it_can_get_custom_cast_field_from_newly_created_model_when_refresh_is_called()
    {
        // TODO
        // https://github.com/vkovic/laravel-custom-casts/issues/5
        // Until better solutions is found, we'll act upon decision from mentioned issue

        $imageModel = Image::create(['thumb' => 'data:image/png;thumb_placeholder.png']);

        $this->assertNull($imageModel->image);

        $imageModel->refresh();

        $this->assertEquals('placeholder.png', $imageModel->image);
    }

}



