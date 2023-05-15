<?php

namespace Drupal\announcement\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
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
   * Configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Configuration factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, CacheTagsInvalidatorInterface $cache_tags_invalidator, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity_type.manager'), $container->get('cache_tags.invalidator'), $container->get('config.factory'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $config = $this->configFactory->get('announcement.settings');
    $cache_tags = [$config->get('cache_tags')];

    $announcementStorage = $this->entityTypeManager->getStorage('announcement');
    $query = $announcementStorage->getQuery();
    $query->condition('status', TRUE);
    $ids = $query->accessCheck(FALSE)->execute();

    $entities = $announcementStorage->loadMultiple($ids);

    $build['announcements'] = $this->entityTypeManager->getViewBuilder('announcement')->viewMultiple($entities);

    foreach ($entities as $entity) {
      $cache_tags = array_merge($cache_tags, $entity->getCacheTags());
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
    $this->cacheTagsInvalidator->invalidateTags($cache_tags);
    $build['#cache']['tags'] = CacheTagsInvalidatorInterface::mergeTags($build['#cache']['tags'], $cache_tags);
  }
}
