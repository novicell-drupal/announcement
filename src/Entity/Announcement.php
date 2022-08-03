<?php
namespace Drupal\announcement\Entity;

use Drupal\announcement\AnnouncementInterface;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the Announcement entity.
 *
 * @ingroup announcement
 *
 * @ContentEntityType(
 *   id = "announcement",
 *   label = @Translation("Announcement"),
 *   label_collection = @Translation("Announcements"),
 *   label_singular = @Translation("announcement"),
 *   label_plural = @Translation("announcements"),
 *   label_count = @PluralTranslation(
 *     singular = "@count announcement",
 *     plural = "@count announcement",
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\announcement\AnnouncementListBuilder",
 *     "access" = "Drupal\announcement\AnnouncementAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\announcement\Form\AnnouncementForm",
 *       "add" = "Drupal\announcement\Form\AnnouncementForm",
 *       "edit" = "Drupal\announcement\Form\AnnouncementForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\announcement\Routing\AnnouncementRouteProvider",
 *     }
 *   },
 *   base_table = "announcement",
 *   data_table = "announcement_field_data",
 *   revision_table = "announcement_revision",
 *   revision_data_table = "announcement_revision_field_data",
 *   translatable = TRUE,
 *   permission_granularity = "entity_type",
 *   admin_permission = "administer announcement",
 *   entity_keys = {
 *     "id" = "nid",
 *     "revision" = "vid",
 *     "label" = "title",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *   },
 *   show_revision_ui = TRUE,
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   field_ui_base_route = "entity.announcement.admin_form",
 *   links = {
 *     "add-page" = "/admin/content/announcement/add",
 *     "add-form" = "/admin/content/announcement/add",
 *     "canonical" = "/admin/content/announcement/{announcement}/edit",
 *     "collection" = "/admin/content/announcement",
 *     "delete-form" = "/admin/content/announcement/{announcement}/delete",
 *     "delete-multiple-form" = "/admin/content/announcement/delete",
 *     "edit-form" = "/admin/content/announcement/{announcement}/edit",
 *     "revision" = "/admin/content/announcement/{announcement}/revisions/{announcement_revision}/view",
 *   }
 * )
 */
class Announcement extends EditorialContentEntityBase implements AnnouncementInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid']
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the content author.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status']
      ->setLabel(t('Enable'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 95,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the announcement was created.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the announcement was last edited.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['publish_time'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Time to enable'))
      ->setDescription(t('The time that the announcement will be enabled.'))
      ->setRequired(FALSE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'datetime_plain',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['unpublish_time'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Time to disable'))
      ->setDescription(t('The time that the announcement will be disabled.'))
      ->setRequired(FALSE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('datetime_type', DateTimeItem::DATETIME_TYPE_DATETIME)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'datetime_plain',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

}
