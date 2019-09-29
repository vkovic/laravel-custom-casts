<?php

namespace Vkovic\LaravelCustomCasts\Test\Integration;

use Illuminate\Support\Str;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithCustomCasts;
use Vkovic\LaravelCustomCasts\Test\Support\Models\Image;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ImageWithHumanReadableCasts;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ImageWithMutator;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ImageWithPrefixNameCast;
use Vkovic\LaravelCustomCasts\Test\TestCase;

class ModelCanUseCustomCastsTest extends TestCase
{
    /**
     * @test
     */
    public function custom_casts_do_not_interfere_with_default_model_casts()
    {
        $imageModel = new Image;
        $imageModel->image = 'data:image/png;image.png';
        $imageModel->data = ['size' => 1000];
        $imageModel->save();

        $imageModel = Image::find($imageModel->id);
        $this->assertTrue(is_array($imageModel->data));

        $imageModel->delete();
    }

    /**
     * @test
     */
    public function it_can_set_attribute_during_model_creation()
    {
        $imageName = Str::random() . '.png';

        $imageModel = Image::create([
            // This base64 string is not valid, used just for testing
            'image' => 'data:image/png;' . $imageName,
        ]);

        $imageModel = Image::find($imageModel->id);

        $this->assertEquals($imageName, $imageModel->image);
    }

    /**
     * @test
     */
    public function it_can_set_attribute_during_model_update()
    {
        $imageNameOne = Str::random() . '.png';
        $imageNameTwo = Str::random() . '.png';

        $imageModel = Image::create([
            'image' => 'data:image/png;' . $imageNameOne
        ]);

        $imageModel->image = 'data:image/png;' . $imageNameTwo;
        $imageModel->save();

        $imageModel = Image::find($imageModel->id);

        $this->assertEquals($imageNameTwo, $imageModel->image);
    }

    /**
     * @test
     */
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

