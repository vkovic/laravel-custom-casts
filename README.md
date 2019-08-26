# Laravel Custom Casts

[![Build](https://api.travis-ci.org/vkovic/laravel-custom-casts.svg?branch=master)](https://travis-ci.org/vkovic/laravel-custom-casts)
[![Downloads](https://poser.pugx.org/vkovic/laravel-custom-casts/downloads)](https://packagist.org/packages/vkovic/laravel-custom-casts)
[![Stable](https://poser.pugx.org/vkovic/laravel-custom-casts/v/stable)](https://packagist.org/packages/vkovic/laravel-custom-casts)
[![License](https://poser.pugx.org/vkovic/laravel-custom-casts/license)](https://packagist.org/packages/vkovic/laravel-custom-casts)

### Make your own cast type for Laravel model attributes

Laravel custom casts works similarly to [Laravel attribute casting](https://laravel.com/docs/5.8/eloquent-mutators#attribute-casting), but with our customly defined logic (in separated class). This means that we can use the same casting logic across our models - we might write [image upload logic](https://github.com/vkovic/laravel-custom-casts/tree/v1.0.2#example-casting-user-image) and use it everywhere. Beside casting to our custom types this package gives us ability to listen and react to underlying model events.

Let's check out some Laravel common cast types and possible example of their usage:

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

Beside `bolean`, `integer` and `decimal` from the example above, out of the box Laravel supports `real`, `float`, `double`, `string`, `object`, `array`, `collection`, `date`, `datetime`, and `timestamp` casts.

Sometimes it is convenient to handle more complex types with custom logic and ability to listen and react to model events. This is where this package come in handy.

>Handling events directly from custom casts could be very useful if we're, for e.g. storing image with custom casts and we need to delete it when the model gets deleted. *Checkout the [old documentation](https://github.com/vkovic/laravel-custom-casts/tree/v1.0.2#example-casting-user-image) for this example.*

---

### :package: vkovic packages :package:

Please checkout my other packages - they are all free, well written and some of them are useful :smile:. If you find something interesting you might give me a hand for further package development, suggest an idea or some kind of improvement, star the repo if you like it or simply check out the code - there's a lot of useful stuff under the hood.

- [**vkovic/laravel-commando**](http://bit.ly/2GT7DV7) ~ Collection of useful `artisan` commands
- *Coming soon* [**vkovic/laravel-event-log**](http://bit.ly/2MFtCn8) ~ Easily log and access logged events, optionally with additional data and related model

## Compatibility

The package is compatible with **Laravel** and **Lumen** versions `5.5`, `5.6`, `5.7` and `5.8`.

## Installation

Install the package via composer:

```bash
composer require vkovic/laravel-custom-casts
```

## Usage

### Utilizing a custom casts class

To enable custom casts in our models, we need to use `HasCustomCasts` trait and we need to define which filed will be casted - per Laravel standards.

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
        'is_admin' => boolean // <-- Laravel default cast type
        'name' => NameCast::class // <-- Our custom cast class (follow section below)
    ];
}
```

### Defining custom cast class

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

Required `setAttribute` method receives `$value` that we're setting on our model field and should return raw value that we want to store in our database.

Optional `getAttribute` method receives raw `$value` from database and should return mutated value. If we omit this method, raw value from database will be returned.

For the sake of this example we'll implement one more method which will attach random title to our users
when name is returned from database.

### Let's test it

Let's create a user and see what will happen.

```php
$user = new App\User;
$user->name = 'john doe';

$user->save();
```

This will create our new user and his name will be stored in the database, first letters uppercased.

When we retrieve our user and try to get his name, title will be prepended to it, just like we defined it
in our custom `NameCast` class.

```php
dd($user->name); // 'Mr. John Doe'
```

### Handling model events

Let's say that we want to notify our administrator when user name changes.

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
