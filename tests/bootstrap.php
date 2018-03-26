<?php

/**
 * @file
 * Boostrap for PHPUnit.
 */

use Drupal\Component\Assertion\Handle;

assert_options(ASSERT_ACTIVE, FALSE);

$autoloader = __DIR__ . '/../vendor/autoload.php';
$loader = require $autoloader;

if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
  define('PHPUNIT_COMPOSER_INSTALL', $autoloader);
}

// Start with classes in known locations.
$loader->add('Drupal\\Tests', __DIR__ . '/../app/core/tests');
$loader->add('Drupal\\KernelTests', __DIR__ . '/../app/core/tests');
$loader->add('Drupal\\FunctionalTests', __DIR__ . '/../app/core/tests');
$loader->add('Drupal\\FunctionalJavascriptTests', __DIR__ . '/../app/core/tests');
class_exists('phpDocumentor\Reflection\DocBlockFactory');

if (!isset($GLOBALS['namespaces'])) {
  // Scan for arbitrary extension namespaces from core and contrib.
  $root = __DIR__ . '/../app/';
  $extension_roots = [
    'modules/custom/',
    $root . 'profiles/',
    $root . 'modules/contrib/',
    $root . 'core/modules',
  ];

  $dirs = array_map(function ($scan_directory) {
    $extensions = [];
    $dirs = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($scan_directory, \RecursiveDirectoryIterator::FOLLOW_SYMLINKS));
    foreach ($dirs as $dir) {
      if (strpos($dir->getPathname(), '.info.yml') !== FALSE) {
        // Cut off ".info.yml" from the filename for use as the extension name.
        // We use getRealPath() so that we can scan extensions represented by
        // directory aliases.
        $extensions[substr($dir->getFilename(), 0, -9)] = $dir->getPathInfo()
          ->getRealPath();
      }
    }
    return $extensions;

  }, $extension_roots);
  $dirs = array_reduce($dirs, 'array_merge', []);
  $namespaces = [];
  foreach ($dirs as $extension => $dir) {
    if (is_dir($dir . '/src')) {
      // Register the PSR-4 directory for module-provided classes.
      $namespaces['Drupal\\' . $extension . '\\'][] = $dir . '/src';
    }
    if (is_dir($dir . '/tests/src')) {
      // Register the PSR-4 directory for PHPUnit test classes.
      $namespaces['Drupal\\Tests\\' . $extension . '\\'][] = $dir . '/tests/src';
    }
  }
  $GLOBALS['namespaces'] = $namespaces;
}
foreach ($GLOBALS['namespaces'] as $prefix => $paths) {
  $loader->addPsr4($prefix, $paths);
}

date_default_timezone_set('Australia/Sydney');
Handle::register();
