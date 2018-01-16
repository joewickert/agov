<?php

namespace Drupal\Tests\agov\Kernel;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\AssertConfigTrait;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that the installed config matches the default config.
 *
 * @group agov
 */
class DefaultConfigTest extends KernelTestBase {

  use AssertConfigTrait;

  /**
   * Schema issues are caught in the aGov web tests.
   *
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  public static $modules = [
    'system',
    'user',
    'node',
    'field',
    'history',
    'block',
    'breakpoint',
    'ckeditor',
    'color',
    'config',
    'comment',
    'contextual',
    'text',
    'contact',
    'menu_link_content',
    'datetime',
    'block_content',
    'quickedit',
    'editor',
    'entity_reference',
    'help',
    'image',
    'menu_ui',
    'media',
    'options',
    'path',
    'page_cache',
    'taxonomy',
    'search',
    'shortcut',
    'toolbar',
    'field_ui',
    'file',
    'filter',
    'rdf',
    'views',
    'views_ui',
    'tour',
    'link',
    'layout_discovery',

    'media_entity_browser',
    'media_entity_image',
    'video_embed_media',
    'video_embed_field',
    'entity_embed',
    'entity_browser',
    'entity_browser_entity_form',
    'entity_browser',
    'pnx_media',
    'linky',
    'linkit',

    'ds',
    'link_attributes',
    'fences',
    'twitter_block',
    'ctools',
    'page_manager',
    'pathauto',
    'token',
    'text_icon',
  ];

  /**
   * An array of config objects not to validate.
   *
   * @var array
   */
  protected $skippedConfig = [
    // @TODO figure out what in the installer is allowing config file overrides
    // and why that doesn't work in KTB?
    'node.settings' => TRUE,
    'system.theme' => TRUE,

    // Block status is not set without a theme enabled.
    'block.block.breadcrumbs' => TRUE,
    'block.block.footerquicklinks' => TRUE,
    'block.block.mainnavigation' => TRUE,
    'block.block.mainnavigation_2' => TRUE,
    'block.block.mainnavigation_3' => TRUE,
    'block.block.pagetitle' => TRUE,
    'block.block.quicklinks' => TRUE,
    'block.block.quicklinks_2' => TRUE,
    'block.block.searchform' => TRUE,
    'block.block.searchform_2' => TRUE,
    'block.block.seven_breadcrumbs' => TRUE,
    'block.block.seven_content' => TRUE,
    'block.block.seven_help' => TRUE,
    'block.block.seven_local_actions' => TRUE,
    'block.block.seven_login' => TRUE,
    'block.block.seven_messages' => TRUE,
    'block.block.seven_primary_local_tasks' => TRUE,
    'block.block.seven_secondary_local_tasks' => TRUE,
    'block.block.sitebranding' => TRUE,
    'block.block.tabs' => TRUE,
  ];

  /**
   * Modules that we don't want to validate the config for. Should be none.
   *
   * @var array
   */
  protected $excludedModules = [
    // This causes many issues when installed with KTB so ignore it.
    'agov_default_content',
    // @TODO, move to contrib.
    'text_icon',

    // Optional modules with config are not currently supported.
    'agov_workbench',
    'agov_scheduled_updates',
    'agov_sitemap',

    // Current schema issues.
    'agov_password_policy',
  ];

  /**
   * The name of the profile.
   *
   * @var string
   */
  protected $profile = 'agov';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // @todo ModuleInstaller calls system_rebuild_module_data which is part of
    //   system.module, see https://www.drupal.org/node/2208429.
    include_once $this->root . '/core/modules/system/system.module';

    // We must set the active profile as so that we can install its default
    // config below.
    $old_settings = Settings::getAll();
    new Settings(['install_profile' => $this->profile] + $old_settings);

    // Set up the state values so we know where to find the files when running
    // drupal_get_filename().
    // @todo Remove as part of https://www.drupal.org/node/2186491
    system_rebuild_module_data();

    // Install the required schema and config for core modules.
    $this->installSchema('system', 'router');
    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('block_content');
    $this->installEntitySchema('block_content_type');
    $this->installEntitySchema('file');
    $this->installEntitySchema('media');
    $this->installConfig(self::$modules);

    // Install the default config for our installation profile.
    $this->container->get('config.installer')->installDefaultConfig('profile', $this->profile);
  }

  /**
   * Test the configuration for the profile specifically.
   */
  public function testProfileConfig() {
    $default_install_path = drupal_get_path('profile', $this->profile) . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY;
    $this->assertConfig($default_install_path);
  }

  /**
   * Tests if installed config is equal to the exported config.
   *
   * @dataProvider providerTestModuleConfig
   */
  public function testModuleConfig($module) {

    $this->container->get('module_installer')
      ->install([$module]);

    $default_install_path = drupal_get_path('module', $module) . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY;
    $this->assertConfig($default_install_path);
  }

  /**
   * Assert the configuration is the same as the installed config.
   *
   * @param string $default_install_path
   *   The full path to the install directory of the extension.
   *
   * @throws \Exception
   */
  protected function assertConfig($default_install_path) {
    $module_config_storage = new FileStorage($default_install_path, StorageInterface::DEFAULT_COLLECTION);

    // Compare the installed config with the one in the module directory.
    foreach ($module_config_storage->listAll() as $config_name) {
      $result = $this->container->get('config.manager')
        ->diff($module_config_storage, $this->container->get('config.storage'), $config_name);
      $this->assertConfigDiff($result, $config_name, $this->skippedConfig);
    }
  }

  /**
   * Test data provider for ::testModuleConfig().
   *
   * @return array
   *   An array of module names to test.
   */
  public function providerTestModuleConfig() {
    $module_dirs = array_keys(iterator_to_array(new \FilesystemIterator(__DIR__ . '/../../../modules/custom/')));
    $module_names = array_map(function ($path) {
      return str_replace(__DIR__ . '/../../../modules/custom/', '', $path);
    }, $module_dirs);

    $modules = [];
    foreach ($module_names as $module) {
      $modules[$module] = [$module];
    }
    return array_diff_key($modules, array_flip($this->excludedModules));
  }

}
