<?php

namespace Drupal\limestone_foundation_paragraphs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Exception\FieldStorageDefinitionUpdateForbiddenException;
use Drupal\Core\Entity\Schema\DynamicallyFieldableEntityStorageSchemaInterface;
use Drupal\Core\Field\FieldException;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\FieldStorageConfigInterface;

/**
 * Provides form for managing module settings.
 */
class LimestoneFoundationParagraphsSettingsForm extends ConfigFormBase {

  /**
   * Get the from ID.
   */
  public function getFormId() {
    return 'limestone_foundation_paragraphs_settings';
  }

  /**
   * Get the editable config names.
   */
  protected function getEditableConfigNames() {
    return ['limestone_foundation_paragraphs.settings'];
  }

  /**
   * Build the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('limestone_foundation_paragraphs.settings');
    $form['settings']['background_colors'] = [
      '#type' => 'textarea',
      '#default_value' => $config->get('background_colors'),
      '#title' => t('Available CSS styles (classes) for Limestone Foundation Paragraphs'),
      '#description' => t('
<p>The list of CSS classes available as background styles for Limestone Foundation Pargaraphs. Enter one value per line, in the format <b>key|label</b> where <em>key</em> is the CSS class name (without the .), and <em>label</em> is the human readable name of the style in administration forms.</p><p>These styles are defined and can be customized in <code>vbp-colors</code> library that is defined in <code>limestone_foundation_paragraphs/limestone_foundation_paragraphs.libraries.yml</code>.</p><p>To customize the styles to fit your brand with your own theme, do the following:
  <ol>
    <li>Copy the LESS (<code>limestone_foundation_paragraphs/less/theme/vbp-colors.theme.less</code>) and CSS (<code>limestone_foundation_paragraphs/css/theme/vbp-colors.theme.css</code>) files to your own theme.</li>
    <li>Override or replace the <code>vbp-colors</code> library in your own frontend theme. You will need to edit <code>YOURTHEME.libraries.yml</code> and <code>YOURTHEME.info.yml</code>. Refer to <a href="@link">the documentation manual for overriding libraries in your theme</a> for more details.</li>
    <li>Edit the LESS/CSS files in your own theme to customize the styles as you wish. You will notice that the admin form will load your styles in the available "Background color" options for Paragraphs.</li>
  </ol>
</p>', ['@link' => ' https://www.drupal.org/docs/8/theming-drupal-8/adding-stylesheets-css-and-javascript-js-to-a-drupal-8-theme#override-extend']),
      '#cols' => 60,
      '#rows' => 10,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Submit Form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    try {
      // Update the Allowed list text values.
      $newAllowedListTextValues = self::optionsExtractAllowedListTextValues($form_state->getValue('background_colors'));
      $fieldStorage = \Drupal\field\Entity\FieldStorageConfig::loadByName('paragraph', 'bp_background');
      $fieldStorage->setSetting('allowed_values', $newAllowedListTextValues);
      $fieldStorage->save();
    }
    catch (FieldStorageDefinitionUpdateForbiddenException $e) {
      drupal_set_message($e->getMessage(), 'error');
      $form_state->setRebuild();
      return;
    }
    catch (Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
      $form_state->setRebuild();
      return;
    }

    $config = $this->config('limestone_foundation_paragraphs.settings');
    $config->set('background_colors', $form_state->getValue('background_colors'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Validate Form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $values = self::optionsExtractAllowedListTextValues($form_state->getValue('background_colors'));

    if (!is_array($values)) {
      $form_state->setErrorByName('background_colors', t('Allowed values list: invalid input.'));
    }
    else {
      // Check that keys are valid for the field type.
      foreach ($values as $key => $value) {
        if (Unicode::strlen($key) > 255) {
          $form_state->setErrorByName('background_colors', t('Allowed values list: each key must be a string at most 255 characters long.'));
          break;
        }
      }
    }
  }

  /**
   * Parses a string of 'allowed values' into an array.
   *
   * @param string $string
   *   The list of allowed values in string format described in
   *   optionsExtractAllowedValues().
   *
   * @return array/null
   *   The array of extracted key/value pairs, or NULL if the string is invalid.
   *
   * @see optionsExtractAllowedListTextValues()
   */
  public function optionsExtractAllowedListTextValues($string) {
    $values = [];

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    foreach ($list as $position => $text) {
      $value = $key = FALSE;

      // Check for an explicit key.
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = trim($matches[1]);
        $value = trim($matches[2]);
        $values[$key] = $value;
      }
      else {
        return;
      }
    }

    return $values;
  }

}
