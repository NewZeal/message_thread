<?php

namespace Drupal\message_thread\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Language\Language;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Form controller for the message_ui entity edit forms.
 *
 * @ingroup message_ui
 */
class MessageThreadForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\message\Entity\Message $message */
    $message_thread = $this->entity;

    $template = \Drupal::entityTypeManager()->getStorage('message_thread_template')->load($this->entity->bundle());

    if ($this->config('message_thread.settings')->get('show_preview')) {
      $form['text'] = [
        '#type' => 'item',
        '#title' => t('Message template'),
        '#markup' => implode("\n", $template->getText()),
      ];
    }

    // Create the advanced vertical tabs "group".
    $form['advanced'] = [
      '#type' => 'details',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];


    $form['owner'] = [
      '#type' => 'fieldset',
      '#title' => t('Owner information'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#group' => 'advanced',
      '#weight' => 90,
      '#attributes' => ['class' => ['message-form-owner']],
      '#attached' => [
        'library' => ['message_ui/message_ui.message'],
        'drupalSettings' => [
          'message_ui' => [
            'anonymous' => \Drupal::config('message_ui.settings')->get('anonymous'),
          ],
        ],
      ],
    ];

    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'owner';
    }

    if (isset($form['created'])) {
      $form['created']['#group'] = 'owner';
    }



    // @todo: assess the best way to access and create tokens tab from D7.
//    $tokens = $message_thread->getArguments();
//
//    $access = \Drupal::currentUser()->hasPermission('update tokens') || \Drupal::currentUser()->hasPermission('bypass message access control');
//    if (!empty($tokens) && ($access)) {
//      $form['tokens'] = [
//        '#type' => 'fieldset',
//        '#title' => t('Tokens and arguments'),
//        '#collapsible' => TRUE,
//        '#collapsed' => TRUE,
//        '#group' => 'advanced',
//        '#weight' => 110,
//      ];
//
//      // Give the user an option to update the har coded tokens.
//      $form['tokens']['replace_tokens'] = [
//        '#type' => 'select',
//        '#title' => t('Update tokens value automatically'),
//        '#description' => t('By default, the hard coded values will be replaced automatically. If unchecked - you can update their value manually.'),
//        '#default_value' => 'no_update',
//        '#options' => [
//          'no_update' => t("Don't update"),
//          'update' => t('Update automatically'),
//          'update_manually' => t('Update manually'),
//        ],
//      ];
//
//      $form['tokens']['values'] = [
//        '#type' => 'container',
//        '#states' => [
//          'visible' => [
//            ':input[name="replace_tokens"]' => ['value' => 'update_manually'],
//          ],
//        ],
//      ];
//
//      // Build list of fields to update the tokens manually.
//      foreach ($message_thread->getArguments() as $name => $value) {
//        $form['tokens']['values'][$name] = [
//          '#type' => 'textfield',
//          '#title' => t("@name's value", ['@name' => $name]),
//          '#default_value' => $value,
//        ];
//      }
//    }
//
//    $form['langcode'] = [
//      '#title' => $this->t('Language'),
//      '#type' => 'language_select',
//      '#default_value' => $message_thread->getUntranslated()->language()->getId(),
//      '#languages' => Language::STATE_ALL,
//    ];

    // @todo : add similar to node/from library, adding css for
    // 'message-form-owner' class.
    // $form['#attached']['library'][] = 'node/form';
    return $form;
  }


  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $message_thread = $this->entity;

    // @todo : check if we need access control here on form submit.
    // Create custom save button with conditional label / value.
    $element['save'] = $element['submit'];
    if ($message_thread->isNew()) {
      $element['save']['#value'] = t('Create');
    }
    else {
      $element['save']['#value'] = t('Update');
    }
    $element['save']['#weight'] = 0;

    $mid = $message_thread->id();
    $url = is_object($message_thread) && !empty($mid) ? Url::fromRoute('entity.message.canonical', ['message' => $mid]) : Url::fromRoute('message.overview_templates');
    $link = Link::fromTextAndUrl(t('Cancel'), $url)->toString();

    // Add a cancel link to the message form actions.
    $element['cancel'] = [
      '#type' => 'markup',
      '#markup' => $link,
    ];

    // Remove the default "Save" button.
    $element['submit']['#access'] = FALSE;

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * Updates the message object by processing the submitted values.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Add owner as a  participant
    // We do this before the parent::submit to ensure the value is saved
    $values = $form_state->getValues();;
    $found = FALSE;
    foreach ($values['field_thread_participants'] as $key => $participant) {
      if (!is_numeric($key)) {
        continue;
      }
      if ($participant['target_id'] == $values['uid'][0]['target_id']) {
        $found = TRUE;

      }
      if ($participant['target_id'] == NULL) {
        unset($values['field_thread_participants'][$key]);
      }

    }
    if (!$found) {
      $values['field_thread_participants'][] = [
        'target_id' => $values['uid'][0]['target_id'],
        'weight' => 0
      ];
      $form_state->setValue(['field_thread_participants'], $values['field_thread_participants']);
    }

    parent::submitForm($form, $form_state);

    /* @var $message_thread Message */
    $message_thread = $this->entity;

    // Set message owner.

    $uid = $form_state->getValue('uid');
    if (is_array($uid) && !empty($uid[0]['target_id'])) {
      $message_thread->setOwnerId($uid[0]['target_id']);
    }

    // Set the timestamp to custom value or request time.
    $created = $form_state->getValue('date');
    if ($created) {
      $message_thread->setCreatedTime(strtotime($created));
    }
    else {
      $message_thread->setCreatedTime(REQUEST_TIME);
    }
