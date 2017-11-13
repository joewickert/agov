<?php

namespace Drupal\agov\Tests;

use Drupal\simpletest\InstallerTestBase;

/**
 * Test the configurable dependencies installer.
 *
 * TODO: move this to a subclass of BrowserTestBase once
 *  https://www.drupal.org/node/2907728 lands.
 *
 * @group agov
 */
abstract class ConfigurableDependenciesTest extends InstallerTestBase {

  /**
   * Override the installation profile to our testing profile.
   *
   * @var string
   */
  protected $profile = 'agov_test_profile';

  /**
   * Test we're on the configurable dependencies.
   */
  public function testInstaller() {
    // Make sure we're on the right page.
    $this->assertText('aGov Module Configuration');

    // We should have the forum module listed and checked by default.
    $this->assertText('This is Drupals core forum module.');
    $this->assertFieldChecked('edit-configurable-modules-forum');

    // We should have the contact module but that should be disabled.
    $this->assertText('Contact');
    $this->assertNoFieldChecked('edit-configurable-modules-contact');

    // Complete the installer so the modules are installed and clear the entity
    // definitions.
    \Drupal::entityManager()->clearCachedDefinitions();
    $this->drupalPostForm($this->getUrl(), ['configurable_modules[forum]' => 1], 'Save and continue');

  }

}
