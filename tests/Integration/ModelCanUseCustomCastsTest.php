<?php

namespace Vkovic\LaravelCustomCasts\Test\Integration;

use Illuminate\Support\Str;
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
    public function model_events_will_be_handled_in_custom_cast_objects()
    {
        //
        // Creating
        //

        $imageModel = Image::create([
            // This base64 string is not valid, used just for testing
            'image' => 'data:image/png;image_1.png'
        ]);

        $eventsReceived = self::getEventsReceived($imageModel);

        $this->assertContains('creating', $eventsReceived);
        $this->assertContains('created', $eventsReceived);

        //
        // Updating
        //

        $imageModel = Image::find($imageModel->id);
        $imageModel->image = 'data:image/png;image_2.png';
        $imageModel->save();

        $eventsReceived = self::getEventsReceived($imageModel);

        $this->assertContains('updating', $eventsReceived);
        $this->assertContains('updated', $eventsReceived);

        //
        // Deleting
        //

        $imageModel = Image::find($imageModel->id);
        $imageModel->delete();

        $eventsReceived = self::getEventsReceived($imageModel);

        $this->assertContains('deleting', $eventsReceived);
        $this->assertContains('deleted', $eventsReceived);
    }

    /**
     * @test
     */
    public function mutators_has_priority_over_custom_casts()
    {
        $imageName = Str::random() . '.png';

        $imageModel = ImageWithMutator::create([
            'image' => $imageName
        ]);

        $imageModel = ImageWithMutator::find($imageModel->id);

        $this->assertEquals($imageName, $imageModel->image);
    }

    /**
     * @test
     */
    public function it_can_cast_attribute_from_db()
    {
        $imageName = Str::random() . '.png';

        $imageModel = ImageWithPrefixNameCast::create([
            'image' => 'data:image/png;' . $imageName
        ]);

        $imageModel = ImageWithPrefixNameCast::find($imageModel->id);

        $this->assertStringStartsWith('casted_', $imageModel->image);
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

    /**
     * @test
     */
    public function it_can_get_handle_custom_cast_field_with_db_default_value()
    {
        $imageModel = Image::create(['thumb' => 'data:image/png;thumb.png']);

        $this->assertEquals('thumb.png', $imageModel->thumb);

        $imageModelTwo = Image::create();

        $imageModelTwo->refresh();

        $this->assertEquals('thumb_placeholder.png', $imageModelTwo->thumb);
    }

    /**
     * @test
     */
    public function it_can_get_custom_cast_fields()
    {
        $imageModel = new Image();

        $customCasts = [
            'image' => 'Vkovic\LaravelCustomCasts\Test\Support\CustomCasts\Base64ImageCast',
            'thumb' => 'Vkovic\LaravelCustomCasts\Test\Support\CustomCasts\Base64ImageCast',
        ];

        $this->assertEquals($customCasts, $imageModel->getCustomCasts());
    }

    /**
     * @test
     */
    public function it_can_use_human_readable_casts()
    {
        $imageModel = new ImageWithHumanReadableCasts;
        $imageModel->image = 'data:image/png;image.png';
        $imageModel->data = ['size' => 1000];
        $imageModel->save();

        $imageModel = Image::find($imageModel->id);

        $this->assertEquals('image.png', $imageModel->image);

        $imageModel->delete();
    }

    protected static function getEventsReceived($imageModel)
    {
        $customCastObject = parent::getProtectedProperty($imageModel, 'customCastObjects')['image'];

        return $customCastObject->eventsReceived;
    }
}

