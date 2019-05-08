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

/*
|--------------------------------------------------------------------------
| Initialize the Eloquent database manager
|--------------------------------------------------------------------------
|
| Eloquent can run as a stand-alone library (outside of Laravel applications)
| with all its functionality intact. We just need to bootstrap it correctly.
|
*/

$capsule = new \Illuminate\Database\Capsule\Manager;

$capsule->addConnection(require(__DIR__.'/tests/config/database.php'));
$capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container));
$capsule->bootEloquent();

$capsule->setAsGlobal();
