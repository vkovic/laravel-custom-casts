# Laravel Custom Casts

[![Build](https://api.travis-ci.org/vkovic/laravel-custom-casts.svg?branch=master)](https://travis-ci.org/vkovic/laravel-custom-casts)
[![Downloads](https://poser.pugx.org/vkovic/laravel-custom-casts/downloads)](https://packagist.org/packages/vkovic/laravel-custom-casts)
[![Stable](https://poser.pugx.org/vkovic/laravel-custom-casts/v/stable)](https://packagist.org/packages/vkovic/laravel-custom-casts)
[![License](https://poser.pugx.org/vkovic/laravel-custom-casts/license)](https://packagist.org/packages/vkovic/laravel-custom-casts)

### Make your own custom cast type for Laravel model attributes

By default, from version 5 Laravel supports attribute casting. If we define `$cast` property on our model, Laravel will
help us convert defined attributes to common data types. Currently supported cast types (Laravel 5.6) are: `integer`,
`real`, `float`, `double`, `string`, `boolean`, `object`, `array`, `collection`, `date`, `datetime` and `timestamp`.

If those default cast types are not enough and you want to make your own, you'r on the right track.

---

## Compatibility

The package is compatible with Laravel versions `>= 5.5`

## Installation

Install the package via composer:

```bash
composer require vkovic/laravel-custom-casts
```

## Example: Casting User Image

When saving an image, there is two things that needs to be done:
1. Save image name (sometimes with path) into corresponding database field
2. Save image physically on the disk

As a guidance for this example we'll use default Laravel user model found in `app/User.php`.

Beside basic, predefined fields: `name`, `email` and `password`, we also want to allow user to upload his avatar. Assume
that we already have `users` table with `image` field (you should create migration for this).

To utilize custom casts, we'll need to add trait to user model, and via `$casts` property link it to the cast class.

```php
// File: app/User.php

namespace App;

use App\CustomCasts\ImageCast;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class User extends Authenticatable
{
    use Notifiable, HasCustomCasts;

    protected $fillable = [
        'name', 'email', 'password', 'image'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    protected $casts = [
        'image' => ImageCast::class
    ];
}

// ...
```

Next step is to create class that'll handle casting. It must implement `setAttribute` method which will take care of
saving the image (from UploadedFile object, via form upload in this case) and generating image name with path - to be preserved in database.

```php
// File: app/CustomCasts/ImageCast.php

namespace App\CustomCasts;

use Vkovic\LaravelCustomCasts\CustomCastBase;
use Illuminate\Http\UploadedFile;

class ImageCast extends CustomCastBase
{
    public function setAttribute($file)
    {
        // Define storage folder
        // (relative to "storage/app" folder in Laravel project)
        // Don't forget to create it !!!
        $storageDir = 'images';

        // Generate random image name
        $filename = str_random() . '.' . $file->extension();

        // Save image to predefined folder
        $file->storeAs($storageDir, $filename);

        // This will be stored in db field: "image"
        return $storageDir . '/' . $filename;
    }
}
```

Let's jump to user creation example. This will trigger our custom cast logic.

Assume that we have user controller which will handle user creation. You should create this on your
own.

> Code below is just a simple example and should be used as guidance only.

```php
// File: app/Http/Controllers/UserController.php

// ...

protected function create(Request $request)
{
    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        // Past the whole Illuminate\Http\UploadedFile object,
        // we'll handle it in our ImageCast class
        'image' => $request->file('image')
    ]);
}

// ...
```

Visit corresponding route input basic details and attach the image. After that, we'll have our user created and image
stored.

But we should also handle deleting image when user is deleted. This can be accomplished by utilizing underlying eloquent
events handling. Each time eloquent event is fired, logic will look up for public method with the same name in our custom
cast class.

Possible method names are:
`retrieved`, `creating`, `created`, `updating`, `updated`, `saving`, `saved`, `deleting`, `deleted`, `restoring` and
`restored`.

```php
// File: app/CustomCasts/ImageCast.php

// Add at the top
use Storage;

// ...

// This method will be triggered after model has been deleted
public function deleted()
{
    // We can access underlying model with $this->model
    // and attribute name that is being casted with $this->attribute

    // Retrieve image path and delete it from the disk
    $imagePath = $this->model->image;
    Storage::delete($imagePath);
}

// ...

```

This should cover basic usage of custom casts.

---

## Contributing

If you plan to modify this Laravel package you should run tests that comes with it.
Easiest way to accomplish this would be with `Docker`, `docker-compose` and `phpunit`.

First, we need to initialize Docker containers:

```php
docker-compose up -d
```

After that, we can run tests and watch the output:

```php
docker-compose exec app vendor/bin/phpunit
```