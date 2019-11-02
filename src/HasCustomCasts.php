<?php

namespace Vkovic\LaravelCustomCasts;

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
     * Custom casts array
     * - key: model attribute (field name)
     * - value: custom cast class name
     *
     * @var array
     */
    protected $customCasts;

    /**
     * Boot trait
     */
    public static function bootHasCustomCasts()
    {
        // When registering a custom cast trait, we will spin through the custom casts
        // attributes and possible observable events and determine if this custom cast
        // has that method. If it does, we will hook it into the model's event system,
        // making it convenient to watch these and remove per attribute if needed.
        $instance = new static;

        $observableEvents = $instance->getObservableEvents();

        foreach ($instance->getCustomCasts() as $attribute => $customCastClass) {
            $customCastObject = $instance->getCustomCastObject($attribute);

            foreach ($observableEvents as $event) {
                if (method_exists($customCastObject, $event)) {
                    self::registerListenerForAttribute($event, $attribute);
                }
            }
        }
    }

    /**
     * Registers event listener for specific custom cast attribute
     *
     * @param string $event
     * @param string $attribute
     */
    private static function registerListenerForAttribute($event, $attribute): void
    {
        static::registerModelEvent(
            $event,
            /** @param self $model */
            static function ($model) use ($attribute, $event) {
                $model->getCustomCastObject($attribute)->$event();
            }
        );
    }

    /**
     * Hook into setAttribute logic and enable our custom cast do the job.
     *
     * This method is will override method in HasAttributes trait.
     *
     * @param $attribute
     * @param $value
     *
     * @return mixed
     *
     * @see \Illuminate\Database\Eloquent\Concerns\HasAttributes::setAttribute()
     */
    public function setAttribute($attribute, $value)
    {
        // Give mutator priority over custom casts
        if ($this->hasSetMutator($attribute)) {
            return $this->setMutatedAttributeValue($attribute, $value);
        }

        if ($this->isCustomCasts($attribute)) {
            $this->attributes[$attribute] = $this->setCustomCast($attribute, $value);

            return $this;
        }

        return parent::setAttribute($attribute, $value);
    }

    /**
     * Filter valid custom casts out of Model::$casts array
     *
     * @return array - key: model attribute (field name)
     *               - value: custom cast class name
     */
    public function getCustomCasts()
    {
        if ($this->customCasts !== null) {
            return $this->customCasts;
        }

        $customCasts = [];

        foreach ($this->casts as $attribute => $type) {
            $castClass = $this->getCastClass($type);

            if (is_subclass_of($castClass, CustomCastBase::class)) {
                $customCasts[$attribute] = $castClass;
            }
        }

        $this->customCasts = $customCasts;

        return $customCasts;
    }

    /**
     * Cast attribute (from db value to our custom format)
     *
     * @param $attribute
     * @param $value
     *
     *
     * @return mixed|null
     *
     * @see \Illuminate\Database\Eloquent\Concerns\HasAttributes::castAttribute()
     */
    protected function castAttribute($attribute, $value)
    {
        if ($this->isCustomCasts($attribute)) {
            return $this->castCustomCast($attribute, $value);
        }

        return parent::castAttribute($attribute, $value);
    }

    /**
     * Cast attribute (from db value to our custom format)
     *
     * @param $attribute
     * @param $value
     *
     * @return mixed|null
     */
    protected function castCustomCast($attribute, $value)
    {
        return $this->getCustomCastObject($attribute)->castAttribute($value);
    }

    /**
     * Cast attribute (from db value to our custom format)
     *
     * @param $attribute
     * @param $value
     *
     * @return mixed|null
     */
    protected function setCustomCast($attribute, $value)
    {
        return $this->getCustomCastObject($attribute)->setAttribute($value);
    }

    /**
     * Returns true if attribute is custom cast
     *
     * @param $attribute
     * @return bool
     */
    protected function isCustomCasts($attribute): bool
    {
        return array_key_exists($attribute, $this->getCustomCasts());
    }

    /**
     * Lazy load custom cast object and return it
     *
     * @param $attribute
     *
     * @return \Vkovic\LaravelCustomCasts\CustomCastBase
     */
    protected function getCustomCastObject($attribute)
    {
        if (!isset($this->customCastObjects[$attribute])) {
            $customCastClass = $this->getCastClass($this->casts[$attribute]);
            $customCastObject = new $customCastClass($this, $attribute);

            $this->customCastObjects[$attribute] = $customCastObject;
        }

        return $this->customCastObjects[$attribute];
    }

    /**
     * Get the cast class name for the given cast type.
     * Cast type can either be FQCN of custom cast class
     * or user assigned alias defined in config.
     *
     * @param string $castType
     *
     * @return string
     */
    protected function getCastClass($castType)
    {
        return config("custom_casts.$castType", $castType);
    }
}