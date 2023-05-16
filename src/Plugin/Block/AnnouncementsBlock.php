<?php

namespace Drupal\announcement\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Psr\Container\ContainerInterface;

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
   * The entity storage for announcements.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The view builder for announcements.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * Constructs a new AnnouncementsBlock object.
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
    $this->viewBuilder = $entity_type_manager->getViewBuilder('announcement');
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

  /**
   * Build the content of the block.
   *
   * @return array
   *   A renderable array.
   */
  public function build() {
    $cacheMetadata = new CacheableMetadata();
    $cacheMetadata->addCacheTags(['languages', 'announcement_list']);
    $build = [];

    $ids = $this->storage->getQuery()->condition('status', TRUE)->execute();
    $entities = $this->storage->loadMultiple($ids);

    $build['announcements'] = $this->viewBuilder->viewMultiple($entities);
    foreach ($entities as $entity) {
      $cacheMetadata->addCacheableDependency($entity);
    }

    $cacheMetadata->applyTo($build);

    return $build;
  }
}
