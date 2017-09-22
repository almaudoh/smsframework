<?php

namespace Drupal\Tests\sms\Kernel\Migrate;

use Drupal\Tests\migrate_drupal\Kernel\d6\MigrateDrupal6TestBase;

class MigrateD6SmsPhoneNumberTest extends MigrateDrupal6TestBase {

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
   * Execute the D6 sms_user phone number migration.
   */
  protected function getMigrationsToTest() {
    return [
      'd6_filter_format',
      'd6_user_role',
      'd6_user',
      'phone_number_settings',
      'd6_sms_number',
    ];
  }

  protected function getMigrationsToRollback() {
    return [
      'd6_sms_number',
      'phone_number_settings',
    ];
  }

  /**
   * File path to the DB fixture for sms_user table and records.
   */
  protected function smsUserFixtureFilePath() {
    return __DIR__ . '/../../../fixtures/migrate/sms_user_drupal6.php';
  }

  /**
   * {@inheritdoc}
   */
  protected function confirmationMessageFixturePath() {
    return __DIR__ . '/../../../fixtures/migrate/sms_confirmation_message_d6.php';
  }

}
