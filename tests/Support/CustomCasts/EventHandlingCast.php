<?php

namespace Vkovic\LaravelCustomCasts\Test\Support\CustomCasts;

use Vkovic\LaravelCustomCasts\CustomCastBase;

class EventHandlingCast extends CustomCastBase
{
    public $eventsReceived = [];

    public function setAttribute($value)
    {
        return $value;
    }

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