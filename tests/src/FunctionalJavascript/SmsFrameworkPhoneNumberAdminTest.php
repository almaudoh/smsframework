<?php

namespace Drupal\Tests\sms\FunctionalJavascript;

use Drupal\Component\Utility\Unicode;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\Tests\sms\Functional\SmsFrameworkTestTrait;

/**
 * Tests phone number administration user interface.
 *
 * @group SMS Framework
 * @group legacy
 */
class SmsFrameworkPhoneNumberAdminTest extends JavascriptTestBase {

  use SmsFrameworkTestTrait;

  public static $modules = ['block', 'entity_test'];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->drupalPlaceBlock('page_title_block');
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');

    $account = $this->drupalCreateUser([
      'administer smsframework',
    ]);
    $this->drupalLogin($account);

  }

  /**
   * Test using existing fields for new phone number settings.
   */
  public function testPhoneNumberFieldExisting() {
    $field_storage = $this->entityTypeManager->getStorage('field_storage_config');
    $field_instance = $this->entityTypeManager->getStorage('field_config');

    // Create a field so it appears as a pre-existing field.
    /** @var \Drupal\field\FieldStorageConfigInterface $field_telephone */
    $field_telephone = $field_storage->create([
      'entity_type' => 'entity_test',
      'field_name' => Unicode::strtolower($this->randomMachineName()),
      'type' => 'telephone',
    ]);
    $field_telephone->save();

    $field_instance->create([
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'field_name' => $field_telephone->getName(),
    ])->save();

    $driver = $this->getSession()->getDriver();
    $driver->selectOption('[input name="entity_bundle"]', 'entity_test|entity_test');
    $driver->wait(1000, 'false');
    $driver->selectOption('[input name="field_mapping[phone_number]"]', $field_telephone->getName());
    $driver->click('[input value="Save"]');

//    $this->drupalPostForm('admin/config/smsframework/phone_number/add', $edit, 'entity_bundle');

//    $edit['field_mapping[phone_number]'] = $field_telephone->getName();
//    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->drupalGet('admin/config/smsframework/phone_number/entity_test.entity_test');
    $this->assertResponse(200);
    $this->assertOptionSelected('edit-field-mapping-phone-number', $field_telephone->getName());
  }

}
