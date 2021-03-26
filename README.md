# What is different about this fork
This fork does not require you to make use of the `config()` function. This is useful if you are using eloquent + custom casts without Laravel

# Laravel Custom Casts

[![Build](https://api.travis-ci.org/vkovic/laravel-custom-casts.svg?branch=master)](https://travis-ci.org/vkovic/laravel-custom-casts)
[![Downloads](https://poser.pugx.org/vkovic/laravel-custom-casts/downloads)](https://packagist.org/packages/vkovic/laravel-custom-casts)
[![Stable](https://poser.pugx.org/vkovic/laravel-custom-casts/v/stable)](https://packagist.org/packages/vkovic/laravel-custom-casts)
[![License](https://poser.pugx.org/vkovic/laravel-custom-casts/license)](https://packagist.org/packages/vkovic/laravel-custom-casts)

### Make your own cast type for Laravel model attributes

Laravel custom casts works similarly to [Eloquent attribute casting](https://laravel.com/docs/6.x/eloquent-mutators#attribute-casting), but with custom-defined logic (in a separate class). This means we can use the same casting logic across multiple models — we might write [image upload logic](https://github.com/vkovic/laravel-custom-casts/tree/v1.0.2#example-casting-user-image) and use it everywhere. In addition to casting to custom types, this package allows custom casts to listen and react to underlying model events.

Let's review some Laravel common cast types and examples of their usage:

```php
namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $casts = [
        'is_admin' => 'boolean',
        'login_count' => 'integer'
        'height' => 'decimal:2'
    ];
}
```

In addition to `boolean`, `integer`, and `decimal`, out of the box Laravel supports `real`, `float`, `double`, `string`, `object`, `array`, `collection`, `date`, `datetime`, and `timestamp` casts.

Sometimes it is convenient to handle more complex types with custom logic, and for casts to be able to listen and react to model events. This is where this package come in handy.

>Handling events directly from custom casts can be very useful if, for example, we're storing an image using a custom casts and we need to delete it when the model is deleted. *Check out the [old documentation](https://github.com/vkovic/laravel-custom-casts/tree/v1.0.2#example-casting-user-image) for this example.*


### :package: vkovic packages :package:

Please check out my other packages — they are all free, well-written, and some of them are useful :smile:. If you find something interesting, consider giving me a hand with package development, suggesting an idea or some kind of improvement, starring the repo if you like it, or simply check out the code - there's a lot of useful stuff under the hood.

- [**vkovic/laravel-commando**](http://bit.ly/2GT7DV7) ~ Collection of useful `artisan` commands
- *Coming soon* [**vkovic/laravel-event-log**](http://bit.ly/2MFtCn8) ~ Easily log and access logged events, optionally with additional data and the related model

## Compatibility

The package is compatible with **Laravel** versions `5.5`, `5.6`, `5.7`, `5.8` and `6`

and **Lumen** versions `5.5`, `5.6`, `5.7`, `5.8`.

Minimum supported version of PHP is `7.1`. 
PHP `8` is also supported.

## Installation

Install the package via Composer:

```bash
composer require vkovic/laravel-custom-casts
```

## Usage

### Utilizing a custom cast class

To enable custom casts in a model, use the `HasCustomCasts` trait and define which attributes will be casted using `$casts` - per Laravel standards.

```php
// File: app/User.php

namespace App;

use App\CustomCasts\NameCast;
use Illuminate\Database\Eloquent\Model;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class User extends Model
{
    use HasCustomCasts;

    protected $casts = [
        'is_admin' => 'boolean', // <-- Laravel default cast type
        'name' => NameCast::class // <-- Our custom cast class (see the section below)
    ];
}
```

### Defining a custom cast class

This class will be responsible for our custom casting logic.

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

The required `setAttribute` method receives the `$value` being set on the model field, and should return a raw value to store in the database.

The optional `castAttribute` method receives the raw `$value` from the database, and should return a mutated value. If this method is omitted, the raw database value will be returned.

For the sake of this example we'll implement one more method which will attach a random title to a user when their name is retrieved from database.

### Testing a custom cast class

Let's create a user and see what happens.

```php
$user = new App\User;
$user->name = 'john doe';

$user->save();
```

This will create our new user and store their name in the database, with the first letter of each word uppercased.

When we retrieve the user and try to access their name, title will be prepended to it — just like we defined in our custom `NameCast` class.

```php
dd($user->name); // 'Mr. John Doe'
```

### Handling model events

Let's say that we want to notify our administrator when a user's name changes.

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

In addition to the `updated` method, we can define other methods for standard model events:
`retrieved`, `creating`, `created`, `updating`, `saving`, `saved`, `deleting`, `deleted`, `restoring` and `restored`.

### Other functionality

As you can see from the above code, we can easily access the casted attribute name as well as an instance of the underlying model.

```php
// File: app/CustomCasts/NameCast.php

// Get the name of the model attribute being casted
dd($this->attribute); // 'name'

// Access our `User` model
dd(get_class($this->model)); // 'App/User'
```

We can also retrieve all casted attributes and their corresponding classes directly from the model.

```php
// File: app/User.php

dd($this->getCustomCasts()); // ['name' => 'App/CustomCasts/NameCast']
```

### Using aliased casts

You may find it easier to use aliases for custom casts, e.g.:

```php
protected $casts = [
    'avatar' => 'image' // <-- You prefer this ...
    // ---
    'avatar' => ImageCast::class // <-- ... over this
];
```

To make the magic happen, first add the package's service provider to the `providers` array:

```php
// File: config/app.php

'providers' => [
    // ...

    /*
     * Package Service Providers...
     */
    Vkovic\LaravelCustomCasts\CustomCastsServiceProvider::class

    // ...
]
```

Once the provider is added, publish the config file which will be used to associate aliases with their corresponding custom cast classes:

```bash
php artisan vendor:publish --provider="Vkovic\LaravelCustomCasts\CustomCastsServiceProvider"
```

This command should create a config file located at `config/custom_casts.php`. Open it up and check out the comments for examples of config options.

> #### More examples
> You can find more examples in the [old documentation](https://github.com/vkovic/laravel-custom-casts/tree/v1.0.2#example-casting-user-image).

## Contributing

If you plan to modify this Laravel package you should run the tests that come with it.
The easiest way to accomplish this is with `Docker`, `docker-compose`, and `phpunit`.

First, initialize the Docker containers:

```bash
docker-compose up -d
```

Then you can run the tests and watch the output:

```bash
docker-compose exec app vendor/bin/phpunit
```
