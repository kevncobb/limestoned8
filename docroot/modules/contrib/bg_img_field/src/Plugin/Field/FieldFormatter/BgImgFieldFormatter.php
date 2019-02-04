<?php

namespace Drupal\bg_img_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\responsive_image\Plugin\Field\FieldFormatter\ResponsiveImageFormatter;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\bg_img_field\Component\Render\CSSSnippet;

/**
 * Plugin implementation of the 'image' formatter.
 *
 * @FieldFormatter(
 *   id = "bg_img_field_formatter",
 *   label = @Translation("Background Image Field Widget"),
 *   field_types = {
 *     "bg_img_field"
 *   },
 *   quickedit = {
 *     "editor" = "image"
 *   }
 * )
 */
class BgImgFieldFormatter extends ResponsiveImageFormatter implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    // get the options for responsive image styles
    $options = $elements['responsive_image_style']['#options'];
    // new options array for storing new option values
    $new_options = [];
    // loop through the options to locate only the ones that are labeled
    // image styles. This will eliminate any by size styles.
    foreach ($options as $key => $option) {
      $storage = $this->responsiveImageStyleStorage->load($key);
      $image_style_mappings = $storage->get('image_style_mappings');
      if (isset($image_style_mappings[0]) && $image_style_mappings[0]['image_mapping_type']
      === 'image_style') {
        $new_options = [$key => $option];
      }
    }
    $elements['responsive_image_style']['#options'] = $new_options;
    // remove the image link element.
    unset($elements['image_link']);

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $responsive_image_style = $this->responsiveImageStyleStorage->load($this->getSetting('responsive_image_style'));
    if ($responsive_image_style) {
      $summary[] = t('Responsive image style: @responsive_image_style', ['@responsive_image_style' => $responsive_image_style->label()]);
    }
    else {
      $summary[] = t('Select a responsive image style.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $entity = $items->getEntity();

    // Load the files to render.
    $files = [];
    foreach ($items->getValue() as $item) {
      $files[] = [
        'file' => File::load($item['target_id']),
        'item' => $item
      ];
    }
    // Early opt-out if the field is empty.
    if (empty($files)) {
      return $elements;
    }

    return $this->build_element($files, $entity);
  }

  /**
   * Build the inline css style based on a set of files and a selector.
   *
   * @param $files
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The parent entity the field belongs to. Used for token replacement in the
   *   selector.
   *
   * @return array
   */
  protected function build_element($files, $entity) {
    $elements = [];
    $css = "";

    // Collect cache tags to be added for each item in the field.
    $responsive_image_style = $this->responsiveImageStyleStorage->load($this->getSetting('responsive_image_style'));
    $image_styles_to_load = [];
    $cache_tags = [];
    if ($responsive_image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $responsive_image_style->getCacheTags());
      $image_styles_to_load = $responsive_image_style->getImageStyleIds();
    }

    // get image styles
    $image_styles = $this->imageStyleStorage->loadMultiple($image_styles_to_load);
    foreach ($image_styles as $image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $image_style->getCacheTags());
    }

    // process the files to get the css markup
    foreach ($files as $file) {
      $selector = $file['item']['css_selector'];
      $selector = \Drupal::token()->replace($selector, [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);
      $css .= $this->generate_background_css(
        $file['file'],
        $responsive_image_style,
        $selector,
        $file['item']
      );

      // attach to head on element to create style tag in the html head.
      if (!empty($css)) {
        // Use the selector in the id to avoid collisions with multiple background
        // formatters on the same page.
        $id = 'picture-background-formatter-' . $selector;
        $elements['#attached']['html_head'][] = [[
          '#tag' => 'style',
          '#value' => new CSSSnippet($css),
        ], $id];
      }
    }

    return $elements;
  }
  /**
   * CSS Generator Helper Function.
   *
   * @param ImageItem $image
   *   URI of the field image.
   * @param string $responsive_image_style
   *   Desired picture mapping to generate CSS.
   * @param string $selector
   *   CSS selector to target.
   * @param array $options
   *   CSS options.
   * @return string
   *   Generated background image CSS.
   *
   */
  protected function generate_background_css($image, $responsive_image_style, $selector, $options) {
    $css = "";

    $css .= $selector . '{';
    $css .= "background-repeat: " . $options['css_repeat'] .";";
    $css .= "background-size: " . $options['css_background_size'] .";";
    $css .= "background-position: " . $options['css_background_position'] .";";
    $css .= '}';

    $breakpoints = \Drupal::service('breakpoint.manager')->getBreakpointsByGroup($responsive_image_style->getBreakpointGroup());
    foreach (array_reverse($responsive_image_style->getKeyedImageStyleMappings()) as $breakpoint_id => $multipliers) {
      if (isset($breakpoints[$breakpoint_id])) {

        $multipliers = array_reverse($multipliers);

        $query = $breakpoints[$breakpoint_id]->getMediaQuery();
        if ($query != "") {
          $css .= ' @media ' . $query . ' {';
        }

        foreach ($multipliers as $multiplier => $mapping) {
          $multiplier = rtrim($multiplier, "x");

          if($mapping['image_mapping_type'] != 'image_style') {
            continue;
          }

          if ($mapping['image_mapping'] == "_original image_") {
            $url = file_create_url($image->getFileUri());
          }
          else {
            $url = ImageStyle::load($mapping['image_mapping'])->buildUrl($image->getFileUri());
          }

          if ($multiplier != 1) {
            $css .= ' @media (-webkit-min-device-pixel-ratio: ' . $multiplier . '), (min-resolution: ' . $multiplier * 96 . 'dpi), (min-resolution: ' . $multiplier . 'dppx) {';
          }
          $css .= $selector . ' {background-image: url(' . $url . ');}';

          if ($multiplier != 1) {
            $css .= '}';
          }
        }

        if ($query != "") {
          $css .= '}';
        }
      }
    }

    return $css;
  }
}
