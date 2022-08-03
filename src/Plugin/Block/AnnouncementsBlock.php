<?php

namespace Drupal\announcement\Plugin\Block;

use Doctrine\Common\Cache\Cache;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides an 'Announcements' block.
 *
 * @Block(
 *   id = "announcement_block",
 *   admin_label = @Translation("Announcements"),
 *   category= @Translation("Announcements")
 * )
 */
class AnnouncementsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Announcement entity storage class.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Announcement entity view builder class.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * Constructs a new SystemBreadcrumbBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   Announcement entity storage class.
   * @var \Drupal\Core\Entity\EntityStorageInterface
   *   Announcement entity view builder class.
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $storage, EntityViewBuilderInterface $viewBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->storage = $storage;
    $this->viewBuilder = $viewBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = $container->get('entity_type.manager');
    return new static($configuration, $plugin_id, $plugin_definition, $entityTypeManager->getStorage('announcement'), $entityTypeManager->getViewBuilder('announcement'));
  }

  /**
   * {@inheritDoc}
   */
  public function build(): array {
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
