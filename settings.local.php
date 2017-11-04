<?php
/**
 * @file
 * Settings for local development.
 */

$databases['default']['default'] = [
  'database' => 'local',
  'username' => 'drupal',
  'password' => 'drupal',
  'host' => 'localhost',
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
];
