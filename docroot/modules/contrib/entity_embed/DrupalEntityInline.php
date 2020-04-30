<?php

namespace Drupal\entity_embed\Plugin\CKEditorPlugin;

use Drupal\editor\Entity\Editor;

/**
 * Defines the "drupalentityinline" plugin.
 *
 * @CKEditorPlugin(
 *   id = "drupalentityinline",
 *   label = @Translation("Entity Inline"),
 *   embed_type_id = "entityinline"
 * )
 */
class DrupalEntityInline extends DrupalEntity {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'entity_embed') . '/js/plugins/drupalentityinline/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'DrupalEntityInline_dialogTitleAdd' => t('Insert entity'),
      'DrupalEntityInline_dialogTitleEdit' => t('Edit entity'),
      'DrupalEntityInline_buttons' => $this->getButtons(),
    ];
  }

}
