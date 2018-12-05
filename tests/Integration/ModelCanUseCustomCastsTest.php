<?php

namespace Vkovic\LaravelCustomCasts\Test\Integration;

use Vkovic\LaravelCustomCasts\Test\Support\Models\Image;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ImageWithMutator;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ImageWithPrefixNameCast;
use Vkovic\LaravelCustomCasts\Test\TestCase;

class ModelCanUseCustomCastsTest extends TestCase
{

    public function test_custom_casts_do_not_interfere_with_default_model_casts()
    {
        $imageModel = new Image;
        $imageModel->image = 'data:image/png;image.png';
        $imageModel->data = ['size' => 1000];
        $imageModel->save();

        $imageModel = Image::find($imageModel->id);
        $this->assertTrue(is_array($imageModel->data));

        $imageModel->delete();
    }

    public function test_model_events_will_be_handled_in_custom_cast_objects()
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

    public function test_mutators_has_priority_over_custom_casts()
    {
        $imageName = str_random() . '.png';

        $imageModel = ImageWithMutator::create([
            'image' => $imageName
        ]);

        $imageModel = ImageWithMutator::find($imageModel->id);
        $this->assertEquals($imageName, $imageModel->image);

        $imageModel->delete();
    }

    public function test_can_cast_attribute_from_db()
    {
        $imageName = str_random() . '.png';

        $imageModel = ImageWithPrefixNameCast::create([
            'image' => 'data:image/png;' . $imageName
        ]);

        $imageModel = ImageWithPrefixNameCast::find($imageModel->id);

        $this->assertStringStartsWith('casted_', $imageModel->image);

        $imageModel->delete();
    }

    public function test_can_set_attribute_during_model_creation()
    {
        $imageName = str_random() . '.png';

        $imageModel = Image::create([
            // This base64 string is not valid, used just for testing
            'image' => 'data:image/png;' . $imageName,
        ]);

        $imageModel = Image::find($imageModel->id);
        $this->assertEquals($imageName, $imageModel->image);

        $imageModel->delete();
    }

    public function test_can_set_attribute_during_model_update()
    {
        $imageNameOne = str_random() . '.png';
        $imageNameTwo = str_random() . '.png';

        $imageModel = Image::create([
            'image' => 'data:image/png;' . $imageNameOne
        ]);

        $imageModel->image = 'data:image/png;' . $imageNameTwo;
        $imageModel->save();

        $imageModel = Image::find($imageModel->id);
        $this->assertEquals($imageNameTwo, $imageModel->image);

        $imageModel->delete();
    }

    protected static function getEventsReceived($imageModel)
    {
        $customCastObject = parent::getProtectedProperty($imageModel, 'customCastObjects')['image'];

        return $customCastObject->eventsReceived;
    }
}

