<?php

namespace Drupal\message_thread;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides the views data for the message entity type.
 */
class MessageThreadViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    // We establish Views handlers for message_thread_index
    $data['message_thread_index']['table']['group'] = t('Message Threads');
    $data['message_thread_index']['table']['join'] = array(
      'message_thread_field_data' => array(
        'left_field' => 'thread_id',
        'field' => 'thread_id',
      ),
    );
    $data['message_thread_index']['table']['join'] = array(
      'message_field_data' => array(
        'left_field' => 'mid',
        'field' => 'mid',
      ),
    );
    $data['message_thread_index']['thread_id'] = array(
      'argument' => array(
        'id' => 'thread_id',
        'numeric' => TRUE,
      ),
      'relationship' => array(
        'id' => 'standard',
        'base' => 'message_thread_field_data',
        'base field' => 'thread_id',
        'title' => $this->t('Message Thread'),
        'label' => $this->t('Link the message thread index to the thread.'),
      ),
    );
    $data['message_thread_index']['mid'] = array(
      'argument' => array(
        'id' => 'mid',
        'numeric' => TRUE,
      ),
      'relationship' => array(
        'id' => 'standard',
        'base' => 'message_field_data',
        'base field' => 'mid',
        'title' => $this->t('Message'),
        'label' => $this->t('Link the message thread index to the message.'),
      ),
    );
    return $data;
  }

}
