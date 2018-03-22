<?php

namespace Drupal\Tests\agov\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Base class for testing aGov.
 */
abstract class AgovTestBase extends BrowserTestBase {

  /**
   * Override the installation profile to our testing profile.
   *
   * @var string
   */
  protected $profile = 'agov';

  protected static $configSchemaCheckerExclusions = [
    // Following are used to test lack of or partial schema. Where partial
    // schema is provided, that is explicitly tested in specific tests.
    'config_schema_test.noschema',
    'config_schema_test.someschema',
    'config_schema_test.schema_data_types',
    'config_schema_test.no_schema_data_types',
    // Used to test application of schema to filtering of configuration.
    'config_test.dynamic.system',
    'page_manager.page_variant.panels',
    'password_policy.password_policy.australian_government_ism_policy_strong',
    'entity_browser.browser.media_browser',
    'entity_browser.browser.media_entity_browser',
    'views.view.media_entity_browser',
    'simple_sitemap.custom',
    'simple_sitemap.settings',
  ];

}
