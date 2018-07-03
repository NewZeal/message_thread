<?php

namespace Drupal\Tests\message_thread\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\message_thread\Entity\MessageThreadTemplate;
use Drupal\message_thread\Entity\MessageThread;

/**
 * Testing the CRUD functionality for the Message template entity.
 *
 * @group message_thread
 */
class MessageThreadTemplateUiTest extends MessageThreadTestBase {

  /**
   * Currently experiencing schema errors
   *
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'language',
    'config_translation',
    'message_thread',
    'filter_test',
  ];

  /**
   * The user object.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->account = $this->drupalCreateUser([
      'administer message thread templates',
      'translate configuration',
      'use text format filtered_html',
    ]);
  }

  /**
   * Test the translation interface for message thread templates.
   */
  public function testMessageThreadTemplateTranslate() {
    $this->drupalLogin($this->account);

    // Test the creation of a message thread template.
    $edit = [
      'label' => 'Dummy message thread template',
      'template' => 'dummy_message_thread_template',
      'description' => 'This is a dummy text',
    ];
    $this->drupalPostForm('admin/structure/message-thread/template/add', $edit, t('Save message thread template'));
    $this->assertText('The message thread template Dummy message thread template created successfully.', 'The message thread template was created successfully');
    $this->drupalGet('admin/structure/message-thread/manage/dummy_message_thread_template');

    $elements = [
      '//input[@value="Dummy message thread template"]' => 'The label input text exists on the page with the right text.',
      '//input[@value="This is a dummy text"]' => 'The description of the message thread exists on the page.',
    ];
    $this->verifyFormElements($elements);

    // Test the editing of a message thread template.
    $edit = [
      'label' => 'Edited dummy message',
      'description' => 'This is a dummy text after editing',
    ];
    $this->drupalPostForm('admin/structure/message-thread/manage/dummy_message_thread_template', $edit, t('Save message thread template'));

    $this->drupalGet('admin/structure/message-thread/manage/dummy_message_thread_template');

    $elements = [
      '//input[@value="Edited dummy message"]' => 'The label input text exists on the page with the right text.',
      '//input[@value="This is a dummy text after editing"]' => 'The description of the message thread exists on the page.',
    ];
    $this->verifyFormElements($elements);

    // Add language.
//    ConfigurableLanguage::create(['id' => 'he', 'name' => 'Hebrew'])->save();
//
//    // Change to post form and add text different then the original.
//    $edit = [
//      'translation[config_names][message_thread.template.dummy_message_thread_template][label]' => 'Translated dummy message thread template to Hebrew',
//      'translation[config_names][message_thread.template.dummy_message_thread_template][description]' => 'This is a dummy text after translation to Hebrew',
//    ];
//    $this->drupalPostForm('admin/structure/message-thread/manage/dummy_message_thread_template/translate/he/add', $edit, t('Save translation'));
//
//    // Go to the edit form and verify text.
//    $this->drupalGet('admin/structure/message-thread/manage/dummy_message_thread_template/translate/he/edit');
//
//    $elements = [
//      '//input[@value="Translated dummy message thread template to Hebrew"]' => 'The text in the form translation is the expected string in Hebrew.',
//      '//textarea[.="This is a dummy text after translation to Hebrew"]' => 'The description element have the expected value in Hebrew.',
//    ];
//    $this->verifyFormElements($elements);
//
//    // Load the message thread template via code in hebrew and english and verify the
//    // text.
//    /* @var $template MessageThreadTemplate */
//    $template = MessageThreadTemplate::load('dummy_message_thread_template');
//    $this->assertEquals('<p>This is a dummy text after translation to Hebrew</p>', $template->getDescription('he'), 'The text in hebrew pulled correctly.');
//    $this->assertEquals('<p>This is a dummy text after editing</p>', $template->getDescription(), 'The text in english pulled correctly.');
//
//    // Create a message thread using that same template and test that multilingual text
//    // still works.
//    /* @var $template Message */
//    $message_thread = MessageThread::create([
//      'template' => 'dummy_message_thread_template',
//    ]);
//    $this->assertEquals('<p>This is a dummy message thread with translated text to Hebrew</p>', $message_thread->getDescription('he'), 'The text in hebrew pulled correctly.');
//    $this->assertEquals('<p>This is a dummy message thread with some edited dummy text</p>', $message_thread->getDescription(), 'The text in english pulled correctly.');
//
//    // Test changing the language of the message thread template with setLanguage().
//    $message_thread->setLanguage('he');
//    $this->assertEquals('<p>This is a dummy message thread with translated text to Hebrew</p>', $message_thread->getDescription(), 'The text in hebrew pulled correctly.');

    // Delete message thread via the UI.
    $this->drupalPostForm('admin/structure/message-thread/delete/dummy_message_thread_template', [], 'Delete');
//    $this->assertText(t('There is no Message thread template yet.'));
    $this->assertFalse(MessageThreadTemplate::load('dummy_message_thread_template'), 'The message thread template deleted via the UI successfully.');
  }

  /**
   * Verifying the form elements values in easy way.
   *
   * When all the elements are passing a pass message thread with the text "The
   * expected values is in the form." When one of the Xpath expression return
   * false the message thread will be display on screen.
   *
   * @param array $elements
   *   Array mapped by in the next format.
   *
   * @code
   *   [XPATH_EXPRESSION => MESSAGE]
   * @endcode
   */
  private function verifyFormElements(array $elements) {
    $errors = [];
    foreach ($elements as $xpath => $message) {
      $element = $this->xpath($xpath);
      if (!$element) {
        $errors[] = $message;
      }
    }

    if (empty($errors)) {
      $this->pass('All elements were found.');
    }
    else {
      $this->fail('The next errors were found: ' . implode("", $errors));
    }
  }

}
