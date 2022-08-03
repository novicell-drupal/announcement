<?php

namespace Drupal\announcement\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

class AnnouncementForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    if ($this->operation === 'edit') {
      $form['#title'] = $this->t('Edit @label', [
        '@label' => $this->entity->label(),
      ]);
    }

    // Announcement author information for administrators.
    if (isset($form['uid']) || isset($form['created'])) {
      $form['author'] = [
        '#type' => 'details',
        '#title' => $this->t('Authoring information'),
        '#group' => 'advanced',
        '#attributes' => [
          'class' => ['announcement-form-author'],
        ],
        '#weight' => 90,
        '#optional' => TRUE,
      ];
    }

    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'author';
    }

    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }

    // Announcement schedule information for administrators.
    if (isset($form['publish_time']) || isset($form['unpublish_time'])) {
      $form['schedule'] = [
        '#type' => 'details',
        '#title' => $this->t('Schedule information'),
        '#group' => 'advanced',
        '#attributes' => [
          'class' => ['announcement-form-schedule'],
        ],
        '#weight' => 80,
        '#optional' => TRUE,
      ];
    }

    if (isset($form['publish_time'])) {
      $form['publish_time']['#group'] = 'schedule';
    }

    if (isset($form['unpublish_time'])) {
      $form['unpublish_time']['#group'] = 'schedule';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    if (is_null($form_state->getValue('publish_time')[0])) {
      $this->entity->set('publish_time', NULL);
    }
    if (is_null($form_state->getValue('unpublish_time')[0])) {
      $this->entity->set('unpublish_time', NULL);
    }
    $saved = parent::save($form, $form_state);
    $context = ['%label' => $this->entity->label(), 'link' => $this->entity->toLink($this->t('Edit'))->toString()];
    $logger = $this->logger('announcement');
    $t_args = ['%label' => $this->entity->toLink($this->entity->label())->toString()];

    if ($saved === SAVED_NEW) {
      $logger->notice('Announcements: added %label.', $context);
      $this->messenger()->addStatus($this->t('Announcement %label has been created.', $t_args));
    }
    else {
      $logger->notice('Announcements: updated %label.', $context);
      $this->messenger()->addStatus($this->t('Announcement %label has been updated.', $t_args));
    }

    // Redirect the user to the media overview if the user has the 'access media
    // overview' permission. If not, redirect to the canonical URL of the media
    // item.
    if ($this->currentUser()->hasPermission('access announcement overview')) {
      $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    }
    else {
      $form_state->setRedirectUrl($this->entity->toUrl());
    }

    return $saved;
  }
}
