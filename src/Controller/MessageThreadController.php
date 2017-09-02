<?php

namespace Drupal\message_thread\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Drupal\message_thread\Entity\MessageThread;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\message_thread\MessageThreadTemplateInterface;
use Drupal\message_thread\MessageThreadInterface;
use Drupal\message\Entity\Message;
use Drupal\Component\Utility\Xss;

/**
 * Controller for adding messages.
 */
class MessageThreadController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The access handler object.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  private $accessHandler;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a MessageUiController object.
   */
  public function __construct() {
    $this->entityManager = \Drupal::entityManager();
    $this->accessHandler = \Drupal::entityManager()->getAccessControlHandler('message_thread');
  }

  /**
   * Generates output of all message template with permission to create.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the message templates that can be added;
   *   however, if there is only one message template defined for the site, the
   *   function will return a RedirectResponse to the message.add page for that
   *   one message template.
   */
  public function addPage() {
    $content = [];
//dpm('here we are');
    // Only use message templates the user has access to.
    foreach ($this->entityManager()->getStorage('message_thread_template')->loadMultiple() as $template) {
      $access = $this->entityManager()
        ->getAccessControlHandler('message_thread')
        ->createAccess($template->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$template->id()] = $template;
      }
    }

    // Bypass the message/add listing if only one message template is available.
    if (count($content) == 1) {
      $template = array_shift($content);
      return $this->redirect('message_thread.add', ['message_thread_template' => $template->id()]);
    }

    // Return build array.
    if (!empty($content)) {
      return ['#theme' => 'message_thread_add_list', '#content' => $content];
    }
    else {
      $url = Url::fromRoute('message_thread.template_add');
      return ['#markup' => 'There are no messages templates. You can create a new message template <a href="/' . $url->getInternalPath() . '">here</a>.'];
    }
  }

  /**
   * Generates form output for adding a new message entity of message_thread_template.
   *
   * @param \Drupal\message\MessageThreadTemplateInterface $message_template
   *   The message template object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function add($message_thread_template) {
    $message_thread = MessageThread::create(['template' => $message_thread_template]);
    $form = $this->entityFormBuilder()->getForm($message_thread);

    return $form;
  }

  /**
   * Generates form output for adding a new message entity of message_template inside a thread
   *
   * @param \Drupal\message\MessageTemplateInterface $message_template
   *   The message template object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function reply($message_template, $message_thread) {
    $message = Message::create(['template' => $message_template]);
    $form = $this->entityFormBuilder()->getForm($message);

    $form['thread_id'] = array(
      '#type' => 'hidden',
      '#value' => $message_thread
    );

//    $form['#submit'][] = 'message_thread_add_message_form_submit';
    foreach (array_keys($form['actions']) as $action) {
      if ($action != 'preview' && isset($form['actions'][$action]['#type']) && $form['actions'][$action]['#type'] === 'submit') {
        unset($form['actions'][$action]['#submit']);
        $form['actions'][$action]['#submit'][] = 'message_thread_add_message_form_submit';
      }
    }
    unset( $form['#submit']);

    return $form;
  }

  /**
   * Generates form output for deleting of multiple message entities.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function deleteMultiple() {
    // @todo - create the path corresponding to below.
    // From devel module - admin/config/development/message_delete_multiple.
    // @todo pass messages to be deleted in args?
    $build = \Drupal::formBuilder()->getForm('Drupal\message_thread\Form\DeleteMultiple');

    return $build;
  }


  /**
   * Generates output of all threads belonging to the current user
   *
   * @return
   *   A render array for a list of the messages;
   */
  public function inBox($user) {
    $message_threads = array();

    // Get threads that the current user belongs to

    // Return build array.
    if (!empty($messages)) {
      return array(
        '#theme' => 'message_thread__inbox',
        '#messages' => $message_threads
      );
    }
    else {
      $url = Url::fromRoute('message.template_add');
      return array(
        '#markup' => 'You have no messages in your inbox. Try sending a message to someone <a href="/' . $url->getInternalPath() . '">sending a message to someone</a>.'
      );
    }
  }

  /**
   * @param MessageThread|NULL $message_thread
   * @return array|string
   */
  function messageThreadTitle(MessageThread $message_thread = NULL) {
    return $message_thread ? ['#markup' => $message_thread->get('field_thread_title')->getValue()[0]['value'], '#allowed_tags' => Xss::getHtmlTagList()] : '';
  }

  /**
   * Generates form output for adding a new message entity of message_template.
   *
   * @param \Drupal\message\MessageTemplateInterface $message_template
   *   The message template object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function sent($user) {
    // Return build array.
    if (!empty($messages)) {
      return array(
        '#theme' => 'message_private__inbox',
        '#messages' => $messages
      );
    }
    else {
      $url = Url::fromRoute('message.template_add');
      return array(
        '#markup' => 'You have no messages in your inbox. Try sending a message to someone <a href="/' . $url->getInternalPath() . '">sending a message to someone</a>.'
      );
    }
  }


}
