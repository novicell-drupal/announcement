<?php

namespace Drupal\announcement\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media\IFrameUrlHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AnnouncementSettingsForm extends ConfigFormBase {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AnnouncementSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }
  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames() {
    return ['announcement.settings'];
  }

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'announcement_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['standalone_url'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Standalone announcement URL'),
      '#default_value' => $this->config('announcement.settings')->get('standalone_url'),
      '#description' => $this->t("Allow users to access @announcement-entities at /announcement/{id}.", ['@announcement-entities' => $this->entityTypeManager->getDefinition('announcement')->getPluralLabel()]),
    ];
    $form['delete_when_deactivated'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Delete when deactivated'),
      '#default_value' => $this->config('announcement.settings')->get('delete_when_deactivated'),
      '#description' => $this->t("Instead of deactivating announcements at the deactivation time, delete them instead."),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('announcement.settings')
      ->set('delete_when_deactivated', $form_state->getValue('delete_when_deactivated'))
      ->set('standalone_url', $form_state->getValue('standalone_url'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
