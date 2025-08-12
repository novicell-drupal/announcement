<?php

namespace Drupal\announcement\Plugin\Transform\Block;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\transform_api\Transform\EntityTransform;
use Drupal\transform_api\TransformBlockBase;
use Psr\Container\ContainerInterface;

/**
 * Provides an announcements block.
 *
 * @TransformBlock(
 *   id = "announcement",
 *   admin_label = @Translation("Announcements"),
 *   category = @Translation("Announcements"),
 * )
 */
class AnnouncementsTransformBlock extends TransformBlockBase {

  /**
   * The entity storage for announcements.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs a new AnnouncementsTransformBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->storage = $entity_type_manager->getStorage('announcement');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  public function transform() {
    $cacheMetadata = new CacheableMetadata();
    $cacheMetadata->addCacheTags(['languages', 'announcement_list']);
    $transform = ['#collapse' => TRUE];

    $ids = $this->storage->getQuery()
      ->accessCheck()
      ->condition('status', TRUE)
      ->execute();
    $entities = $this->storage->loadMultiple($ids);

    $transform['announcements'] = new EntityTransform($entities);
    foreach ($entities as $entity) {
      $cacheMetadata->addCacheableDependency($entity);
    }

    $cacheMetadata->applyTo($transform);

    return $transform;
  }

}
