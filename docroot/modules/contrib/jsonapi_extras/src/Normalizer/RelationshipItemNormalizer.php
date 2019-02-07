<?php

namespace Drupal\jsonapi_extras\Normalizer;

use Drupal\jsonapi\Normalizer\NormalizerBase;
use Drupal\jsonapi\Normalizer\RelationshipItem;
use Drupal\jsonapi\Normalizer\RelationshipItemNormalizer as RelationshipItemNormalizerJsonapi;
use Drupal\jsonapi\Normalizer\Value\CacheableNormalization;
use Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface;
use Drupal\serialization\Normalizer\CacheableNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Converts the Drupal entity reference item object to a JSON:API structure.
 *
 * @internal
 */
class RelationshipItemNormalizer extends NormalizerBase implements SerializerAwareInterface, CacheableNormalizerInterface, DenormalizerInterface {

  use SerializerAwareTrait;

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = RelationshipItem::class;

  /**
   * The JSON:API base normalizer.
   *
   * @var \Drupal\jsonapi\Normalizer\RelationshipItemNormalizer
   */
  protected $subject;

  /**
   * The resource type repository for changes on the target resource type.
   *
   * @var \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface
   */
  protected $resourceTypeRepository;

  /**
   * Instantiates a RelationshipItemNormalizer object.
   *
   * @param \Drupal\jsonapi\Normalizer\RelationshipItemNormalizer $subject
   *   The decorated normalizer.
   * @param \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface $resource_type_repository
   *   The repository.
   */
  public function __construct(RelationshipItemNormalizerJsonapi $subject, ResourceTypeRepositoryInterface $resource_type_repository) {
    $this->subject = $subject;
    $this->resourceTypeRepository = $resource_type_repository;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($relationship_item, $format = NULL, array $context = []) {
    $normalized_output = $this->subject->normalize($relationship_item, $format, $context);
    /** @var \Drupal\jsonapi_extras\ResourceType\ConfigurableResourceType $resource_type */
    $resource_type = $context['resource_type'];
    $enhancer = $resource_type->getFieldEnhancer($relationship_item->getParent()->getPropertyName());
    if (!$enhancer) {
      return $normalized_output;
    }
    // Apply any enhancements necessary.
    $transformed = $enhancer->undoTransform($normalized_output->getNormalization());
    $target_id = $transformed['id'];
    // @TODO: Enhancers should utilize CacheableNormalization to infer additional cacheability from the enhancer.
    return new CacheableNormalization($normalized_output, ['target_uuid' => $target_id, 'meta' => $transformed['meta']]);
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    return $this->subject->denormalize($data, $class, $format, $context);
  }

}
