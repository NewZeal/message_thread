<?php

namespace Drupal\Tests\message_thread\Functional;

/**
 * Testing the CRUD functionality for the Message template entity.
 *
 * @group message_thread
 */
class MessageThreadTemplateCrudTest extends MessageThreadTestBase {

  /**
   * Currently experiencing schema errors
   *
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Creating/reading/updating/deleting the message template entity and test it.
   */
  public function testCrudEntityType() {
    // Create the message template.
    $created_message_template = $this->createMessageThreadTemplate('dummy_message', 'Dummy test', 'This is a dummy message with a dummy message', ['Dummy message']);

    // Reset any static cache.
    drupal_static_reset();

    // Load the message and verify the message template structure.
    $template = $this->loadMessageThreadTemplate('dummy_message');

    $values = [
      'template' => 'Template',
      'label' => 'Label',
      'description' => 'Description',
      'text' => 'Text',
    ];
    foreach ($values as $key => $label) {
      $param = [
        '@label' => $label,
      ];

      $this->assertEqual(call_user_func([$template, 'get' . $key]), call_user_func([$created_message_template, 'get' . $key]), format_string('The @label between the message we created an loaded are equal', $param));
    }

    // Verifying updating action.
    $template->setLabel('New label');
    $template->save();

    // Reset any static cache.
    drupal_static_reset();

    $template = $this->loadMessageThreadTemplate('dummy_message');
    $this->assertEqual($template->getLabel(), 'New label', 'The message was updated successfully');

    // Delete the message any try to load it from the DB.
    $template->delete();

    // Reset any static cache.
    drupal_static_reset();

    $this->assertFalse($this->loadMessageThreadTemplate('dummy_message'), 'The message was not found in the DB');
  }

}
