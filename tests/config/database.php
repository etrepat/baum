<?php

$connections = [
    // sqlite connection
    'sqlite' => [
        'driver' => 'sqlite',
        'database' => getenv('DB_NAME') ?: ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => getenv('DB_FOREIGN_KEYS') ?: true
    ],

    // postgres connection
    'postgres' => [
        'driver' => 'pgsql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '5432',
        'database' => getenv('DB_NAME') ?: 'baum_test',
        'username' => getenv('DB_USERNAME') ?: 'postgres',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
        'sslmode' => 'prefer'
    ],

    // mysql connection
    'mysql' => [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_NAME') ?: 'baum_test',
        'username' => getenv('DB_USERNAME') ?: 'mysql',
        'password' => getenv('DB_PASSWORD') ?: '',
        'unix_socket' => getenv('DB_SOCKET') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'options' => extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA => getenv('MYSQL_ATTR_SSL_CA'),
        ]) : [],
    ],
];

return $connections[getenv('DB_CONNECTION') ?: 'sqlite'];
