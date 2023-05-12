<?php

namespace Drupal\announcement\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $cache_tags_invalidator = \Drupal::service('cache_tags.invalidator');

    $ids = $this->entityTypeManager->getStorage('announcement')->getQuery()->condition('status', TRUE)->execute();
    $entities = $this->entityTypeManager->getStorage('announcement')->loadMultiple($ids);

    $build['announcements'] = $this->entityTypeManager->getViewBuilder('announcement')->viewMultiple($entities, 'full');
    foreach ($entities as $entity) {
      $cache_tags_invalidator->addTags($entity->getCacheTagsToInvalidate());
    }

    return $build;
  }

}
