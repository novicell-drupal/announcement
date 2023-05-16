<?php

namespace Drupal\announcement\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Configuration factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity_type.manager'), $container->get('config.factory'));
  }

  /**
   * Build the content of the block.
   *
   * @return array
   *   A renderable array.
   *
   * @throws \Exception
   *   Thrown if an invalid cache tag is detected.
   */
  public function build() {
    $build = [];
    $config = $this->configFactory->get('announcement.settings');
    $cache_tags = (array) $config->get('cache_tags');

    $announcementStorage = $this->entityTypeManager->getStorage('announcement');
    $query = $announcementStorage->getQuery();
    $query->condition('status', TRUE);
    $ids = $query->execute();

    if (empty($ids)) {
      return $build;
    }

    $entities = $announcementStorage->loadMultiple($ids);
    $build['announcements'] = $this->entityTypeManager->getViewBuilder('announcement')->viewMultiple($entities);

    foreach ($entities as $entity) {
      $entityCacheTags = $entity->getCacheTags();
      foreach ($entityCacheTags as $tag) {
        if (!is_string($tag)) {
          throw new \Exception('Invalid cache tag: ' . var_export($tag, TRUE));
        }
      }
      $cache_tags = Cache::mergeTags($cache_tags, $entityCacheTags);
    }

    $cacheMetadata = new CacheableMetadata();
    $cacheMetadata->addCacheTags(['languages', 'announcement_list']);

    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'] ?? [], $cache_tags, $cacheMetadata->getCacheTags());

    return $build;
  }
}
