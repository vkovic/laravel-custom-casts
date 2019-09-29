<?php

namespace Vkovic\LaravelCustomCasts\Test\Integration;

use DB;
use Illuminate\Database\Eloquent\Model;
use Vkovic\LaravelCustomCasts\Test\Support\Models\ModelWithEventHandlingCast;
use Vkovic\LaravelCustomCasts\Test\TestCase;

class CanHandleModelEventsTest extends TestCase
{
    /**
     * @test
     */
    public function can_handle_creating_events()
    {
        $model = new ModelWithEventHandlingCast;
        $model->field_1 = '';
        $model->save();

        $eventsReceived = self::getEventsReceived($model);

        $this->assertContains('creating', $eventsReceived);
        $this->assertContains('created', $eventsReceived);
    }

    /**
     * @test
     */
    public function can_handle_updating_event()
    {
        // Manually create a record in db
        DB::table('data')->insert(['field_1' => 'a']);

        $model = ModelWithEventHandlingCast::first();
        $model->field_1 = 'b';
        $model->save();

        $eventsReceived = self::getEventsReceived($model);

        $this->assertContains('updating', $eventsReceived);
        $this->assertContains('updated', $eventsReceived);
    }

    /**
     * @test
     */
    public function can_handle_deleting_events()
    {
        // Manually create a record in db
        DB::table('data')->insert(['field_1' => '']);

        $model = ModelWithEventHandlingCast::first();
        $model->delete();

        $eventsReceived = self::getEventsReceived($model);

        $this->assertContains('deleting', $eventsReceived);
        $this->assertContains('deleted', $eventsReceived);
    }

    protected static function getEventsReceived(Model $model)
    {
        $customCastObject = parent::getProtectedProperty($model, 'customCastObjects')['field_1'];

        return $customCastObject->eventsReceived;
    }
}

