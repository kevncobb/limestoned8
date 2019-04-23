<?php

/**
 * @file
 * Update helper drush command.
 */

/**
 * Implements hook_drush_command().
 */
function update_helper_drush_command() {

  $commands['update-helper-apply-update'] = [
    'description' => 'Apply config updates.',
    'aliases' => ['uhau'],
    'arguments' => [
      'module' => 'Module name.',
      'updateName' => 'Update name.',
    ],
    'options' => [
      'force' => FALSE,
    ],
    'examples' => [
      'drush uhau <module> <updateName>' => 'Apply the update <updateName> from <module>',
      'drush uhau --force <module> <updateName>' => 'Force apply the update <updateName> from <module>',
    ],
  ];

  return $commands;
}

/**
 * Drush command implementation.
 */
function drush_update_helper_apply_update($module = "", $updateName = "") {
  if (!$module || !$updateName) {
    return drush_print(dt('Please provide module name and update number, eg: drush uhau <module> <updateName>'));
  }
  $force = drush_get_option('force', FALSE);
  $updateHelper = \Drupal::service('update_helper.updater');
  $updateHelper->executeUpdate($module, $updateName, $force);
  return $updateHelper->logger()->output();
}
