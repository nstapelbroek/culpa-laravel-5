# Culpa for Laravel 5 [![Build Status](https://travis-ci.org/nstapelbroek/culpa-laravel-5.svg?branch=master)](https://travis-ci.org/nstapelbroek/culpa-laravel-5) [![Latest Stable Version](https://poser.pugx.org/nstapelbroek/culpa/v/stable)](https://packagist.org/packages/nstapelbroek/culpa) [![License](https://poser.pugx.org/nstapelbroek/culpa/license)](https://packagist.org/packages/nstapelbroek/culpa) [![Dependency Status](https://www.versioneye.com/user/projects/568f8b4d691e2d00380000b5/badge.svg?style=flat)](https://www.versioneye.com/user/projects/568f8b4d691e2d00380000b5)

Blameable extension for Laravel 5 Eloquent ORM models. This extension
automatically adds references to the authenticated user when creating, updating
or soft-deleting a model.

**Disclaimer**: I've created this repository because I didn't want to include a dev-master version of [the original fork](https://github.com/nstapelbroek/culpa) in my composer.json.
Once the original [pull request](https://github.com/rmasters/culpa/pull/14) gets merged, this package will probably become deprecated and you'll be best of switching back within a year.
Due to changes in the namespace and directory structure this package is not backwards compatible with [the original Culpa for laravel < 4](https://github.com/rmasters/culpa).


## Installation

This package works with Laravel 5.1 (running PHP 5.5.9+).

To install the package in your project:

1.  Run `composer require nstapelbroek/culpa`,
2.  Add to the `providers` list in config/app.php:
    `"Culpa\CulpaServiceProvider"`,
3.  Publish the configuration to your application:
    `php artisan vendor:publish`


## Usage

You can add auditable fields on a per-model basis by adding a protected property
and a model observer. The property `$blameable` contains events you wish to
record - at present this is restricted to created, updated and deleted - which
function the same as Laravel's timestamps.

```php
use Culpa\Traits\Blameable;
use Culpa\Traits\CreatedBy;
use Culpa\Traits\DeletedBy;
use Culpa\Traits\UpdatedBy;
use Illuminate\Database\Eloquent\Model

class Comment extends Model
{
    use Blameable, CreatedBy, UpdatedBy;

    protected $blameable = array('created', 'updated', 'deleted');

    // Other model logic here
}
```

*   On create, the authenticated user will be set in `created_by`,
*   On create and update, the authenticated user will be set in `updated_by`,
*   Additionally, if the model was soft-deletable, the authenticated user will be
    set in `deleted_by`.

To activate the automatic updating of these fields, you need to add the blamable trait to the model.
That's it! Need more tweak options for Culpa? take a look at the [Tweaks and Configuration docs](docs/1. Tweaks and Configuration.md).

## License

Culpa is released under the [MIT License](LICENSE).
