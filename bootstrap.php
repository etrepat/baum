<?php

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our library. We just need to utilize it!
|
*/

require __DIR__.'/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Initialize the Eloquent database manager
|--------------------------------------------------------------------------
|
| Eloquent can run as a stand-alone library (outside of Laravel applications)
| with all its functionality intact. We just need to bootstrap it correctly.
|
*/

$container = new \Illuminate\Container\Container;

$dispatcher = new \Illuminate\Events\Dispatcher($container);

$capsule = new \Illuminate\Database\Capsule\Manager($container);

$capsule->setEventDispatcher($dispatcher);

$capsule->addConnection(require(__DIR__.'/tests/config/database.php'));

$capsule->bootEloquent();

$capsule->setAsGlobal();

/*
|--------------------------------------------------------------------------
| Initialize the Collection extensions
|--------------------------------------------------------------------------
|
| This would normally be under a service provider on a laravel application but
| for testing we just initialize the mixin here.
|
*/

\Illuminate\Database\Eloquent\Collection::mixin(new \Baum\Mixins\Collection);

/*
|--------------------------------------------------------------------------
| Initialize the schema blueprint extensions
|--------------------------------------------------------------------------
|
| This would normally be under a service provider on a laravel application but
| for testing we just initialize the mixin here.
|
*/

\Illuminate\Database\Schema\Blueprint::mixin(new \Baum\Mixins\Blueprint);
