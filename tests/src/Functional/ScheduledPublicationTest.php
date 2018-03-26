<?php

namespace Drupal\Tests\agov\Functional;

/**
 * Test the scheduled publications.
 *
 * @group agov
 */
class ScheduledPublicationTest extends AgovTestBase {

  /**
   * An array of modules to be installed.
   *
   * @var array
   */
  public static $modules = [
    'agov_scheduled_updates',
    'agov_workbench',
    'content_moderation',
  ];

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
    $this->adminUser = $this->drupalCreateUser([
      // Nodes.
      'administer nodes',
      'administer scheduled update types',
      'create publication content',
      'access content overview',
      'administer content types',
      'bypass node access',
      // Scheduled updates.
      'view scheduled update entities',
      'create scheduled_publications scheduled updates',
      'delete any scheduled_publications scheduled updates',
      'edit any scheduled_publications scheduled updates',
      // Moderation state.
      'administer moderation state transitions',
      'administer moderation states',
      'view any unpublished content',
      'use published_draft transition',
      'use draft_draft transition',
      'use needs_review_needs_review transition',
      'use published_published transition',
      'use needs_review_published transition',
      'use draft_published transition',
      'use draft_needs_review transition',
    ]);
  }

  /**
   * Test the scheduled publications is available and working.
   */
  public function testScheduledPublication() {
    $this->drupalLogin($this->adminUser);

    // Create an unpublished node with a future publication date.
    $this->drupalGet('node/add/publication');
    $this->assertText('Scheduled publishing');
    $this->drupalPostForm(NULL, [], 'Add new scheduled publish date');
    $this->drupalPostForm(NULL, [
      'publish_date[form][inline_entity_form][update_timestamp][0][value][date]' => '2030-01-01',
      'publish_date[form][inline_entity_form][update_timestamp][0][value][time]' => '16:59:31',
    ], 'Create scheduled publish date');
    $this->drupalPostForm(NULL, [
      'title[0][value]' => $this->randomMachineName(),
    ], 'Save and Request Review');

    $node = $this->lastCreatedEntity('node');
    $update = $this->lastCreatedEntity('scheduled_update');

    // Ensure the node is unpublished.
    $this->drupalLogout();
    $this->drupalGet($node->toUrl());
    $this->assertText('Page not found');

    // Rewind the update time, so it's in the past.
    $update->update_timestamp = REQUEST_TIME - 1500;
    $update->save();

    // Run all updates and revisit the node.
    \Drupal::service('scheduled_updates.update_runner')->runAllUpdates();
    $this->drupalGet($node->toUrl());
    $this->assertText($node->label());
  }

  /**
   * Load the last created entity of a given type.
   *
   * @param string $type
   *   The type of entity to load.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   A loaded entity.
   */
  protected function lastCreatedEntity($type) {
    $type_manager = \Drupal::entityTypeManager();
    $id_key = $type_manager->getDefinition($type)->getKey('id');
    $results = \Drupal::entityQuery($type)->sort($id_key, 'DESC')->range(0, 1)->execute();
    $id = array_shift($results);
    return $type_manager->getStorage($type)->load($id);
  }

}
