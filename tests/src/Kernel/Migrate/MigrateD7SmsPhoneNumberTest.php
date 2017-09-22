<?php

namespace Drupal\Tests\sms\Kernel\Migrate;

use Drupal\migrate\Exception\RequirementsException;
use Drupal\Tests\migrate_drupal\Kernel\d7\MigrateDrupal7TestBase;

class MigrateD7SmsPhoneNumberTest extends MigrateDrupal7TestBase {

  use MigratePhoneNumberTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'sms',
    'telephone',
    'dynamic_entity_reference',
  ];

  /**
   * Tests that the requirements for the d7_sms_number migration are enforced.
   */
  public function _testMigrationRequirements() {
    $this->setExpectedException(RequirementsException::class, 'Missing migrations d7_user, phone_number_settings.');
    $this->getMigration('d7_sms_number')->checkRequirements();
  }

  /**
   * Execute the D7 sms_user phone number migration.
   */
  protected function getMigrationsToTest() {
    return [
      'd7_filter_format',
      'd7_user_role',
      'd7_user',
      'phone_number_settings',
      'd7_sms_number',
    ];
  }

  protected function getMigrationsToRollback() {
    return [
      'd7_sms_number',
      'phone_number_settings',
    ];
  }

  /**
   * File path to the DB fixture for sms_user table and records.
   */
  protected function smsUserFixtureFilePath() {
    return __DIR__ . '/../../../fixtures/migrate/sms_user_drupal7.php';
  }

  /**
   * {@inheritdoc}
   */
  protected function confirmationMessageFixturePath() {
    return __DIR__ . '/../../../fixtures/migrate/sms_confirmation_message_d7.php';
  }

}
