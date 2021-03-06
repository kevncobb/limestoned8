<?php

/**
 * @file
 * Hooks provided by the Menu Position module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allow a rule to be altered after it is evaluated but before action is taken.
 *
 * @param object $rule
 *   The rule that was just evaulated.
 * @param array $context
 *   A small context variable used by the menu_position module.
 * @param bool $rule_matches
 *   Whether we have a matching rule or not.
 * @param bool $set_breadcrumb
 *   Whether the breadcrumb still needs to be set or not.
 */
function hook_menu_position_rule_alter(&$rule, array &$context, &$rule_matches, &$set_breadcrumb) {
  // Disable the rule if we're looking at a node with a certain id.
  if ($context['node']->nid == 119) {
    $rule_matches = FALSE;
  }
}

/**
 * Registers rule plugins with menu_position module.
 *
 * Modules implementing menu position rule plugins should return an associative
 * array of data. Each key in the array should be the name of plugin implemented
 * by the module. Each key's value should be an associative array with the
 * following optional data:
 * - form_callback: (optional) The name of the function to use to add a
 *   configurable form elements to the rule definition form. Defaults to
 *   "MODULE_menu_position_rule_PLUGIN_form".
 * - condition_callback: (optional) The name of the function to use when the
 *   rule is being tested. Defaults to "MODULE_menu_position_condition_PLUGIN".
 * - file: (optional) The path, relative to the .module file, to an include file
 *   containing the form and condition callback function definitions. The
 *   hook_menu_position_rule_plugins() implementation must be in a .module file,
 *   but the include file will only be loaded if a rule is configured to use the
 *   plugin's condition.
 *
 * @return array
 *   An associative array containing the information about each plugin.
 */
function hook_menu_position_rule_plugins() {
  return [
    // Register the "foo" plugin.
    'foo' => [
      'form_callback' => 'my_module_menu_position_rule_foo_form',
      'condition_callback' => 'my_module_menu_position_condition_foo',
      'file' => 'my_module.foo.inc',
    ],
    // Register the "bar" plugin.
    // Use the defaults for all options.
    'bar' => [],
  ];
}

/**
 * @} End of "addtogroup hooks".
 */
