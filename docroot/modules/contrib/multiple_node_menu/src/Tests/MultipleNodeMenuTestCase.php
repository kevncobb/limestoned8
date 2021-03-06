<?php

namespace Drupal\multiple_node_menu\Tests;

/**
 * Test adding, editing and deleting multiple menu links attachd to nodes.
 *
 * @group multiple_node_menu
 */

class MultipleNodeMenuTestCase extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  protected $admin_user;

  public static function getInfo() {
    return [
      'name' => 'Multiple Node Menu',
      'description' => 'Test adding, editing and deleting multiple menu links attachd to nodes.',
      'group' => 'Multiple Node Menu',
    ];
  }

  public function setUp() {
    parent::setUp('menu', 'multiple_node_menu');
    $this->admin_user = $this->drupalCreateUser([
      'access administration pages',
      'administer content types',
      'administer menu',
      'create page content',
      'edit any page content',
      'delete any page content',
    ]);
    $this->drupalLogin($this->admin_user);
  }

  public /**
   * Test creating, editing, deleting menu links via node form widget.
   */
  function testNodeFormWidget() {
    // Enable Navigation menu as available menu.
    $edit = [
      'menu_options[navigation]' => 1
      ];
    $this->drupalPost('admin/structure/types/manage/page', $edit, t('Save content type'));

    // Change default parent item to Navigation menu, so we can assert more
    // easily.
    $edit = [
      'menu_parent' => 'navigation:0'
      ];
    $this->drupalPost('admin/structure/types/manage/page', $edit, t('Save content type'));

    // Create a node.
    $node_title = $this->randomName();
    $edit = [
      'title' => $node_title,
      'body[' . \Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED . '][0][value]' => $this->randomString(),
      'multiple_node_menu[enabled]' => TRUE,
      'multiple_node_menu[add_link][link_title]' => $node_title,
      'multiple_node_menu[add_link][weight]' => 5,
    ];
    $this->drupalPost('node/add/page', $edit, t('Save'));
    $node = $this->drupalGetNodeByTitle($node_title);

    // Assert that the link exists.
    $this->drupalGet('');
    $this->assertLink($node_title, 0, t('Menu link is present.'));

    // Check if weight was set correctly.
    $this->drupalGet('node/' . $node->nid . '/edit');
    $this->assertOptionSelected('edit-multiple-node-menu-current-links-0-weight', 5, t('Menu weight correct in edit form'));

    // Add another item via Ajax.
    $new_link_title = $this->randomName();
    $edit = [
      'multiple_node_menu[add_link][link_title]' => $new_link_title,
      'multiple_node_menu[add_link][weight]' => -5,
    ];
    $this->drupalPostAjax(NULL, $edit, ['op' => t('Add new menu link')]);
    $this->drupalPost(NULL, [], t('Save'));

    // Assert that the new link exists.
    $this->drupalGet('');
    $this->assertLink($new_link_title, 0, t('Menu link is present.'));

    // Disable first menu item.
    $edit = [
      'multiple_node_menu[current_links][0][enabled]' => FALSE
      ];
    $this->drupalPost('node/' . $node->nid . '/edit', $edit, t('Save'));

    // Assert that the first link has been hidden.
    $this->drupalGet('');
    $this->assertNoLink($node_title, t('Menu link was disabled.'));

    // Edit the node and remove the menu link.
    $this->drupalGet('node/' . $node->nid . '/edit');
    $this->drupalPostAjax(NULL, [], ['remove_1' => t('Delete')]);
    $this->drupalPost(NULL, [], t('Save'));

    // Assert that there are no links to display for the node.
    $this->drupalGet('');
    $this->assertNoLink($node_title, t('No enabled menu links.'));
    $this->assertNoLink($new_link_title, t('No enabled menu links.'));

    // Edit the node and re-enable the menu link.
    $edit = [
      'multiple_node_menu[enabled]' => TRUE,
      'multiple_node_menu[current_links][0][enabled]' => TRUE,
    ];
    $this->drupalPost('node/' . $node->nid . '/edit', $edit, t('Save'));

    // Assert that the link exists.
    $this->drupalGet('');
    $this->assertLink($node_title, 0, t('Menu link is present.'));

    // Test disabling menu links when the 'Provide menu link' checkbox is
    // unchecked.
    $edit = [
      'multiple_node_menu[enabled]' => FALSE
      ];
    $this->drupalGet('');
    $this->drupalPost('node/' . $node->nid . '/edit', $edit, t('Save'));

    // Assert that there are no links to display for the node.
    $this->drupalGet('');
    $this->assertNoLink($node_title, t('No enabled menu links.'));
  }

}
