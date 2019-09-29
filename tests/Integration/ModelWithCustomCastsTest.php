<?php

namespace Vkovic\LaravelCustomCasts\Test\Integration;

use DB;
use Illuminate\Support\Str;
use Vkovic\LaravelCustomCasts\Test\Support\CustomCasts\Base64Cast;
use Vkovic\LaravelCustomCasts\Test\Support\Models\Image;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ImageWithMutator;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithAliasedCustomCasts;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithCustomCasts;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithDefaultValueForCustomCasts;
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
        $model->field_1 = $string;
        $model->save();

        // Get raw data (as stdClass) without using `Model`
        $data = DB::table('data')->find(1);

        // Raw data should be base 64 encoded string
        $this->assertSame(base64_encode($string), $data->field_1);
    }

    /**
     * @test
     */
    public function can_access_attribute_via_custom_casts()
    {
        $string = Str::random();
        $b64String = base64_encode($string);

        // Save field directly without using `Model`
        DB::table('data')->insert([
            'field_1' => $b64String
        ]);

        $model = ModelWithCustomCasts::first();

        // Retrieved data should be same as initial string
        $this->assertSame($string, $model->field_1);
    }

    /**
     * @test
     */
    public function mutator_has_priority_over_custom_casts()
    {
        $model = new ModelWithMutatorAndCustomCasts;
        $model->field_1 = 'mutated_via_custom_casts';
        $model->save();

        $data = DB::table('data')->first();

        $this->assertEquals('mutated_via_mutator', $data->field_1);
    }

    /**
     * @test
     */
    public function accessor_has_priority_over_custom_casts()
    {
        DB::table('data')->insert(['field_1' => '']);

        $model = ModelWithMutatorAndCustomCasts::first();

        $this->assertEquals('accessed_via_accessor', $model->field_1);
    }

    /**
     * @test
     */
    public function it_can_handle_custom_cast_field_with_db_default_value()
    {
//        $imageModel = Image::create(['thumb' => 'data:image/png;thumb.png']);
//
//        $this->assertEquals('thumb.png', $imageModel->thumb);
//
//        $imageModelTwo = Image::create();
//
//        $imageModelTwo->refresh();
//
//        $this->assertEquals('thumb_placeholder.png', $imageModelTwo->thumb);
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
            'field_1' => Base64Cast::class,
        ];

        $this->assertEquals($customCasts, $model1->getCustomCasts());
        $this->assertEquals($customCasts, $model2->getCustomCasts());
    }
}



