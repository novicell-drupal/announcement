<?php

namespace Drupal\announcement\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Announcements' block.
 *
 * @Block(
 *   id = "announcement_block",
 *   admin_label = @Translation("Announcements"),
 *   category = @Translation("Announcements")
 * )
 */
class AnnouncementsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Announcement entity storage class.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Cache tags invalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Constructs a new AnnouncementsBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   Announcement entity storage class.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Announcement entity view builder class.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   Cache tags invalidator service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity_type.manager'), $container->get('cache_tags.invalidator'));
  }

/**
 * {@inheritdoc}
 */
public function build() {
  $build = [];
  $cache_tags = ['announcement_list'];

  // Check if the 'announcement' entity type exists.
  if ($this->entityTypeManager->hasDefinition('announcement')) {
    $announcementStorage = $this->entityTypeManager->getStorage('announcement');

    // Query and load the entities only if the entity type exists.
    $ids = $announcementStorage->getQuery()->condition('status', TRUE)->execute();
    $entities = $announcementStorage->loadMultiple($ids);

    // Check if entities are found.
    if (!empty($entities)) {
      $build['announcements'] = $this->entityTypeManager->getViewBuilder('announcement')->viewMultiple($entities, 'full');

      foreach ($entities as $entity) {
        $cache_tags = array_merge($cache_tags, $entity->getCacheTags());
      }
    }
  }

  $this->addCacheableDependency($build, $cache_tags);

  return $build;
}

  /**
   * Adds cacheable dependency for the block.
   *
   * @param array $build
   *   The render array build.
   * @param array $cache_tags
   *   The cache tags array.
   */
  protected function addCacheableDependency(array &$build, array $cache_tags) {
    $build['#cache']['tags'] = CacheTagsInvalidatorInterface::mergeTags($build['#cache']['tags'], $cache_tags);
  }

}
