<?php

namespace Drupal\slick\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * A Trait common for all blazy formatters.
 */
trait SlickFormatterViewTrait {

  /**
   * Returns similar view elements.
   */
  public function commonViewElements(FieldItemListInterface $items, $langcode, array $entities = [], array $settings = []) {
    // Early opt-out if the field is empty.
    if ($items->isEmpty()) {
      return [];
    }

    // Collects specific settings to this formatter.
    $settings = array_merge($this->buildSettings(), $settings);
    $settings['langcode'] = $langcode;

    // Build the settings.
    $build = ['settings' => $settings];

    // Modifies settings before building elements.
    $entities = empty($entities) ? [] : array_values($entities);
    $this->formatter->preBuildElements($build, $items, $entities);

    // Build the elements.
    $elements = $entities ?: $items;
    $this->buildElements($build, $elements, $langcode);

    // Modifies settings post building elements.
    $this->formatter->postBuildElements($build, $items, $entities);

    // Pass to manager for easy updates to all Blazy formatters.
    return $this->manager->build($build);
  }

}
