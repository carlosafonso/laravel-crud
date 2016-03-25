# laravel-crud
[![Build Status](https://travis-ci.org/carlosafonso/laravel-crud.svg?branch=master)](https://travis-ci.org/carlosafonso/laravel-crud)
[![Code Climate](https://codeclimate.com/github/carlosafonso/laravel-crud/badges/gpa.svg)](https://codeclimate.com/github/carlosafonso/laravel-crud)
[![Test Coverage](https://codeclimate.com/github/carlosafonso/laravel-crud/badges/coverage.svg)](https://codeclimate.com/github/carlosafonso/laravel-crud/coverage)

Convenience layer on top of the Laravel framework for developing CRUD applications

## Description
This project aims to be a useful layer on top of the excellent Laravel framework to ease up the development of CRUD applications. It avoids developers the repeating tasks of developing CRUD logic and allows them to concentrate on the specifics of their application domains.

## Projected features
* Unobstrusive.
* Heavily customizable with hooks and overridable functions.
* Useful both for RESTful APIs (JSON) and standard HTML responses.
* Developed with good practices and design patterns in mind.

## Installation
Install this package via Composer:

```
composer require carlosafonso/laravel-crud
```

Then add `Afonso\LvCrud\Providers\LvCrudServiceProvider::class` to the list of providers in `config/app.php`.

Don't forget to publish the configuration file specific to this package:

```
php artisan vendor:publish --provider="Afonso\LvCrud\Providers\LvCrudServiceProvider"
```

This will add a new configuration file called `crud.php` inside your `config` folder.

## Usage
Have any of your controllers extend from `Afonso\LvCrud\Controllers\CrudController`:

```php
use Afonso\LvCrud\Controllers\CrudController;

class FoosController extends CrudController
{
    //
}
```

This will automatically enable the CRUD behavior.

### Default model namespace
This library assumes that all models are namespaced using the following rules:

```
Foo\Bar\Controllers\FoosController -> Foo\Bar\Models\Foo
```

If your code does not follow this convention, the default model namespace can be specified by overriding the `getModelNamespace` function in your controller.

```php
public function getModelNamespace()
{
    return 'My\\Custom\\Namespace';
}
```

### CrudModelInterface
This library expects all related models to implement `Afonso\LvCrud\Models\CrudModelInterface`. You can either do this for all models or just implement this on a base model class that you will later extend and override if necessary.

### HTML/JSON support
By default, CRUD controllers support both JSON and HTML responses. This behavior can be tuned by overriding the following functions:

```php
/*
 * In this example we're only supporting JSON responses.
 */

public function supportsJson()
{
    return true;
}

public function supportsHtml()
{
    return false;
}
```

### Read-only controllers
Read-only controllers don't allow creating, updating or deleting data.

A controller can be declared as read-only by setting the `$readOnly` flag to `true`:

```php
class MyController extends CrudController
{
    protected $readOnly = true;
}
```

Read-only controllers will return with an HTTP status of `405 Method Not Allowed` when doing POSTs, PUTs or DELETEs on the resource.

## Configuration options
The following attributes can be modified in the configuration file, `crud.php`:

### default_items_per_page
The default number of entitites per page when the URL param `page_size` is not specified.
