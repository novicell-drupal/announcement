<?php

namespace Drupal\announcement;

use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a announcement entity.
 */
interface AnnouncementInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, RevisionLogInterface, EntityPublishedInterface {

  /**
   * Denotes that the announcement is not published.
   */
  const NOT_PUBLISHED = 0;

  /**
   * Denotes that the announcement is published.
   */
  const PUBLISHED = 1;

  /**
   * Gets the announcement title.
   *
   * @return string
   *   Title of the announcement.
   */
  public function getTitle();

  /**
   * Sets the announcement title.
   *
   * @param string $title
   *   The announcement title.
   *
   * @return $this
   *   The called announcement entity.
   */
  public function setTitle($title);

  /**
   * Gets the announcement creation timestamp.
   *
   * @return int
   *   Creation timestamp of the announcement.
   */
  public function getCreatedTime();

  /**
   * Sets the announcement creation timestamp.
   *
   * @param int $timestamp
   *   The announcement creation timestamp.
   *
   * @return $this
   *   The called announcement entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the announcement revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the announcement revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return $this
   *   The called announcement entity.
   */
  public function setRevisionCreationTime($timestamp);

}
