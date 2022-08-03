<?php

namespace Drupal\announcement;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines an access control handler for announcement items.
 */
class AnnouncementAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a announcementAccessControlHandler object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface|null $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityTypeManagerInterface $entity_type_manager = NULL) {
    parent::__construct($entity_type);
    if (!isset($entity_type_manager)) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $entity_type_manager argument is deprecated in drupal:9.3.0 and will be required in drupal:10.0.0. See https://www.drupal.org/node/3214171', E_USER_DEPRECATED);
      $entity_type_manager = \Drupal::entityTypeManager();
    }
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager'),
    );
  }


  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\announcement\announcementInterface $entity */
    // Allow admin permission to override all operations.
    if ($account->hasPermission($this->entityType->getAdminPermission())) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    $is_owner = ($account->id() && $account->id() === $entity->getOwnerId());
    switch ($operation) {
      case 'view':
        if ($entity->isPublished()) {
          $access_result = AccessResult::allowedIf($account->hasPermission('view announcement'))
            ->cachePerPermissions()
            ->addCacheableDependency($entity);
          if (!$access_result->isAllowed()) {
            $access_result->setReason("The 'view announcement' permission is required when the announcement item is published.");
          }
        }
        elseif ($account->hasPermission('view own unpublished announcement')) {
          $access_result = AccessResult::allowedIf($is_owner)
            ->cachePerPermissions()
            ->cachePerUser()
            ->addCacheableDependency($entity);
          if (!$access_result->isAllowed()) {
            $access_result->setReason("The user must be the owner and the 'view own unpublished announcement' permission is required when the announcement item is unpublished.");
          }
        }
        else {
          $access_result = AccessResult::neutral()
            ->cachePerPermissions()
            ->addCacheableDependency($entity)
            ->setReason("The user must be the owner and the 'view own unpublished announcement' permission is required when the announcement item is unpublished.");
        }
        return $access_result;

      case 'update':
        if ($account->hasPermission('update any announcement')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        if ($account->hasPermission('update own announcement') && $is_owner) {
          return AccessResult::allowed()->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);
        }
        return AccessResult::neutral("The following permissions are required: 'update any announcement' OR 'update own announcement'.")->cachePerPermissions();

      case 'delete':
        if ($account->hasPermission('delete any announcement')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        if ($account->hasPermission('delete own announcement') && $is_owner) {
          return AccessResult::allowed()->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);
        }
        return AccessResult::neutral("The following permissions are required: 'delete any announcement' OR 'delete own announcement'.")->cachePerPermissions();

      case 'view all revisions':
        // Perform basic permission checks first.
        if (!$account->hasPermission('view all announcement revisions')) {
          return AccessResult::neutral("The 'view all announcement revisions' permission is required.")->cachePerPermissions();
        }

        // First check the access to the default revision and finally, if the
        // announcement passed in is not the default revision then access to that,
        // too.
        $announcement_storage = $this->entityTypeManager->getStorage($entity->getEntityTypeId());
        $access = $this->access($announcement_storage->load($entity->id()), 'view', $account, TRUE);
        if (!$entity->isDefaultRevision()) {
          $access = $access->andIf($this->access($entity, 'view', $account, TRUE));
        }
        return $access->cachePerPermissions()->addCacheableDependency($entity);

      default:
        return AccessResult::neutral()->cachePerPermissions();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $permissions = [
      'administer announcement',
      'create announcement',
    ];
    return AccessResult::allowedIfHasPermissions($account, $permissions, 'OR');
  }

}
