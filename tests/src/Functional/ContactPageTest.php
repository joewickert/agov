<?php

namespace Drupal\Tests\agov\Functional;

use Drupal\Core\Test\AssertMailTrait;

/**
 * Test the contact page.
 *
 * @group agov
 */
class ContactPageTest extends AgovTestBase {

  use AssertMailTrait;

  /**
   * The admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser([]);
  }

  /**
   * Test the contact page is available and working.
   */
  public function testContactPage() {
    // Anonymous users can view the contact page and get the name fields.
    $this->assertContactPage();
    $mails = $this->getMails();
    $this->assertEqual(1, count($mails));

    // Authenticated users get the form as well.
    $this->drupalLogin($this->adminUser);
    $this->assertContactPage();
    $mails = $this->getMails();
    $this->assertEqual(2, count($mails));
  }

  /**
   * Assert we're on the contact page, it's available and submissions work.
   */
  protected function assertContactPage() {
    $this->drupalGet('/contact');
    $this->assertResponse(200);
    $this->assertFieldById('edit-message-0-value');
    $this->assertNoFieldById('edit-preview', 'Preview');

    $edit = [
      'subject[0][value]' => $this->randomMachineName(),
      'message[0][value]' => $this->randomString(),
    ];
    if (!$this->loggedInUser) {
      $edit += [
        'name' => $this->randomString(),
        'mail' => $this->randomMachineName() . '@example.com',
      ];
    }
    $this->drupalPostForm($this->getUrl(), $edit, 'Send message');
    $this->assertText('Your message has been sent.');
  }

}
