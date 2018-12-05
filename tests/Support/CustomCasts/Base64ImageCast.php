<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\CustomCasts;

use Vkovic\LaravelCustomCasts\CustomCastBase;

class Base64ImageCast extends CustomCastBase
{
    /**
     * This array will be filled with all events called on related model
     *
     * Used for testing purposes only
     *
     * @var array
     */
    public $eventsReceived = [];

    /**
     * @param mixed $value
     *
     * @return string
     *
     * @throws \Exception
     */
    public function setAttribute($value)
    {
        // Quickly determine if passed value is base 64 encoded string
        if (starts_with($value, 'data:image')) {
            // For testing purposes, we'll extract image content and name
            // from example base 64 string
            $name = explode(';', $value)[1];

            // In a real app we should store image somewhere,
            // so we could use something like:
            //
            // ...
            //
            // file_put_contents(__DIR__ . '/' . $name, base64_decode($value));
            //
            // ...
            //

            // We'll return name so it can be stored in model "image" field
            // for later physical image retrieval
            return $name;
        } else {
            // We expects only base 64 encoded strings
            throw new \Exception('Image needs to be base64 encoded string');
        }
    }

    /**
     * For some reason __call is not working so dedicate method to each model event
     */

    public function retrieved()
    {
        $this->eventsReceived[] = 'retrieved';
    }

    public function creating()
    {
        $this->eventsReceived[] = 'creating';
    }

    public function created()
    {
        $this->eventsReceived[] = 'created';
    }

    public function updating()
    {
        $this->eventsReceived[] = 'updating';
    }

    public function updated()
    {
        $this->eventsReceived[] = 'updated';
    }

    public function saving()
    {
        $this->eventsReceived[] = 'saving';
    }

    public function saved()
    {
        $this->eventsReceived[] = 'saved';
    }

    public function deleting()
    {
        $this->eventsReceived[] = 'deleting';
    }

    public function deleted()
    {
        $this->eventsReceived[] = 'deleted';
    }

    public function restoring()
    {
        $this->eventsReceived[] = 'restoring';
    }

    public function restored()
    {
        $this->eventsReceived[] = 'restored';
    }
}