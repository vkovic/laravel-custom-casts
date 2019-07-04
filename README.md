# Laravel Custom Casts

[![Build](https://api.travis-ci.org/vkovic/laravel-custom-casts.svg?branch=master)](https://travis-ci.org/vkovic/laravel-custom-casts)
[![Downloads](https://poser.pugx.org/vkovic/laravel-custom-casts/downloads)](https://packagist.org/packages/vkovic/laravel-custom-casts)
[![Stable](https://poser.pugx.org/vkovic/laravel-custom-casts/v/stable)](https://packagist.org/packages/vkovic/laravel-custom-casts)
[![License](https://poser.pugx.org/vkovic/laravel-custom-casts/license)](https://packagist.org/packages/vkovic/laravel-custom-casts)

### Make your own custom cast type for Laravel model attributes

Laravel custom casts works similarly to [Laravel default accessors and mutators](https://laravel.com/docs/5.8/eloquent-mutators#accessors-and-mutators),
but with one noticeable difference: we can take our casting/mutating logic (getters, setters) away from models and put it in separate class.

For even more convenience our custom cast classes have ability to react to underlying model events.

>For example, this could be very useful if we're storing image with custom casts and we need to delete it
>when model changes. See the [old documentation](https://github.com/vkovic/laravel-custom-casts/tree/v1.0.2#example-casting-user-image) for this examples.

### :package: New package :package:

Checkout my new package [vkovic/laravel-commando](https://github.com/vkovic/laravel-commando).

It's collection of some really handy Laravel `artisan` commands that I'm sure most of you find useful.

---

## Compatibility

The package is compatible with Laravel versions `5.5`, `5.6`, `5.7` and `5.8`.

## Installation

Install the package via composer:

```bash
composer require vkovic/laravel-custom-casts
```

## Usage

### Defining custom cast class

```php
// File: app/CustomCasts/NameCast.php

namespace App\CustomCasts;

use Vkovic\LaravelCustomCasts\CustomCastBase;

class NameCast extends CustomCastBase
{
    public function setAttribute($value)
    {
        return ucwords($value);
    }

    public function castAttribute($value)
    {
        return $this->getTitle() . ' ' . $value;
    }

    protected function getTitle()
    {
        return ['Mr.', 'Mrs.', 'Ms.', 'Miss'][rand(0, 3)];
    }
}
```

Required `setAttribute` method receives `$value` that we're setting on our model field and should return raw value that we want to store in our database.

Optional `getAttribute` method receives raw `$value` from database and should return mutated value. If we omit this method, raw value from database will be returned.

For the sake of this example we'll implement one more method which will attach random title to our users
when name is returned from database.

### Utilizing our custom casts class

To enable custom casts in our models, we need to use `HasCustomCasts` trait.
Beside that, we need to define which filed will use our custom cast class, following standard Laravel approach.

```php
namespace App;

use App\CustomCasts\NameCast;
use Illuminate\Database\Eloquent\Model;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class User extends Model
{
    use HasCustomCasts;

    protected $casts = [
        'name' => NameCast::class
    ];
}
```

Lets create example user and see what's happening.

```php
$user = new App\User;
$user->name = 'john doe';

$user->save();
```

This will create our new user and his name will be stored in the database, first letters uppercased.

When we retrieve our user and try to get his name, title will be prepended to it, just like we defined it
in `NameCast` class.

```php
dd($user->name); // 'Mr. John Doe'
```

### Handling model events

Lets say that we want to notify our administrator when user name changes.

```php
// File: app/CustomCasts/NameCast.php

public function updated()
{
    $attribute = $this->attribute;

    if($this->model->isDirty($attribute)) {
        // Notify admin about name change
    }
}
```

Beside `updated` method, we can as well create other methods for standard model events:
`retrieved`, `creating`, `created`, `updating`, `saving`, `saved`, `deleting`, `deleted`, `restoring` and `restored`.

### Other functionality

As you can assume from code above, we can easily access casted attribute name as well as instance of underlying model.

```php
// File: app/CustomCasts/NameCast.php

// Get model attribute name being casted
dd($this->attribute); // 'name'

// Access our `User` model
dd(get_class($this->model)); // 'App/User'
```

Beside this we can retrieve all casted attributes and their corresponding classes directly from our model.

```php
// File: app/User.php

dd($this->getCustomCasts()); // ['name' => 'App/CustomCasts/NameCast']
```

### More examples

You can find more examples on the [old documentation](https://github.com/vkovic/laravel-custom-casts/tree/v1.0.2#example-casting-user-image).

---

## Contributing

If you plan to modify this Laravel package you should run tests that comes with it.
Easiest way to accomplish this would be with `Docker`, `docker-compose` and `phpunit`.

First, we need to initialize Docker containers:

```bash
docker-compose up -d
```

After that, we can run tests and watch the output:

```bash
docker-compose exec app vendor/bin/phpunit
```