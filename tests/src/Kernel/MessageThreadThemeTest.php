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

}
