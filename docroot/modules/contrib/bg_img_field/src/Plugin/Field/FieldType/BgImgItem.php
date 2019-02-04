<?php

namespace Drupal\bg_img_field\Plugin\Field\FieldType;


use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Plugin implementation of the 'bg_img_field' field type.
 *
 * @FieldType(
 *   id = "bg_img_field",
 *   label = @Translation("Background Image Field"),
 *   description = @Translation("Field for creating responsive background
 *   images."), default_widget = "bg_img_field_widget", default_formatter =
 *   "bg_img_field_formatter"
 * )
 */
class BgImgItem extends ImageItem {
  public static function defaultStorageSettings() {
    $settings =  parent::defaultStorageSettings();

    $settings['css_settings']['css_selector'] = '';
    $settings['css_settings']['css_repeat'] = 'inherit';
    $settings['css_settings']['css_background_size'] = 'inherit';
    $settings['css_settings']['css_background_position'] = 'inherit';

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $elements = parent::storageSettingsForm($form, $form_state, $has_data);

    // remove title and alt from the setting form, they are not need
    // in background images.
    unset($elements['default_image']['alt']);
    unset($elements['default_image']['title']);

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = parent::defaultFieldSettings();
    // change value of setting  set in image field
    $settings['file_extensions'] = "png jpg jpeg svg";
    $settings['alt_field'] = 0;
    $settings['alt_field_required'] = 0;
    // add the specific css settings.
    $settings['css_settings']['css_selector'] = '';
    $settings['css_settings']['css_repeat'] = 'inherit';
    $settings['css_settings']['css_background_size'] = 'inherit';
    $settings['css_settings']['css_background_position'] = 'inherit';

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema =  parent::schema($field_definition);

    $schema['columns']['css_selector'] = [
      'description' => "CSS selector to target the background image placement.",
      'type' => 'text',
    ];

    $schema['columns']['css_repeat'] = [
      'description' => "CSS property that determines the repeat attribute.",
      'type' => 'varchar',
      'length' => 255,
    ];

    $schema['columns']['css_background_size'] = [
      'description' => "CSS property that determines the background size attribute.",
      'type' => 'varchar',
      'length' => 255,
    ];

    $schema['columns']['css_background_position'] = [
      'description' => "CSS property that determines the background position attribute.",
      'type' => 'varchar',
      'length' => 255,
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['css_selector'] = DataDefinition::create('string')
      ->setLabel(t('CSS Selector'))
      ->setDescription(t("CSS selector that will be used to place the background image. attribute."));

    $properties['css_repeat'] = DataDefinition::create('string')
      ->setLabel(t('CSS Repeat Property'))
      ->setDescription(t("CSS attribute that set the repeating value of the background image."));

    $properties['css_background_size'] = DataDefinition::create('string')
      ->setLabel(t('CSS Background Size Property'))
      ->setDescription(t("CSS attribute that set the background size value of the background image."));

    $properties['css_background_position'] = DataDefinition::create('string')
      ->setLabel(t('CSS Background Position Property'))
      ->setDescription(t("CSS attribute that set the background position value of the background image."));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $parentElements =  parent::fieldSettingsForm($form, $form_state);
    // unset fields from image field that will not be used.
    unset($parentElements['alt_field']);
    unset($parentElements['alt_field_required']);
    unset($parentElements['title_field']);
    unset($parentElements['title_field_required']);
    //     unset to clean up the UI.
    unset($parentElements['default_image']['alt']);
    unset($parentElements['default_image']['title']);

    $elements['css_settings'] = [
      '#type' => 'details',
      '#title' => t('CSS Settings'),
      '#description' => 'Set default CSS properties for the background image.',
      '#open' =>  FALSE
    ];

    // load tokens based on the entity type it is on.
    $token_types = [$this->getFieldDefinition()->getTargetEntityTypeId()];

    // Get defined settings
    $css_option_settings = $this->getSetting('css_settings');

    // The css selector input field needed to
    $elements['css_settings']['css_selector'] = array(
      '#type'             => 'textfield',
      '#title'            => t('Selector'),
      '#description'      => t('CSS Selector for background image.'),
      '#default_value'    => $css_option_settings['css_selector'],
      '#token_types'      => $token_types,
      '#element_validate' => 'token_element_validate',
    );

    // The tokens that are scoped for the selector input.
    $elements['css_settings']['tokens'] = [
      '#theme'        => 'token_tree_link',
      '#token_types'  => $token_types,
      '#global_types' => TRUE,
      '#show_nested'  => FALSE,
    ];

    // User ability to select a background repeat option.
    $elements['css_settings']['css_repeat'] = [
      '#type' => 'radios',
      '#title' => t('Repeat Options'),
      '#description' => t('Add the css no repeat value to the background image.'),
      '#default_value' => $css_option_settings['css_repeat'],
      '#options' => [
        "inherit" => t("inherit"),
        "no-repeat" => t("no-repeat"),
        "repeat" => t('repeat'),
      ]
    ];

    // User the ability to choose background size.
    $elements['css_settings']['css_background_size'] = [
      '#type' => 'radios',
      '#title' => t('Background Size'),
      '#description' => t("Add the background size setting you would like for the image, use inherit for default."),
      '#default_value' => $css_option_settings['css_background_size'],
      '#options' => [
        'inherit' => t('inherit'),
        'auto' => t('auto'),
        'cover' => t('cover'),
        'contain' => t('contain'),
        'initial' => t('initial'),
      ]
    ];

    // User the ability to set the background position.
    $elements['css_settings']['css_background_position'] = [
      '#type' => 'radios',
      '#title' => t('Background Position'),
      '#description' => t('Set a background position, leave unchecked to have your own in your theme css.'),
      '#default_value' => $css_option_settings['css_background_position'],
      '#options' => [
        "inherit" => t("inherit"),
        "left top" => t("left top"),
        "left center" => t("left center"),
        "left bottom" => t("left bottom"),
        "right top" => t("right bottom"),
        "right center" => t("right center"),
        "right bottom" => t("right bottom"),
        "center top" => t("center top"),
        "center center" => t("center center"),
        "center bottom" => t("center bottom")
      ],
      '#tree' => TRUE,
    ];

    $elements['file_settings'] = [
      '#type' => 'details',
      '#title' => t("File Settings"),
      '#open' => FALSE,
    ] + $parentElements;

    return $elements;
  }
}