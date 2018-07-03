<?php

namespace Drupal\Tests\message_thread\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\message_thread\Entity\MessageThread;
use Drupal\simpletest\UserCreationTrait;

/**
 * Test theming of message threads.
 *
 * @group message_thread
 */
class MessageThreadThemeTest extends KernelTestBase {

  use MessageThreadTemplateCreateTrait;
  use UserCreationTrait;

  /**
   * User account.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['message_thread', 'user', 'system', 'filter'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installEntitySchema('message_thread');
    $this->installEntitySchema('user');
    $this->installSchema('system', ['sequences']);
    $this->installConfig(['filter']);

    $this->account = $this->createUser();
  }

  /**
   * Test that message render returns message text wrapped in a div.
   */
  public function testMessageTextWrapper() {
    $template = 'dummy_message_thread';
    // Create message thread to be rendered.
    $message_thread_template = $this->createMessageThreadTemplate($template, 'Dummy message thread', '', ['Text to be wrapped by div.']);
    $message_thread = MessageThread::create(['template' => $message_thread_template->id()])
      ->setOwner($this->account);

    $message_thread->save();

    // Simulate theming of the message.
    $build = $this->container->get('entity_type.manager')->getViewBuilder('message_thread')->view($message_thread);
    $output = $this->container->get('renderer')->renderRoot($build);
    $this->setRawContent($output);
  }

}
