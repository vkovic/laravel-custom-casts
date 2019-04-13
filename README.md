# Laravel Custom Casts

[![Build](https://api.travis-ci.org/vkovic/laravel-custom-casts.svg?branch=master)](https://travis-ci.org/vkovic/laravel-custom-casts)
[![Downloads](https://poser.pugx.org/vkovic/laravel-custom-casts/downloads)](https://packagist.org/packages/vkovic/laravel-custom-casts)
[![Stable](https://poser.pugx.org/vkovic/laravel-custom-casts/v/stable)](https://packagist.org/packages/vkovic/laravel-custom-casts)
[![License](https://poser.pugx.org/vkovic/laravel-custom-casts/license)](https://packagist.org/packages/vkovic/laravel-custom-casts)

### Make your own custom cast type for Laravel model attributes

If we want to customize how data is mutated to be stored in database, retrieved from it and maybe perform some other logic along, we can use [Laravel default models accessors and mutators](https://laravel.com/docs/5.8/eloquent-mutators#accessors-and-mutators).

On the other hand, if we want to define our custom class for casting specific data types or decoupling casting logic so we can use it in multiple models, custom cast package is here to help.

---

## Compatibility

The package is compatible with Laravel versions `>= 5.5`

## Installation

Install the package via composer:

```bash
composer require vkovic/laravel-custom-casts
```

## Example: Casting User Image

>Handling project images is great example because it demonstrate full power of custom casts.
We will use custom casts class setter (mutator), getter (accessor) and we'll react to some model events to
physically remove and update image when image field on the model is changed.

### What needs to be done

When saving the model:
- save image path into corresponding database field
- store image physically on the disk

When retrieving the image we need to handle:
- retrieving image path from database
- serving image placeholder when image is not set (`null` in database)

When updating model image we need to:
- save another image
- delete previous image

When deleting the model itself we should:
- remove image physically from the filesystem

All of the above can be simply handled with this package.

### Preparing the project

As a guidance for this example we'll use default Laravels user model found in `app/User.php`.
Also for fully functional example you should create image placeholder in `public` directory (e.g. `public/placeholder.jpg`).

Beside basic, predefined fields: `name`, `email` and `password`, we also want to allow user to upload his avatar.

Alter user table migration and add `image` field.

```php
// File: database/migrations/2014_10_12_000000_create_users_table.php

// ...

Schema::create('users', function (Blueprint $table) {
    $table->increments('id');
    $table->string('name');
    $table->string('email')->unique();
    $table->string('image')->nullable(); // <= add this
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});

// ...

```

To utilize custom casts, we'll need to add trait to user model, and via `$casts` property link it to our class that will handle custom image cast.

```php
// File: app/User.php

namespace App;

use App\CustomCasts\ImageCast;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class User extends Authenticatable
{
    use Notifiable, HasCustomCasts; // <= include package trait

    protected $fillable = [
        'name', 'email', 'password', 'image'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    protected $casts = [
        'image' => ImageCast::class // <= define custom cast class on model attribute
    ];

    // ...
```

Next step is to create class that'll handle casting (we already included it in above file as `ImageCast`). It must implement `setAttribute` method which will take care of
saving the image (from `UploadedFile` object, via form upload in this case) and generating image name with path - to be preserved in database.

Also we'll define `castAttribute` method which will take care of retrieving our image from database and in case there is no image, we'll return image placeholder.

```php
namespace App\CustomCasts;

use Vkovic\LaravelCustomCasts\CustomCastBase;
use Illuminate\Http\UploadedFile;

class ImageCast extends CustomCastBase
{
    // Setting the value from the model to the database field (mutator).
    // Mutating value passed to model image attribute to the database field
    // and performing image save.
    public function setAttribute($file)
    {
        // Define storage folder (relative to `storage/app` folder in Laravel project)
        // Don't forget to create it and link it to public directory
        // (see https://laravel.com/docs/5.8/filesystem#the-public-disk)
        $storageDir = 'images';

        // Generate random image name
        $filename = str_random() . '.' . $file->extension();

        // Save image to predefined folder
        $file->storeAs($storageDir, $filename);

        // This will be stored in db field
        return $storageDir . '/' . $filename;
    }

    // Getting value from the database (accessor).
    // `$value` variable will hold raw database value for the image field
    public function castAttribute($value)
    {
        // Return image placeholder if there is no image
        if ($value === null) {
            return 'images/placeholder.png';
        }

        return $value;
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
    $user = new User;

    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = bcrypt($request->password);

    // Here, we're setting model image attribute to `UploadedFile` object.
    // Actual saving will be handled in `ImageCast::setAttribute()`.
    $user->image = $request->file('image');

    $user->save();
}

// ...
```

Visit corresponding route, input basic details and attach the image.
After that, we'll have our user created and image stored.

But we should also handle deleting image when user is deleted. This can be accomplished by utilizing underlying eloquent events handling. Each time eloquent event is fired, logic will look up for public method with the same name in our custom cast class.

Possible method names are:
`retrieved`, `creating`, `created`, `updating`, `updated`, `saving`, `saved`, `deleting`, `deleted`, `restoring` and `restored`, just like model event names.

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