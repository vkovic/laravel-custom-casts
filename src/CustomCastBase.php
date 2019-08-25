<?php

namespace Vkovic\LaravelCustomCasts;

use Illuminate\Database\Eloquent\Model;

abstract class CustomCastBase
{
    /**
     * Model
     *
     * @var Model
     */
    protected $model;

    /**
     * Corresponding db field (model attribute name)
     *
     * @var string
     */
    protected $attribute;

    public function __construct(Model $model, $attribute)
    {
        $this->model = $model;
        $this->attribute = $attribute;
    }

    /**
     * Enforce implementation in child classes
     *
     * Intercept value passed to model under specified field ($attribute)
     * and change it to our will, and/or add some logic, before it's going
     * to be saved to database
     *
     * @param mixed $value Default value passed to model attribute
     *
     * @return mixed
     */
    abstract public function setAttribute($value);

    /**
     * Cast attribute (from db value to our custom format)
     *
     * @param mixed $value Value from database field
     *
     * @return mixed|null Our customized value
     */
    public function castAttribute($value)
    {
        return $value;
    }
}