<?php

namespace Drupal\Tests\message_thread\Functional;

use Drupal\message_thread\Entity\MessageThreadTemplate;
use Drupal\Tests\message_thread\Kernel\MessageThreadTemplateCreateTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Holds set of tools for the message testing.
 */
abstract class MessageThreadTestBase extends BrowserTestBase {

  use MessageThreadTemplateCreateTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['message_thread', 'views'];

  /**
   * The node access controller.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  protected $accessController;

  /**
   * Load a message template easily.
   *
   * @param string $template
   *   The template of the message.
   *
   * @return \Drupal\message\Entity\MessageThreadTemplate
   *   The message Object.
   */
  protected function loadMessageThreadTemplate($template) {
    return MessageThreadTemplate::load($template);
  }

  /**
   * Return a config setting.
   *
   * @param string $config
   *   The config value.
   * @param string $storage
   *   The storing of the configuration. Default to message.message.
   *
   * @return mixed
   *   The value of the config.
   */
//  protected function getConfig($config, $storage = 'message_thread.settings') {
//    return \Drupal::config($storage)->get($config);
//  }

  /**
   * Set a config value.
   *
   * @param string $config
   *   The config name.
   * @param string $value
   *   The config value.
   * @param string $storage
   *   The storing of the configuration. Default to message.message.
   */
//  protected function configSet($config, $value, $storage = 'message_thread.settings') {
//    \Drupal::configFactory()->getEditable($storage)->set($config, $value);
//  }

}
