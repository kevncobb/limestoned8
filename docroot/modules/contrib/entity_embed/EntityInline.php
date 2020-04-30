<?php

namespace Drupal\entity_embed\Plugin\EmbedType;

/**
 * Entity Inline embed type.
 *
 * @EmbedType(
 *   id = "entityinline",
 *   label = @Translation("Entity Inline")
 * )
 */
class EntityInline extends Entity {

  /**
   * {@inheritdoc}
   */
  public function getDefaultIconUrl() {
    return file_create_url(drupal_get_path('module', 'entity_embed') . '/js/plugins/drupalentityinline/entity.png');
  }

}
