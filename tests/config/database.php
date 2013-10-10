<?php

// Test against in-memory SQLite DB
return array(
  'driver'    => 'sqlite',
  'database'  => ':memory:',
  'prefix'    => ''
);

// // Test against local Postgres
// return array(
//   'driver'   => 'pgsql',
//   'host'     => 'localhost',
//   'database' => 'baum_test',
//   'username' => 'postgres',
//   'password' => 'postgres',
//   'charset'  => 'utf8',
//   'prefix'   => '',
//   'schema'   => 'public',
// );

// // Test against local MySQL
// return array(
//   'driver'   => 'mysql',
//   'host'     => 'localhost',
//   'database' => 'baum_test',
//   'username' => 'mysql',
//   'password' => 'mysql',
//   'charset'   => 'utf8',
//   'collation' => 'utf8_unicode_ci',
//   'prefix'    => '',
// );
