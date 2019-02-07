<?php

namespace Drupal\jsonapi_defaults;

use Drupal\jsonapi\Routing\JsonApiParamEnhancer;
use Drupal\jsonapi\Routing\Routes;
use Drupal\jsonapi_defaults\Controller\EntityResource;
use Drupal\jsonapi_extras\Entity\JsonapiResourceConfig;
use Drupal\jsonapi_extras\ResourceType\ConfigurableResourceType;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * JsonApiDefaultsJsonApiParamEnhancer class.
 *
 * @internal
 */
class JsonApiDefaultsJsonApiParamEnhancer extends JsonApiParamEnhancer {

  /**
   * Configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * {@inheritdoc}
   */
  public function setContainer(ContainerInterface $container = NULL) {
    parent::setContainer($container);

    $this->configManager = $container->get('config.manager');
  }

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {
    if (!Routes::isJsonApiRequest($defaults)) {
      return parent::enhance($defaults, $request);
    }
    $resource_type = Routes::getResourceTypeNameFromParameters($defaults);
    // If this is a related resource, then we need to swap to the new resource
    // type.
    $route = $defaults[RouteObjectInterface::ROUTE_OBJECT];
    $related_field = $route->getDefault('_on_relationship')
      ? NULL
      : $route->getDefault('related');
    try {
      $resource_type = EntityResource::correctResourceTypeOnRelated($related_field, $resource_type);
    }
    catch (\LengthException $e) {
      watchdog_exception('jsonapi_defaults', $e);
      $resource_type = NULL;
    }

    if (!$resource_type instanceof ConfigurableResourceType) {
      return parent::enhance($defaults, $request);
    }
    $resource_config = $resource_type->getJsonapiResourceConfig();
    if (!$resource_config instanceof JsonapiResourceConfig) {
      return parent::enhance($defaults, $request);
    }
    $default_filter_input = $resource_config->getThirdPartySetting(
      'jsonapi_defaults',
      'default_filter',
      []
    );

    $default_filter = [];
    foreach ($default_filter_input as $key => $value) {
      if (substr($key, 0, 6) === 'filter') {
        $key = str_replace('filter:', '', $key);
        // TODO: Replace this with use of the NestedArray utility.
        $this->setFilterValue($default_filter, $key, $value);
      }
    }
    $filters = array_merge(
      $default_filter,
      $request->query->get('filter', [])
    );

    if (!empty($filters)) {
      $request->query->set('filter', $filters);
    }

    return parent::enhance($defaults, $request);
  }

  /**
   * Set filter into nested array.
   *
   * @param array $arr
   *   The default filter.
   * @param string $path
   *   The filter path.
   * @param mixed $value
   *   The filter value.
   */
  private function setFilterValue(array &$arr, $path, $value) {
    $keys = explode('#', $path);

    foreach ($keys as $key) {
      $arr = &$arr[$key];
    }

    $arr = $value;
  }

}
