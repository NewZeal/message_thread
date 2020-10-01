<?php

namespace Drupal\message_thread\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for message template deletion.
 */
class MessageThreadTemplateDeleteConfirm extends EntityConfirmFormBase {

  /**
   * The query factory to create entity queries.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $queryFactory;

  /**
   * Constructs a new MessageTemplateDeleteConfirm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity query object.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->queryFactory = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the message thread template %template?',
      ['%template' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Check if any messages are using this template.
    $number_messages = $this->queryFactory->getStorage('message')->getQuery();
    $count = $number_messages->condition('template', $this->entity->id())->count()->execute();
    if ($count) {
      $caption = $this->formatPlural($count, '%template is used by 1 message on your site. You cannot remove this message template until you have removed all of the %template messages.', '%template is used by @count messages on your site. You may not remove %template until you have removed all of the %template messages.', ['%template' => $this->entity->label()]);
      $form['#title'] = $this->getQuestion();
      $form['description'] = ['#markup' => $caption];
      return $form;
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $t_args = ['%name' => $this->entity->label()];
    $this->messenger()->addStatus(t('The message template %name has been deleted.', $t_args));
    $this->logger('content')->notice('Deleted message template %name', $t_args);
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * Returns the route to go to if the user cancels the action.
   *
   * @return \Drupal\Core\Url
   *   A URL object.
   */
  public function getCancelUrl() {
    return new Url('message_thread.overview_templates');
  }

}