//
//    // Get the tokens to be replaced and prepare for replacing.
//    $replace_tokens = $form_state->getValue('replace_tokens');
//    $token_actions = empty($replace_tokens) ? [] : $replace_tokens;
//
//    // Get the message args and replace tokens.
//    if ($args = $message_thread->getArguments()) {
//
//      if (!empty($token_actions) && $token_actions != 'no_update') {
//
//        // Loop through the arguments of the message.
//        foreach (array_keys($args) as $token) {
//
//          if ($token_actions == 'update') {
//            // Get the hard coded value of the message and him in the message.
//            $token_name = str_replace(['@{', '}'], ['[', ']'], $token);
//            $token_service = \Drupal::token();
//            $value = $token_service->replace($token_name, ['message' => $message_thread]);
//          }
//          else {
//            // Hard coded value given from the user.
//            $value = $form_state->getValue($token);
//          }
//
//          $args[$token] = $value;
//        }
//      }
//    }
//
//    $this->entity->setArguments($args);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /* @var $message_thread Message */
    $message_thread = $this->entity;
    $insert = $message_thread->isNew();
    $message_thread->save();

    // Set up message link and status message contexts.
    $message_thread_link = $message_thread->link($this->t('View'));
    $context = [
      '@type' => $message_thread->getTemplate(),
      '%title' => 'Message:' . $message_thread->id(),
      'link' => $message_thread_link,
    ];
    $t_args = [
      '@type' => $message_thread->getEntityType()->getLabel(),
      '%title' => 'Message:' . $message_thread->id(),
    ];

    // Display newly created or updated message depending on if new entity.
    if ($insert) {
      $this->logger('content')->notice('@type: added %title.', $context);
      drupal_set_message(t('@type %title has been created.', $t_args));
    }
    else {
      $this->logger('content')->notice('@type: updated %title.', $context);
      drupal_set_message(t('@type %title has been updated.', $t_args));
    }

    // Redirect to message thread view display if user has access.
    if ($message_thread->id()) {
      $form_state->setValue('thread_id', $message_thread->id());
      $form_state->set('thread_id', $message_thread->id());
      if ($message_thread->access('view')) {
        $form_state->setRedirect('entity.message_thread.canonical', ['message_thread' => $message_thread->id()]);
      }
      else {
        $form_state->setRedirect('<front>');
      }
      // @todo : for node they clear temp store here, but perhaps unused with
      // message.
    }
    else {
      // In the unlikely case something went wrong on save, the message will be
      // rebuilt and message form redisplayed.
      drupal_set_message(t('The message thread could not be saved.'), 'error');
      $form_state->setRebuild();
    }
  }

}
