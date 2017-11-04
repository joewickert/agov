<?php

namespace Drupal\Tests\agov\Functional;

/**
 * Test the media configuration is working correctly.
 *
 * @group agov
 */
class MediaIntegrationTest extends AgovTestBase {

  /**
   * The admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser([
      'access media_browser entity browser pages',
      'administer media display',
      'administer media fields',
      'administer media form display',
      'use text format text_and_media',
      'administer media',
      'create media',
      'delete any media',
      'delete media',
      'update any media',
      'update media',
      'view media',
    ]);
  }

  /**
   * Test the media integration is working.
   */
  public function testMediaIntegration() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('entity-browser/iframe/media_browser');
    $this->assertTrue(!empty($this->xpath('//input[@value="Add Existing"]')));
    $this->assertTrue(!empty($this->xpath('//input[@value="Upload Image"]')));
    $this->assertTrue(!empty($this->xpath('//input[@value="Add Video"]')));
  }

}
