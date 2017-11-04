<?php

namespace Drupal\Tests\agov\Functional;

/**
 * Test access disclosure for admin pages.
 *
 * @group agov
 */
class AccessDisclosureTest extends AgovTestBase {

  /**
   * Prevent disclosure of access denied pages by returning a 404 response.
   */
  public function testAdminPage404() {
    $this->drupalGet('/admin');
    $this->assertSession()->statusCodeEquals(404);
  }

}
