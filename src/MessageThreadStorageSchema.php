<?php

namespace Drupal\message_thread;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the term schema handler.
 */
class MessageThreadStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset = FALSE);

    $schema['message_thread_index'] = array(
      'description' => 'Maintains denormalized information about thread/message relationships.',
      'fields' => array(
        'mid' => array(
          'description' => 'The {message}.mid this record tracks.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'thread_id' => array(
          'description' => 'The thread ID.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'created' => array(
          'description' => 'The Unix timestamp when the message was created.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ),
      ),
      'primary key' => array('mid', 'thread_id'),
      'indexes' => array(
        'thread_message' => array('thread_id', 'created'),
      ),
      'foreign keys' => array(
        'tracked_message' => array(
          'table' => 'message',
          'columns' => array('mid' => 'mid'),
        ),
        'term' => array(
          'table' => 'message_field_data',
          'columns' => array('mid' => 'mid'),
        ),
      ),
    );

    return $schema;
  }

}
