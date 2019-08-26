<?php

namespace Vkovic\LaravelCustomCasts;

use Illuminate\Events\Dispatcher;

trait HasCustomCasts
{
    /**
     * Each field which is going to be custom casted
     * will have its own custom cast instance in this array
     *
     * @var array
     */
    protected $customCastObjects = [];
    
    /**
     * Caches all attribute names with custom casts for
     * improved performance.
     *
     * @var array
     */
    protected $customCasts = null;

    /**
     * Boot trait
     */
    public static function bootHasCustomCasts()
    {
        // Enable custom cast classes to listen to model events
        app(Dispatcher::class)->listen('eloquent.*: ' . get_called_class(), function ($event, $data) {
            $eventName = explode('.', explode(':', $event)[0])[1];

            /** @var self $model */
            $model = $data[0];

            foreach ($model->getCustomCasts() as $attribute => $customCastClass) {
                $customCastObject = $model->getCustomCastObject($attribute);

                if (method_exists($customCastObject, $eventName)) {
                    $customCastObject->$eventName();
                }
            }
        });
    }

    /**
     * Hook into setAttribute logic and enable our custom cast do the job.
     *
     * This method is will override method in HasAttributes trait.
     *
     * @param $attribute
     * @param $value
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function setAttribute($attribute, $value)
    {
        // Give mutator priority over custom casts
        if ($this->hasSetMutator($attribute)) {
            $method = 'set' . studly_case($attribute) . 'Attribute';

            return $this->{$method}($value);
        }

        if (array_key_exists($attribute, $this->getCustomCasts())) {
            /** @var $customCastObject CustomCastBase */
            $customCastObject = $this->getCustomCastObject($attribute);

            $this->attributes[$attribute] = $customCastObject->setAttribute($value);

            return $this;
        }

        return parent::setAttribute($attribute, $value);
    }

    /**
     * Cast attribute (from db value to our custom format)
     *
     * @param $attribute
     * @param $value
     *
     *
     * @return mixed|null
     */
    protected function castAttribute($attribute, $value)
    {
        if (array_key_exists($attribute, $this->getCustomCasts())) {
            $customCastObject = $this->getCustomCastObject($attribute);

            return $customCastObject->castAttribute($value);
        }

        return parent::castAttribute($attribute, $value);
    }

    /**
     * Lazy load custom cast object and return it
     *
     * @param $attribute
     *
     * @return CustomCastBase
     */
    protected function getCustomCastObject($attribute)
    {
        // Check if custom cast object already been set
        if (isset($this->customCastObjects[$attribute])) {
            return $this->customCastObjects[$attribute];
        }

        $customCastClass = $this->casts[$attribute];
        $customCastObject = new $customCastClass($this, $attribute);

        return $this->customCastObjects[$attribute] = $customCastObject;
    }

    /**
     * Filter valid custom casts out of Model::$casts array
     *
     * @return array - key: model attribute (field name)
     *               - value: custom cast class name
     */
    public function getCustomCasts()
    {
        if (isset($this->customCasts)) {
            return $this->customCasts;
        }
        
        $customCasts = [];
        foreach ($this->casts as $attribute => $castClass) {
            if (is_subclass_of($castClass, CustomCastBase::class)) {
                $customCasts[$attribute] = $castClass;
            }
        }

        $this->customCasts = $customCasts;
        return $customCasts;
    }
}
