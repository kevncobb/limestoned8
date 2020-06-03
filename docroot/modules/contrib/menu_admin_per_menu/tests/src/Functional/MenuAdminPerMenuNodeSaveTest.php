<?php

namespace Drupal\Tests\menu_admin_per_menu\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the interaction of the node system with menu links.
 *
 * @group menu_admin_per_menu
 */
class MenuAdminPerMenuNodeSaveTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['menu_ui', 'node', 'block', 'menu_admin_per_menu'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with permission to create nodes but not administer menu.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $contentOnlyUser;

  /**
   * A user with permission to create nodes and administer menu.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $contentAndMenuUser;

  /**
   * A user with permission to access content only.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $anonymousUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType(
      [
        'type' => 'page',
        'name' => 'Basic page',
        'display_submitted' => FALSE,
      ]
    );

    $this->drupalPlaceBlock('system_menu_block:main');

    $this->contentOnlyUser = $this->drupalCreateUser(
      [
        'access content',
        'administer content types',
      ]
    );
    $this->contentAndMenuUser = $this->drupalCreateUser(
      [
        'access content',
        'administer content types',
        'administer main menu items',
      ]
    );
    $this->anonymousUser = $this->drupalCreateUser(
      [
        'access content',
      ]
    );
  }

  /**
   * Test menu re-save by users without permission.
   *
   * Tests that a menu still exists and remains existing if a user without the
   * menu permissions re-saves a node.
   */
  public function testResaveMenuLinkWithoutAccess() {
    $menu_link_title = $this->randomString();

    // Save the node with the menu.
    $this->drupalLogin($this->contentAndMenuUser);
    $edit = [
      'title[0][value]' => $this->randomString(),
      'body[0][value]' => $this->randomString(),
      'menu[enabled]' => 1,
      'menu[title]' => $menu_link_title,
    ];
    $this->drupalPostForm('node/add/page', $edit, t('Save'));

    // Ensure the menu is in place.
    $this->assertSession()->linkExists($menu_link_title);

    // Logout.
    $this->drupalLogout();

    // Save the node again as someone without permission.
    $this->drupalLogin($this->contentOnlyUser);
    $edit = [
      'title[0][value]' => $this->randomString(),
      'body[0][value]' => $this->randomString(),
    ];
    $this->drupalPostForm('node/add/page', $edit, t('Save'));

    // Ensure the menu is still in place.
    $this->assertSession()->linkExists($menu_link_title);

    // Ensure anonymous users with "access content" permission can see this
    // menu.
    $this->drupalLogout();
    $this->drupalGet('');
    $this->assertSession()->linkExists($menu_link_title);
  }

}
