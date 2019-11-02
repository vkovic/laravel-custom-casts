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
        // Enable custom cast classes to listen to model events
        self::registerModelEvent('*', static function ($event, $data) {
            $eventName = explode('.', explode(':', $event)[0])[1];

            /** @var self $model */
            [$model] = $data;

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
     * @return mixed
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