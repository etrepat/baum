<?php

/**
 * Register composer auto  loader
 */
require __DIR__.'/vendor/autoload.php';

/**
 * Initialize Capsule
 */
$capsule = new Illuminate\Database\Capsule\Manager;

$capsule->addConnection(require(__DIR__.'/tests/config/database.php'));

$capsule->setEventDispatcher(new Illuminate\Events\Dispatcher);

$capsule->bootEloquent();

$capsule->setAsGlobal();

/**
 * Manually load some required models
 */
require __DIR__.'/tests/models/Category.php';
require __DIR__.'/tests/models/ScopedCategory.php';
require __DIR__.'/tests/models/Menu.php';
require __DIR__.'/tests/models/Rank.php';
