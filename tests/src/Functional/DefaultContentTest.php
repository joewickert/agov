<?php

namespace Drupal\Tests\agov\Functional;

/**
 * Test the default content.
 *
 * @group agov
 */
class DefaultContentTest extends AgovTestBase {

  /**
   * An array of modules to be installed.
   *
   * @var array
   */
  public static $modules = ['agov_default_content'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->adminUser = $this->createUser([], NULL, TRUE);
  }

  /**
   * Test various default content placements.
   */
  public function testDefaultContent() {
    $session = $this->assertSession();

    // Homepage.
    $this->drupalGet('<front>');

    // Assert discover aGov block.
    $session->elementContains('css', '.region-featured-top .button', 'Discover aGov');

    // Assert three promo blocks.
    $session->elementContains('css', '.block-region-promo-1 .teaser__title a', 'Integer lacinia ullamcorper tempor');
    $session->elementContains('css', '.block-region-promo-2 .teaser__title a', 'Suspendisse id elit arcu.');
    $session->elementContains('css', '.block-region-promo-3 .teaser__title a', 'Nam eget placerat sapien');

    // Assert latest updates.
    $session->elementContains('css', '.block-views-blocklatest-updates-block-1 h2', 'Latest Updates');

    // Assert Twitter feed.
    $session->elementContains('css', '.block-twitter-block h2', 'Twitter feed');

    // Assert Quicklinks.
    $session->elementContains('css', '.menu--quick-links h2', 'Quick Links');

    // Assert stay up-to-date block.
    $session->pageTextContains('Stay up to date');

    // Assert icon blocks.
    $session->elementContains('css', '.block-region-footer-1 .box__title a', 'Cras accumsan fringilla');
    $session->elementContains('css', '.block-region-footer-2 .box__title a', 'Duis id consequat ipsum');
    $session->elementContains('css', '.block-region-footer-3 .box__title a', 'Nunc pulvinar venenatis');

    // Assert footer menus.
    $session->elementContains('css', '#block-footerquicklinks h2', 'Quick Links');
    $session->elementContains('css', '#block-mainnavigation-3 h2', 'Site map');

    // Test the breadcrumbs.
    $this->drupalGet('/publications');

    /** @var \Behat\Mink\Element\NodeElement[] $breadcrumbs */
    $breadcrumbs = $this->cssSelect('.breadcrumb__list li');
    $this->assertSame('Home', $breadcrumbs[0]->getText());
    $this->assertSame('Publications', $breadcrumbs[1]->getText());

    // Assert breadcrumb on individual publication page.
    $this->drupalGet('/publications/saluto-vicis');
    $breadcrumbs = $this->cssSelect('.breadcrumb__list li');
    $this->assertSame('Home', $breadcrumbs[0]->getText());
    $this->assertSame('Publications', $breadcrumbs[1]->getText());
    $this->assertSame('Saluto Vicis', $breadcrumbs[2]->getText());

    // Assert the sub-menu only appears on the /resources page.
    $this->drupalGet('/resources');
    $session->responseContains('block-mainnavigation-2');
    $this->drupalGet('/about-us');
    $session->responseNotContains('block-mainnavigation-2');

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/content/media');
    $session->linkExists('Leopard in the snow in Gulmarg, Kashmir');
    $session->responseContains('video_thumbnails/155212414.jpg');
    $session->linkExists('Solar panels');
  }

}
